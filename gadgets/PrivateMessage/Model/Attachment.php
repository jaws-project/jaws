<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
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
     * Get a message attachments Info
     *
     * @access  public
     * @param   integer  $id   Attachment id
     * @return  array    Array of message attachments or Empty Array
     */
    function GetMessageAttachment($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_attachments');
        $table->select('message:integer', 'filename', 'title', 'filesize', 'filetype');
        $result = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }

    /**
     * Get a message attachments file path
     *
     * @access  public
     * @param   integer  $uid   user id
     * @param   string   $filename   attachment file name
     * @return  array    Array of message attachments or Empty Array
     */
    function GetMessageAttachmentFilePath($uid, $filename)
    {
        return JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $uid . DIRECTORY_SEPARATOR . $filename;
    }

}