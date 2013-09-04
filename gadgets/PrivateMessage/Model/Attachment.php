<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
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
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $table->select('id:integer', 'host_filename', 'user_filename', 'file_size', 'hints_count:integer');
        $result = $table->where('message_id', $id)->fetchAll();
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
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $table->select('message_id:integer', 'host_filename', 'user_filename', 'file_size', 'hints_count:integer');
        $result = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }

    /**
     * Increment attachment download hits
     *
     * @access  public
     * @param   integer  $id   Attachment id
     * @return  mixed   True or Jaws_Error
     */
    function HitAttachmentDownload($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $res = $table->update(
            array(
                'hints_count' => $table->expr('hints_count + ?', 1)
            )
        )->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }
}