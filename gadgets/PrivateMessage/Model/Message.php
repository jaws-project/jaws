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
     * Get a message Info
     *
     * @access  public
     * @param   integer  $id   Message id
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetMessage($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.attachment',
            'pm_messages.insert_time', 'users.nickname as from_nickname', 'users.username as from_username',
            'users.avatar', 'users.email', 'pm_recipients.status:integer', 'from:integer'
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

    /**
     * Get a message
     *
     * @access  public
     * @param   integer  $id     Message id
     * @param   integer  $user   User id
     * @return  mixed    True or Jaws_Error on failure
     */
    function DeleteMessage($id, $user)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $result = $table->delete()->where('message_id', $id)->and()->where('recipient', $user)->exec();
        return $result;
    }

    /**
     * Mark messages status
     *
     * @access  public
     * @param   array    $ids      Message id(s)
     * @param   integer  $status   New message status
     * @param   integer  $user     User id
     * @return  bool    True or False
     */
    function MarkMessages($ids, $status, $user)
    {
        if(!is_array($ids) && is_numeric($ids)) {
            $ids = array($ids);
        }

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->update(array('status' => $status))->where('message_id', $ids, 'in')->and()->where('recipient', $user);
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return true;
    }

    /**
     * Reply message
     *
     * @access  public
     * @param   integer  $id     Message id
     * @param   integer  $user   User id
     * @param   string   $reply  Reply message
     * @return  mixed    True or Jaws_Error on failure
     */
    function ReplyMessage($id, $user, $reply)
    {
        $message = $this->GetMessage($id);

        $table = Jaws_ORM::getInstance()->table('pm_messages');
        //Start Transaction
        $table->beginTransaction();

        $data = array();
        $data['parent_id']   = $id;
        $data['from']        = $user;
        $data['subject']     = _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']);
        $data['body']        = $reply;
        $data['insert_time'] = time();
        $message_id = $table->insert($data)->exec();

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $data = array();
        $data['message_id'] = $message_id;
        $data['recipient'] = $message['from'];

        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        //Commit Transaction
        $table->commit();
        return true;
    }

 }