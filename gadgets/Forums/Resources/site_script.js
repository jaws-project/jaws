/**
 * Forums JS actions
 *
 * @category    Ajax
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * add a file entry
 */
function extraAttachment()
{
    var div = $('attachment_model').cloneNode(true);
    div.style.display = 'inline-block';
    $('btn_add_attachment').grab(div, 'before');
}

/**
 * Remove a file entry
 */
function removeAttachment(element)
{
    Element.destroy(element.getParent());
}
