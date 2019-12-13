/**
 * Subscription Javascript front-end actions
 *
 * @category    Ajax
 * @package     Subscription
 */
function Jaws_Gadget_Subscription() { return {
    // application PubicKey
    applicationServerKey : "BHdFMcHttE6c17cFuu6T7G4eN7TiY-1tn5EhgClhAVGC_fdVEBh8y3goJXC_PyoC5-eRW-NGcN5hg2He0kQyTX4",

    // ASync callback method
    AjaxCallback : {
        UpdateSubscription: function(response) {
            //
        },
    },

    /**
     * Update subscription
     */
    updateSubscription: function() {
        if($('#web-push-subscription').checkbox('isChecked')) {
            this.webPushSubscribe();
        } else {
            this.gadget.ajax.callAsync(
                'UpdateSubscription',
                $.unserialize($('form[name=subscription]').serialize())
            );
        }

        return false;
    },

    /**
     * Check Browser Support Web Push Notification
     */
    browserSupportWebPushNotification: function () {
        if (!('serviceWorker' in navigator)) {
            console.warn("Service workers are not supported by this browser");
            return false;
        }

        if (!('PushManager' in window)) {
            console.warn('Push notifications are not supported by this browser');
            return false;
        }

        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
            console.warn('Notifications are not supported by this browser');
            return false;
        }

        // Check the current Notification permission.
        // If its denied, the button should appears as such, until the user changes the permission manually
        if (Notification.permission === 'denied') {
            console.warn('Notifications are denied by the user');
            return false;
        }

        return true;
    },

    /**
     * urlBase64ToUint8Array
     */
    urlBase64ToUint8Array: function (base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    },

    /**
     * push Send Subscription To Server
     */
    pushSendSubscriptionToServer: function (subscription) {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        const data = $.unserialize($('form[name=subscription]').serialize());
        data.webPush = {
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
            contentEncoding,
        };

        this.gadget.ajax.callAsync(
            'UpdateSubscription',
            data
        );
    },

    /**
     * webPushSubscribe
     */
    webPushSubscribe : function () {
        // Use serviceWorker.ready to ensure that you can subscribe for push
        navigator.serviceWorker.ready
            .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.applicationServerKey),
            }))
            .then(subscription => {
                // Subscription was successful
                // create subscription on your server
                return this.pushSendSubscriptionToServer(subscription);
            })
            // .then(subscription => {
            //     console.log(3333333333333);
            // }) // update your UI
            .catch(e => {
                if (Notification.permission === 'denied') {
                    // The user denied the notification permission which
                    // means we failed to subscribe and the user will need
                    // to manually change the notification permission to
                    // subscribe to push messages
                    console.warn('Notifications are denied by the user.');
                    // changePushButtonState('incompatible');
                } else {
                    // A problem occurred with the subscription; common reasons
                    // include network errors or the user skipped the permission
                    console.error('Impossible to subscribe to push notifications', e);
                    // changePushButtonState('disabled');
                }
            });
    },


    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        // init iic-sale action
        if (this.gadget.actions.indexOf('Subscription') >= 0) {

            // register service worker
            if(this.browserSupportWebPushNotification()) {
                $('#web-push-subscription').show();

                navigator.serviceWorker
                    .register('service-worker.js')
                    .then(function() { console.log('Service Worker Registered'); })
                    .catch(function(error) {
                        console.error('Service Worker Error', error);
                    });
            }
        }
    },

}};
