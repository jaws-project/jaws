/**
 * SiteActivity Javascript actions
 *
 * @category    Ajax
 * @package     SiteActivity
 */
/**
 * Use async mode, create Callback
 */
var SiteActivityCallback = {
    SaveSettings: function(response) {
        SiteActivityAjax.showResponse(response);
    }
};

/**
 * save gadget settings
 */
function saveSettings(form) {
    SiteActivityAjax.callAsync(
        'SaveSettings',
        {
            'gadgets_drivers': $.unserialize($('#gadgets_drivers select').serialize())
        }
    );
}

var SiteActivityAjax = new JawsAjax('SiteActivity', SiteActivityCallback);
