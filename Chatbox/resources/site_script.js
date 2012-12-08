/**
 * Chatbox Javascript actions
 *
 * @category   Ajax
 * @package    Chatbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Get messages list
 *
 */
function GetMessages()
{
    var messages = ChatboxAjax.callSync('getmessages');
    $('chatbox_messages').innerHTML = messages;
}


var ChatboxAjax = new JawsAjax('Chatbox');
setInterval("GetMessages()", 30*1000);
