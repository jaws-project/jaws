/**
 * ServerTime Javascript actions
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ServerTimeCallback = { 
    UpdateProperties: function(response) {
        ServerTimeAjax.showResponse(response);
    }
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    ServerTimeAjax.callAsync('UpdateProperties', $('date_format').value);

}

var ServerTimeAjax = new JawsAjax('ServerTime', ServerTimeCallback);
