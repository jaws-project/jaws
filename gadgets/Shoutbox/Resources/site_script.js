/**
 * Shoutbox Javascript actions
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Get comments list
 *
 */
function GetComments()
{
    var comments = ShoutboxAjax.call('GetComments', {}, false, {'async': false});
    $('.shoutbox_comments').html(comments);
}

var ShoutboxAjax = new JawsAjax('Shoutbox');
// ShoutboxAjax.setMessageBox('shoutbox_comments_response');
setInterval(GetComments, 30*1000);
