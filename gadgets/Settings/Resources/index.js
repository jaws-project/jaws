/**
 * Settings Javascript front-end actions
 *
 * @category    Ajax
 * @package     Settings
 */
function Jaws_Gadget_Settings() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var SettingsCallback = {
};

/**
 * Update settings
 */
function updateSettings()
{
    var result = SettingsAjax.call(
        'UpdateSettings',
        $.unserialize($('form[name=settings]').serialize())
    );
    return false;
}

var SettingsAjax = new JawsAjax('Settings', SettingsCallback);