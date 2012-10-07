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
    var messages = chatbox.getmessages();
    $('chatbox_messages').innerHTML = messages;
}

var chatbox = new chatboxajax();
chatbox.serverErrorFunc = Jaws_Ajax_ServerError;
setInterval("GetMessages()", 30*1000);
