/**
 * Notification Javascript actions
 *
 * @category    Ajax
 * @package     Notification
 */
/**
 * Use async mode, create Callback
 */
var NotificationCallback = {
    SaveSettings: function(response) {
        NotificationAjax.showResponse(response);
    }
};

/**
 * Submit the button
 */
function saveChanges(form)
{
    NotificationAjax.callAsync('SaveSettings', gadgets);
}

var NotificationAjax = new JawsAjax('Notification', NotificationCallback);
