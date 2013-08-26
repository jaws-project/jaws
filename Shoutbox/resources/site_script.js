/**
 * Shoutbox Javascript actions
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Get messages list
 *
 */
function GetMessages()
{
    var messages = ShoutboxAjax.callSync('getmessages');
    $$('.shoutbox_comments').innerHTML = messages;
}


var ShoutboxAjax = new JawsAjax('Shoutbox');
setInterval("GetMessages()", 30*1000);
