/**
 * ServerTime Javascript actions
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_ServerTime() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var ServerTimeCallback = {
};

/**
 * Update the properties
 */
function updateProperties(form)
{
    ServerTimeAjax.call('UpdateProperties', $('#date_format').val());

}

var ServerTimeAjax = new JawsAjax('ServerTime', ServerTimeCallback);
