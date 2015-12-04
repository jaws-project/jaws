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
 * save gadget settings
 */
function saveSettings(form) {
    NotificationAjax.callAsync(
        'SaveSettings',
        {
            'gadgets_drivers': $.unserialize($('#gadgets_drivers select').serialize())
        }
    );
}

var NotificationAjax = new JawsAjax('Notification', NotificationCallback);
