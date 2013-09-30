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
    var fileDivElement = new Element('div#div_attachment' + NumAttachments);
    var fileInputElement = new Element('input', {type: 'file', name: 'attachment[]'});
    fileDivElement.grab(fileInputElement);
    
    var fileAElement = new Element('a', {href: 'javascript:removeAttachment(' + NumAttachments + ');', html: ' ' + lblRemoveAttachment});
    //var fileImageElement = new Element('img', {src: removeIconSrc, alt: lblRemoveAttachment});
    fileDivElement.grab(fileAElement);
    $('attachments').grab(fileDivElement);
}

/**
 * Remove a file entry
 */
function removeAttachment(objID)
{
    NumAttachments--;
    $('div_attachment' + objID).dispose();
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