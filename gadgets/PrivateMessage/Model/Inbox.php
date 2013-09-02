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
class PrivateMessage_Model_Inbox extends Jaws_Gadget_Model
{
    /**
     * Get Inbox
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   integer  $status    Message status
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetInbox($user, $status = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.attachment',
            'pm_messages.insert_time', 'users.nickname as from_nickname', 'pm_recipients.status:integer'
        );
        $table->join('users', 'pm_messages.from', 'users.id');
        $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message_id');
        $table->where('pm_recipients.recipient', $user);

        if ($status !== null) {
            $table->and()->where('pm_recipients.status', $status);
        }

        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get Inbox Statistics
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   integer  $status    Message status
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetInboxStatistics($user, $status = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->select('count(message_id):integer')->where('recipient', $user);

        if ($status !== null) {
            $table->and()->where('status', $status);
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

 }