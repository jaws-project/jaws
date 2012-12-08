/**
 * ServerTime Javascript actions
 *
 * @category   Ajax
 * @package    ServerTime
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ServerTimeCallback = { 
    updateproperties: function(response) {
        showResponse(response);
    }
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    ServerTimeAjax.callAsync('updateproperties', $('date_format').value);

}

var ServerTimeAjax = new JawsAjax('ServerTime', ServerTimeCallback);
