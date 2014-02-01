<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Model_Attachment extends Jaws_Gadget_Model
{
    /**
     * Get a message attachments Info
     *
     * @access  public
     * @param   integer  $id   Message id
     * @return  array    Array of message attachments or Empty Array
     */
    function GetMessageAttachments($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_message_attachment');
        $table->select('pm_attachments.id:integer', 'filename', 'title', 'filesize', 'filetype');
        $table->join('pm_attachments', 'pm_attachments.id', 'pm_message_attachment.attachment');
        $result = $table->where('message', $id)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }

    /**
     * Get a message attachment Info
     *
     * @access  public
     * @param   integer  $id        Attachment id
     * @param   integer  $message   Message id
     * @return  array    Array of message attachments or Empty Array
     */
    function GetAttachment($id, $message = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_attachments');
        $table->select('id:integer', 'filename', 'title', 'filesize', 'filetype');
        if (!empty($message)) {
            $table->join('pm_message_attachment', 'pm_attachments.id', 'pm_message_attachment.attachment');
            $table->where('message', (int)$message);
        }
        $result = $table->and()->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }
}