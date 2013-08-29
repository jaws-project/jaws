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
 * Get comments list
 *
 */
function GetComments()
{
    var comments = ShoutboxAjax.callSync('GetComments');
    __('.shoutbox_comments').innerHTML = comments;
}


var ShoutboxAjax = new JawsAjax('Shoutbox');
setInterval("GetComments()", 30*1000);
