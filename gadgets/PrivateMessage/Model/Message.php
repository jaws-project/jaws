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
class PrivateMessage_Model_Message extends Jaws_Gadget_Model
{
    /**
     * Get Message Info
     *
     * @access  public
     * @param   integer  $id   Message id
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetMessage($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.title', 'pm_messages.body', 'pm_messages.attachment',
            'pm_messages.insert_time', 'users.nickname as from_nickname', 'pm_recipients.status:integer'
        );
        $table->join('users', 'pm_messages.from', 'users.id');
        $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message_id');
        $table->where('pm_messages.id', $id);

        $result = $table->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

 }