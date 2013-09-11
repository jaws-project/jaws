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
     * @param   integer $id                 Message id
     * @param   bool    $fetchAttachment    Fetch message's attachment info?
     * @param   bool    $getRecipients       Get recipient info?
     * @return  mixed   Inbox count or Jaws_Error on failure
     */
    function GetMessage($id, $fetchAttachment = false, $getRecipients = true)
    {
        $messageTable = Jaws_ORM::getInstance()->table('pm_messages');
        $messageTable->select('id:integer')->where('parent', $id)->alias('child_id');

        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $columns = array(
            'pm_messages.id:integer', 'parent:integer', 'pm_messages.subject', 'pm_messages.body',
            'users.nickname as from_nickname', 'users.username as from_username', 'users.avatar', 'users.email',
            'user:integer', 'pm_messages.insert_time', $messageTable);
        if($getRecipients) {
            $columns[] = 'pm_recipients.read:boolean';
        }

        $table->select($columns);
        $table->join('users', 'pm_messages.user', 'users.id');
        if($getRecipients) {
            $columns[] = 'pm_recipients.read:boolean';
            $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message');
            $table->where('pm_recipients.id', $id);
        } else {
            $table->where('pm_messages.id', $id);
        }

        $result = $table->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        if($fetchAttachment) {
            $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Attachment');
            $result['attachments'] = $model->GetMessageAttachments($id);
        }
        return $result;
    }

    /**
     * Get parent messages info
     *
     * @access  public
     * @param   integer   $id                 Message id
     * @param   bool      $fetchAttachment    Fetch message's attachment info?
     * @param   $result
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetParentMessages($id, $fetchAttachment, &$result)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer', 'parent:integer', 'pm_messages.subject', 'pm_messages.body',
            'users.nickname as from_nickname', 'users.username as from_username', 'users.avatar', 'users.email',
            'pm_recipients.read:boolean', 'user:integer', 'pm_messages.insert_time'
        );
        $table->join('users', 'pm_messages.user', 'users.id');
        $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message');
        $table->where('pm_recipients.id', $id);

        $message = $table->fetchRow();
        if (Jaws_Error::IsError($message)) {
            return new Jaws_Error($message->getMessage(), 'SQL');
        }

        if($fetchAttachment) {
            $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Attachment');
            $message['attachments'] = $model->GetMessageAttachments($id);
        }

        $result[] = $message;
        if(!empty($message['parent'])) {
            $this->GetParentMessages($message['parent'], $fetchAttachment, $result);
        }

        return true;
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
        $result = $table->delete()->where('message', $id)->and()->where('recipient', $user)->exec();
        return $result;
    }

    /**
     * Mark messages read
     *
     * @access  public
     * @param   array    $ids      Message id(s)
     * @param   integer  $read     Message read flag
     * @param   integer  $user     User id
     * @return  bool    True or False
     */
    function MarkMessages($ids, $read, $user)
    {
        if(!is_array($ids) && is_numeric($ids)) {
            $ids = array($ids);
        }

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->update(array('read' => $read, 'update_time' => time()));
        $res = $table->where('id', $ids, 'in')->and()->where('recipient', $user)->exec();
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
        $data['parent']      = $message['id'];
        $data['user']        = $user;
        $data['subject']     = _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']);
        $data['body']        = $reply;
        $data['insert_time'] = time();
        $message_id = $table->insert($data)->exec();

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $data = array();
        $data['message']    = $message_id;
        $data['recipient']  = $message['user'];

        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        //Commit Transaction
        $table->commit();
        return true;
    }

    /**
     * Send message
     *
     * @access  public
     * @param   integer $user           User id
     * @param   array   $message        Message data
     * @param   array   $attachments    File attachments data
     * @return  mixed    True or Jaws_Error on failure
     */
    function SendMessage($user, $message, $attachments)
    {
        $mTable = Jaws_ORM::getInstance()->table('pm_messages');
        //Start Transaction
        $mTable->beginTransaction();

        $data = array();
        $data['user']        = $user;
        $data['subject']     = $message['subject'];
        $data['body']        = $message['body'];
        $data['published']   = $message['published'];
        $data['insert_time'] = time();
        $message_id = $mTable->insert($data)->exec();
        if (Jaws_Error::IsError($message_id)) {
            return false;
        }

        if (!empty($attachments) && count($attachments) > 0) {
            $table = Jaws_ORM::getInstance()->table('pm_attachments');
//            $aData = array();
//            foreach ($attachments as $attachment) {
//                $attachment['message_id'] = $message_id;
//                $aData[] = $attachment;
//            }
//            $res = $table->insertAll($aData)->exec();
            foreach ($attachments as $attachment) {
                $aData = array();
                $aData = $attachment;
                $aData['message'] = $message_id;
                $res = $table->insert($aData)->exec();
                if (Jaws_Error::IsError($res)) {
                    return false;
                }
            }

        }

        $recipient_users = array();
        if (!empty($message['recipient_users'])) {
            $recipient_users = explode(",", $message['recipient_users']);
        }
        if (!empty($message['recipient_groups'])) {
            $recipient_groups = explode(",", $message['recipient_groups']);
            $table = Jaws_ORM::getInstance()->table('users_groups');
            $group_users = $table->select('user_id')->where('group_id', $recipient_groups, 'in')->fetchColumn();
            if (!empty($group_users) && count($group_users) > 0) {
                $recipient_users = array_merge($recipient_users, $group_users);
            }
        }

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
//        $data = array();
//        foreach($recipient_users as $recipient_user) {
//            $data[] = array(
//                'message_id' =>$message_id,
//                'recipient' =>$recipient_user,
//            );
//        }
//        $res = $table->insertAll($data)->exec();

        if (!empty($recipient_users) && count($recipient_users) > 0) {
            foreach ($recipient_users as $recipient_user) {
                $data = array(
                    'message' => $message_id,
                    'recipient' => $recipient_user,
                );
                $res = $table->insert($data)->exec();
                if (Jaws_Error::IsError($res)) {
                    return false;
                }
            }
        } else {
            //Rollback Transaction
            $mTable->rollback();
            return false;
        }

        //Commit Transaction
        $mTable->commit();
        return true;
    }

 }