/**
 * Settings Javascript front-end actions
 *
 * @category    Ajax
 * @package     Settings
 */

/**
 * Use async mode, create Callback
 */
var SettingsCallback = {
    UpdateSettings: function(response) {
        SettingsAjax.showResponse(response);
    }
};

/**
 * Update settings
 */
function updateSettings()
{
    var result = SettingsAjax.callAsync(
        'UpdateSettings',
        $.unserialize($('form[name=settings]').serialize())
    );
    return false;
}

var SettingsAjax = new JawsAjax('Settings', SettingsCallback);