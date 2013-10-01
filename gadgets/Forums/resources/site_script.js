/**
 * Forums JS actions
 *
 * @category   Ajax
 * @package    Forums
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * add a file entry
 */
function extraAttachment()
{
    NumAttachments++;
    var div = $('attachment_model').cloneNode(true);
    div.id = 'div_attachment' + NumAttachments
    div.style.display = 'block';
    $('attachments').grab(div);
}

/**
 * Remove a file entry
 */
function removeAttachment(objID)
{
    NumAttachments--;
    Element.destroy($(objID).parentNode);
}

/**
 * Remove a old attach file
 */
function removeCurrentAttachment(objID)
{
    NumAttachments--;
    $('p_attach' + objID).dispose();
}

var NumAttachments = 0;