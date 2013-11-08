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
     * @param   bool    $getRecipients      Get recipient info?
     * @return  mixed   Inbox count or Jaws_Error on failure
     */
    function GetMessage($id, $fetchAttachment = false, $getRecipients = true)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $columns = array(
            'pm_messages.id:integer', 'parent:integer', 'pm_messages.subject', 'pm_messages.body', 'published:boolean',
            'users.nickname as from_nickname', 'users.username as from_username', 'users.avatar', 'users.email',
            'user:integer', 'pm_messages.insert_time', 'pm_messages.type:integer', 'recipient_users', 'recipient_groups');
        if($getRecipients) {
            $columns[] = 'pm_recipients.recipient:integer';
            $columns[] = 'pm_recipients.read:boolean';
            $columns[] = 'pm_recipients.archived:boolean';
            $columns[] = 'pm_recipients.recipient:integer';
        } else {
            $subTable = Jaws_ORM::getInstance()->table('pm_recipients');
            $subTable->select('count(id)')->where('read', true)->and()->where('message', (int)$id)->alias('read_count');
            $columns[] = $subTable;
        }

        $table->select($columns);
        $table->join('users', 'pm_messages.user', 'users.id');
        if($getRecipients) {
            $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message');
            $table->where('pm_recipients.id', (int)$id);
        } else {
            $table->where('pm_messages.id', (int)$id);
        }

        $result = $table->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        if($fetchAttachment && !empty($result)) {
            $model = $this->gadget->model->load('Attachment');
            $result['attachments'] = $model->GetMessageAttachments($result['id']);
        }
        return $result;
    }

    /**
     * Get a message recipients
     *
     * @access  public
     * @param   integer $id   Message id
     * @return  mixed   Inbox count or Jaws_Error on failure
     */
    function GetMessageRecipients($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');

        $result = $table->select('recipient:integer')->where('message', $id)->fetchColumn();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a message recipients info
     *
     * @access  public
     * @param   integer $id   Message id
     * @return  mixed   Inbox count or Jaws_Error on failure
     */
    function GetMessageRecipientsInfo($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');

        $table->select(array('recipient:integer', 'users.nickname as nickname',
                             'users.username as username', 'users.avatar', 'users.email', 'update_time'
        ))->where('message', $id);
        $table->join('users', 'pm_recipients.recipient', 'users.id');
        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
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
        $table->where('pm_messages.id', $id);

        $message = $table->fetchRow();
        if (Jaws_Error::IsError($message)) {
            return new Jaws_Error($message->getMessage(), 'SQL');
        }

        if($fetchAttachment) {
            $model = $this->gadget->model->load('Attachment');
            $message['attachments'] = $model->GetMessageAttachments($id);
        }

        $result[] = $message;
        if(!empty($message['parent'])) {
            $this->GetParentMessages($message['parent'], $fetchAttachment, $result);
        }

        return true;
    }

    /**
     * Delete outbox message
     *
     * @access  public
     * @param   array    $ids           Message ids
     * @param   array    $justDelete    What items need delete?(attachments, recipients)
     * @return  mixed    True or False or Jaws_Error on failure
     */
    function DeleteOutboxMessage($ids, $justDelete = array())
    {
        $result = false;
        if(empty($ids)) {
            return false;
        }
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }

        // Delete message's recipients
        $rTable = Jaws_ORM::getInstance()->table('pm_recipients');
        //Start Transaction
        $rTable->beginTransaction();

        // Delete attachments
        if (count($justDelete) == 0 || in_array('attachments', $justDelete)) {
            $aModel = $this->gadget->model->load('Attachment');
            foreach ($ids as $id) {
                $message = $this->GetMessage($id, true, false);
                foreach ($message['attachments'] as $attachment) {
                    $filepath = $aModel->GetMessageAttachmentFilePath($message['user'], $attachment['filename']);
                    if (!Jaws_Utils::delete($filepath)) {
                        //Rollback Transaction
                        $rTable->rollback();
                        return false;
                    }
                }
            }

            // Delete message's attachments
            $aTable = Jaws_ORM::getInstance()->table('pm_attachments');
            $result = $aTable->delete()->where('message', $ids, 'in')->exec();
        }

        if (count($justDelete) == 0 || in_array('recipients', $justDelete)) {
            $result = $rTable->delete()->where('message', $ids, 'in')->exec();
        }

        if (count($justDelete) == 0) {
            // Delete message
            $mTable = Jaws_ORM::getInstance()->table('pm_messages');
            $result = $mTable->delete()->where('id', $ids, 'in')->exec();
        }

        //Commit Transaction
        $rTable->commit();
        return $result;
    }

    /**
     * Archive inbox message
     *
     * @access  public
     * @param   array    $ids     Message ids
     * @param   integer  $user     User id
     * @param   bool     $status   Archive status(true=archive, false=no archive)
     * @return  mixed    True or Jaws_Error on failure
     */
    function ArchiveInboxMessage($ids, $user, $status)
    {
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->update(array('archived' => $status))->where('id', $ids, 'in');
        if ($user != null) {
            $table->and()->where('recipient', $user);
        }
        $result = $table->exec();
        return $result;
    }

    /**
     * Delete inbox message
     *
     * @access  public
     * @param   array    $ids     Message ids
     * @return  mixed    True or Jaws_Error on failure
     */
    function DeleteInboxMessage($ids)
    {
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        return $result;
    }

    /**
     * Change messages publish status
     *
     * @access  public
     * @param   integer  $ids           Messages id
     * @param   bool     $published     Published status
     * @return  bool    True or False
     */
    function MarkMessagesPublishStatus($ids, $published)
    {
        if(!is_array($ids) && is_numeric($ids)) {
            $ids = array($ids);
        }

        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->update(array('published' => $published, 'update_time' => time()));
        $res = $table->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return true;
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
     * Compose message
     *
     * @access  public
     * @param   integer $user           User id
     * @param   array   $messageData    Message data
     * @return  mixed   Message Id or Jaws_Error on failure
     */
    function ComposeMessage($user, $messageData)
    {
        $messageData['type'] = PrivateMessage_Info::PRIVATEMESSAGE_TYPE_MESSAGE;
        // merge recipient users & groups to an array
        $recipient_users = array();
        if (trim($messageData['recipient_users']) == '0' || !empty($messageData['recipient_users'])) {
            if (trim($messageData['recipient_users']) == '0') {
                $messageData['type'] = PrivateMessage_Info::PRIVATEMESSAGE_TYPE_ANNOUNCEMENT;
                $table = Jaws_ORM::getInstance()->table('users');
                $recipient_users = $table->select('id:integer')->fetchColumn();
            } else {
                $recipient_users = explode(",", $messageData['recipient_users']);
            }
        }
        if (!empty($messageData['recipient_groups'])) {
            $recipient_groups = explode(",", $messageData['recipient_groups']);
            $table = Jaws_ORM::getInstance()->table('users_groups');
            $table->select('user_id:integer');
            $table->join('groups', 'groups.id', 'users_groups.group_id');
            $table->where('group_id', $recipient_groups, 'in');
            $group_users = $table->and()->where('groups.owner', $user)->fetchColumn();
            if (!empty($group_users) && count($group_users) > 0) {
                $recipient_users = array_merge($recipient_users, $group_users);
            }
        }
        $recipient_users = array_unique($recipient_users);

        // validation input fields
        if ($messageData['published']) {
            if (empty($recipient_users) || count($recipient_users) <= 0 || empty($messageData['subject'])) {
                return new Jaws_Error(_t('PRIVATEMESSAGE_MESSAGE_INCOMPLETE_FIELDS'), _t('PRIVATEMESSAGE_NAME'));
            }

        } else {
            if (empty($messageData['title'])) {
                return new Jaws_Error(_t('PRIVATEMESSAGE_MESSAGE_INCOMPLETE_FIELDS'), _t('PRIVATEMESSAGE_NAME'));
            }
        }

        $mTable = Jaws_ORM::getInstance()->table('pm_messages');
        //Start Transaction
        $mTable->beginTransaction();

        $data = array();
        // compose a draft message
        if (!empty($messageData['id']) && $messageData['id'] > 0) {
            $data['subject']            = $messageData['subject'];
            $data['body']               = $messageData['body'];
            $data['published']          = $messageData['published'];
            $data['type']               = $messageData['type'];
            $data['attachments']        = count($messageData['attachments']);
            $data['recipient_users']    = $messageData['recipient_users'];
            $data['recipient_groups']   = $messageData['recipient_groups'];
            $data['update_time']        = time();
            $res = $mTable->update($data)->where('id', $messageData['id'])->exec();
            $message_id = $messageData['id'];
            if (Jaws_Error::IsError($res)) {
                return false;
            }
        } else {
            $data['user']               = $user;
            if(!empty($messageData['parent'])) {
                $data['parent']         = $messageData['parent'];
            }
            $data['subject']            = $messageData['subject'];
            $data['body']               = $messageData['body'];
            $data['published']          = $messageData['published'];
            $data['type']               = $messageData['type'];
            $data['attachments']        = count($messageData['attachments']);
            $data['recipient_users']    = $messageData['recipient_users'];
            $data['recipient_groups']   = $messageData['recipient_groups'];
            $data['insert_time']        = time();
            $message_id = $mTable->insert($data)->exec();
            if (Jaws_Error::IsError($message_id)) {
                return false;
            }
        }

        // Insert attachments info
        if (!empty($messageData['attachments']) && count($messageData['attachments']) > 0) {
            $aModel = $this->gadget->model->load('Attachment');

            $aData = array();
            $pm_dir = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $user . DIRECTORY_SEPARATOR;
            foreach ($messageData['attachments'] as $attachment) {

                // check new attachments file -- we must copy tmp files to correct location
                if (is_array($attachment)) {
                    $src_filepath = Jaws_Utils::upload_tmp_dir() . '/' . $attachment['filename'];
                    $dest_filepath = $pm_dir . DIRECTORY_SEPARATOR . $attachment['filename'];
                } else {
                    // check exist attachments -- we just need to copy it!
                    $attachment = $aModel->GetMessageAttachment($attachment);
                    $message_info = $this->GetMessage($attachment['message'], false, false);
                    $src_filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $message_info['user'] .
                        DIRECTORY_SEPARATOR . $attachment['filename'];
                    $dest_filepath = $pm_dir . $attachment['filename'];
                }

                if (!file_exists($src_filepath)) {
                    continue;
                }

                if (!file_exists($pm_dir)) {
                    if (!Jaws_Utils::mkdir($pm_dir)) {
                        return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', JAWS_DATA));
                    }
                }

                $cres = Jaws_Utils::rename($src_filepath, $dest_filepath);
                if ($cres) {
                    Jaws_Utils::delete($src_filepath);
                    $aData[] = array(
                        'title'         => $attachment['title'],
                        'filename'      => $attachment['filename'],
                        'filesize'      => $attachment['filesize'],
                        'filetype'      => $attachment['filetype'],
                        'message_id'    => $message_id,
                    );
                }

            }

            if (!empty($messageData['id']) && $messageData['id'] > 0) {
                // delete message's recipients and attachments before insert new items
                $this->DeleteOutboxMessage($messageData['id'], array('attachments', 'recipients'));
            }

            $table = Jaws_ORM::getInstance()->table('pm_attachments');
            $res = $table->insertAll(array('title', 'filename', 'filesize', 'filetype', 'message'), $aData)->exec();
            if (Jaws_Error::IsError($res)) {
                return false;
            }
        } else {
            if (!empty($messageData['id']) && $messageData['id'] > 0) {
                // delete message's recipients and attachments before insert new items
                $this->DeleteOutboxMessage($messageData['id'], array('attachments', 'recipients'));
            }
        }

        // Insert recipients info
        if (!empty($recipient_users) && count($recipient_users) > 0) {
            $table = Jaws_ORM::getInstance()->table('pm_recipients');
            $rData = array();
            foreach ($recipient_users as $recipient_user) {
                $read = false;
                if ($recipient_user == 0) {
                    $read = true;
                }
                $rData[] = array($message_id, $recipient_user, $read);
            }
            $res = $table->insertAll(array('message', 'recipient', 'read'), $rData)->exec();
            if (Jaws_Error::IsError($res)) {
                return false;
            }
        }

        //Commit Transaction
        $mTable->commit();
        return $message_id;
    }

 }