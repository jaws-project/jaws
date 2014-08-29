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
            'pm_messages.id:integer', 'pm_messages.subject', 'pm_messages.body', 'from:integer', 'to:integer',
            'users.nickname as from_nickname', 'users.username as from_username', 'users.avatar', 'users.email',
            'pm_messages.insert_time', 'pm_messages.folder:integer', 'recipient_users', 'recipient_groups',
            'pm_messages.read:boolean');
/*        if($getRecipients) {
            $columns[] = 'pm_recipients.recipient:integer';
            $columns[] = 'pm_recipients.read:boolean';
            $columns[] = 'pm_recipients.archived:boolean';
            $columns[] = 'pm_recipients.recipient:integer';
        } else {
            $subTable = Jaws_ORM::getInstance()->table('pm_recipients');
            $subTable->select('count(id)')->where('read', true)->and()->where('message', (int)$id)->alias('read_count');
            $columns[] = $subTable;
        }*/

        $table->select($columns);
        $table->join('users', 'pm_messages.from', 'users.id', 'left');
        $message = $table->where('pm_messages.id', (int)$id)->fetchRow();
        if (Jaws_Error::IsError($message)) {
            return new Jaws_Error($message->getMessage());
        }

        // fetch recipients info
        if ($getRecipients && !empty($message)) {
            $usersId = (empty($message['recipient_users'])) ? '' : explode(',', $message['recipient_users']);
            $groupsId = (empty($message['recipient_groups'])) ? '' : explode(',', $message['recipient_groups']);

            $users = '';
            $groups = '';
            if (!empty($usersId)) {
                $table = Jaws_ORM::getInstance()->table('users');
                $users = $table->select('id:integer', 'nickname', 'username')->where('id', $usersId, 'in')->fetchAll();
            }

            if (!empty($groupsId)) {
                $table = Jaws_ORM::getInstance()->table('groups');
                $groups = $table->select('id:integer', 'name', 'title')->where('id', $groupsId, 'in')->fetchAll();
            }

            $message['users'] = $users;
            $message['groups'] = $groups;
        }

        // fetch attachments
        if ($fetchAttachment && !empty($message)) {
            $model = $this->gadget->model->load('Attachment');
            $message['attachments'] = $model->GetMessageAttachments($message['id']);
        }
        return $message;
    }

    /**
     * Delete message permanently
     *
     * @access  public
     * @param   array    $ids    Message ids
     * @param   int      $user   User id
     * @return  mixed    True or False or Jaws_Error on failure
     */
    function DeleteMessage($ids, $user)
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }

        // Get all attachments that will deleted
        $table = Jaws_ORM::getInstance()->table('pm_message_attachment');
        $attachments = $table->select('attachment')->distinct()->where('message', $ids, 'in')->fetchColumn();
        if (Jaws_Error::IsError($attachments)) {
            return false;
        }

        // Delete messages attachments relation
        $table = $table->table('pm_message_attachment');
        //Start Transaction
        $table->beginTransaction();

        $result = $table->delete()->where('message', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $table->rollback();
            return false;
        }

        // Delete messages
        $table = $table->table('pm_messages');

        $table->delete()->where('id', $ids, 'in');
        $table->and()->openWhere()->openWhere('pm_messages.from', (int)$user)->and()
            ->closeWhere('pm_messages.to', 0);
        $table->or()->where('pm_messages.to', (int)$user)->closeWhere();
        $result = $table->exec();
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $table->rollback();
            return false;
        }

        // Delete Message attachment(s)
        // Check if it is last attachment so delete the attachment file from disk and database
        foreach ($attachments as $attachment) {
            $table = $table->table('pm_message_attachment');
            $attachmentCount = $table->select('count(message)')->where('attachment', $attachment)->fetchOne();
            if ($attachmentCount == 0) {
                $model = $this->gadget->model->load('Attachment');
                $attachmentInfo = $model->GetAttachment($attachment);
                $filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . 'attachments' .
                    DIRECTORY_SEPARATOR . $attachmentInfo['filename'];
                if (!Jaws_Utils::delete($filepath)) {
                    //Rollback Transaction
                    $table->rollback();
                    return false;
                }

                // Delete attachment from main table
                $result = $table->table('pm_attachments')->delete()->where('id', $attachment)->exec();
                if (Jaws_Error::IsError($result)) {
                    //Rollback Transaction
                    $table->rollback();
                    return false;
                }
            }
        }

        //Commit Transaction
        $table->commit();
        return true;
    }

    /**
     * Archive message
     *
     * @access  public
     * @param   array    $ids     Message ids
     * @param   integer  $user    User id
     * @param   bool     $status  Archive status(true=archive, false=no archive)
     * @return  mixed    True or Jaws_Error on failure
     */
    function ArchiveMessage($ids, $user, $status)
    {
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }

        if ($status) {
            $table = Jaws_ORM::getInstance()->table('pm_messages');
            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED))->where('id', $ids, 'in');

            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res = $table->exec();

        } else {
            $table = Jaws_ORM::getInstance()->table('pm_messages');
            //Start Transaction
            $table->beginTransaction();

            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX));
            $table->where('id', $ids, 'in')->and()->where('to', 0, '>');
            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res1 = $table->exec();
            if(Jaws_Error::IsError($res1)) {
                //Rollback Transaction
                $table->rollback();
                return false;
            }

            $table = Jaws_ORM::getInstance()->table('pm_messages');
            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX));
            $table->where('id', $ids, 'in')->and()->where('to', 0);

            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res2 = $table->exec();
            if(Jaws_Error::IsError($res2)) {
                //Rollback Transaction
                $table->rollback();
                return false;
            }

            //Commit Transaction
            $table->commit();
            $res = ($res1 || $res2);
        }
        return $res;
    }

    /**
     * Trash message
     *
     * @access  public
     * @param   array    $ids     Message ids
     * @param   integer  $user    User id
     * @param   bool     $status  Trash status(true=trash, false=restore trash)
     * @return  mixed    True or Jaws_Error on failure
     */
    function TrashMessage($ids, $user, $status)
    {
        if (!is_array($ids) && $ids > 0) {
            $ids = array($ids);
        }

        if ($status) {
            $table = Jaws_ORM::getInstance()->table('pm_messages');
            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH))->where('id', $ids, 'in');

            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res = $table->exec();

        } else {
            $table = Jaws_ORM::getInstance()->table('pm_messages');
            //Start Transaction
            $table->beginTransaction();

            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX));
            $table->where('id', $ids, 'in')->and()->where('to', 0, '>');
            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res1 = $table->exec();
            if(Jaws_Error::IsError($res1)) {
                //Rollback Transaction
                $table->rollback();
                return false;
            }

            $table = Jaws_ORM::getInstance()->table('pm_messages');
            $table->update(array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX));
            $table->where('id', $ids, 'in')->and()->where('to', 0);

            if ($user != null) {
                $table->and()->openWhere('from', $user)->or();
                $table->closeWhere('to', $user);
            }
            $res2 = $table->exec();
            if(Jaws_Error::IsError($res2)) {
                //Rollback Transaction
                $table->rollback();
                return false;
            }

            //Commit Transaction
            $table->commit();
            $res = ($res1 || $res2);
        }
        return $res;
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

        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->update(array('read' => $read, 'update_time' => time()));
        $res = $table->where('id', $ids, 'in')->and()->where('to', $user)->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return true;
    }

    /**
     * Send message
     *
     * @access  public
     * @param   integer $user           User id
     * @param   array   $messageData    Message data
     * @return  mixed   Message Id or Jaws_Error on failure
     */
    function SendMessage($user, $messageData)
    {
        $table = Jaws_ORM::getInstance();
        // merge recipient users & groups to an array
        $recipient_users = array();
        if (trim($messageData['recipient_users']) == '0' || !empty($messageData['recipient_users'])) {
            if (trim($messageData['recipient_users']) == '0') {
                $table = $table->table('users');
                $recipient_users = $table->select('id:integer')->fetchColumn();
            } else {
                $recipient_users = explode(",", $messageData['recipient_users']);
            }
        }
        if (!empty($messageData['recipient_groups'])) {
            $recipient_groups = explode(",", $messageData['recipient_groups']);
            $table = $table->table('users_groups');
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
                return new Jaws_Error(_t('PRIVATEMESSAGE_MESSAGE_INCOMPLETE_FIELDS'));
            }

        } else {
            if (empty($messageData['subject'])) {
                return new Jaws_Error(_t('PRIVATEMESSAGE_MESSAGE_INCOMPLETE_FIELDS'));
            }
        }

        $mTable = $table->table('pm_messages');
        //Start Transaction
        $mTable->beginTransaction();

        $messageIds = array();
        $data = array();
        $data['subject']            = $messageData['subject'];
        $data['body']               = $messageData['body'];
        $data['attachments']        = isset($messageData['attachments'])? count($messageData['attachments']) : 0;
        $data['recipient_users']    = $messageData['recipient_users'];
        $data['recipient_groups']   = isset($messageData['recipient_groups'])? $messageData['recipient_groups'] : null;
        $data['update_time']        = time();

        // Detect notification, draft or publish?
        $is_notification = ($messageData['folder'] == PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS);
        if($messageData['published']) {
            $data['folder'] = $is_notification?
                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS :
                PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX;
            // Send new message
            if(empty($messageData['id'])) {
                // First we must insert a record to messages table for sender
                if ($is_notification) {
                    $senderMessageId = 0;
                } else {
                    $data['from'] = $user;
                    $data['to'] = 0;
                    $data['insert_time'] = time();
                    $senderMessageId = $mTable->insert($data)->exec();
                }
            // Send old message
            } else {
                $mTable->update($data)->where('id', $messageData['id'])->exec();
                $senderMessageId = $messageData['id'];
            }

            // Insert records for every recipient users
            if (!empty($recipient_users) && count($recipient_users) > 0) {
                $table = $table->table('pm_messages');
                $target_folder = $is_notification?
                    PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS :
                    PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX;
                $from = $is_notification? 0 : $user;
                foreach ($recipient_users as $recipient_user) {
                    $data['folder'] = $target_folder;
                    $data['insert_time'] = time();
                    $data['from'] = $from;
                    $data['to'] = $recipient_user;
                    $messageId = $table->insert($data)->exec();
                    if (Jaws_Error::IsError($messageId)) {
                        //Rollback Transaction
                        $table->rollback();

                        return false;
                    }

                    $messageIds[] = $messageId;
                }
            }

        } else {
            $data['folder'] = PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT;
            // save new draft message
            if(empty($messageData['id'])) {
                $data['from'] = $user;
                $data['to'] = 0;
                $data['insert_time'] = time();
                $senderMessageId = $mTable->insert($data)->exec();

                // update old message info
            } else {
                $senderMessageId = $messageData['id'];
                $mTable->update($data)->where('id', $senderMessageId)->exec();
            }

        }

        // Insert attachments info
        if (!empty($messageData['attachments']) && count($messageData['attachments']) > 0) {
            $maData = array();
            $pm_dir = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR;
            foreach ($messageData['attachments'] as $attachment) {

                // check new attachments file -- we must copy tmp files to correct location
                if (is_array($attachment)) {
                    $src_filepath = Jaws_Utils::upload_tmp_dir() . '/' . $attachment['filename'];
                    $dest_filepath = $pm_dir . $attachment['filename'];

                    if (!file_exists($src_filepath)) {
                        continue;
                    }

                    if (!file_exists($pm_dir)) {
                        if (!Jaws_Utils::mkdir($pm_dir)) {
                            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', JAWS_DATA));
                        }
                    }

                    $cres = Jaws_Utils::rename($src_filepath, $dest_filepath);
                    Jaws_Utils::delete($src_filepath);

                    if ($cres) {
                        $aData = array(
                            'title'         => $attachment['title'],
                            'filename'      => $attachment['filename'],
                            'filesize'      => $attachment['filesize'],
                            'filetype'      => $attachment['filetype'],
                        );

                        $table = $table->table('pm_attachments');
                        $attachmentId = $table->insert($aData)->exec();
                        if (Jaws_Error::IsError($attachmentId)) {
                            //Rollback Transaction
                            $table->rollback();
                            return false;
                        }

                        // Add sender message Id to pm_message_attachment table
                        $maData[] = array('message' => $senderMessageId, 'attachment' => $attachmentId);
                        // Add recipient message Id to pm_message_attachment table
                        foreach($messageIds as $messageId) {
                            $maData[] = array('message' => $messageId, 'attachment' => $attachmentId);
                        }
                    }

                } else {
                    // Add sender message Id to pm_message_attachment table
                    $maData[] = array('message' => $senderMessageId, 'attachment' => $attachment);
                    // Add recipient message Id to pm_message_attachment table
                    foreach($messageIds as $messageId) {
                        $maData[] = array('message' => $messageId, 'attachment' => $attachment);
                    }
                }

            }

            if (!empty($maData) && count($maData) > 0) {
                $table = $table->table('pm_message_attachment');
                $res = $table->insertAll(array('message', 'attachment'), $maData)->exec();
                if (Jaws_Error::IsError($res)) {
                    //Rollback Transaction
                    $table->rollback();
                    return false;
                }
            } else {
                //Rollback Transaction
                $table->rollback();
                return false;
            }
        }

        //Commit Transaction
        $mTable->commit();
        return $senderMessageId;
    }

    /**
     * Get Messages
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   integer  $folder    Folder
     * @param   array    $filters   Search filters
     * @param   int      $limit     Count of posts to be returned
     * @param   int      $offset    Offset of data array
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetMessages($user, $folder = null, $filters = null, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname', 'pm_messages.read:boolean', 'users.username as from_username',
            'pm_messages.attachments:integer', 'from:integer', 'to:integer', 'folder:integer'
        );
        $table->join('users', 'pm_messages.from', 'users.id', 'left');
        if (!empty($folder)) {
            $table->and()->where('pm_messages.folder', $folder);
        }

        switch ($folder) {
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS :
                $table->and()->where('pm_messages.to', (int)$user);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX :
                $table->and()->where('pm_messages.to', (int)$user);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX :
                $table->and()->where('pm_messages.from', (int)$user)->and()->where('pm_messages.to', 0);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT :
                $table->and()->where('pm_messages.from', (int)$user)->and()->where('pm_messages.to', 0);
                break;
            default :
                $table->and()->openWhere()->openWhere('pm_messages.from', (int)$user)->and()
                             ->closeWhere('pm_messages.to', 0);
                $table->or()->where('pm_messages.to', (int)$user)->closeWhere();
                break;
        }

        if (!empty($filters)) {
            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_messages.read', true);
                } else {
                    $table->and()->where('pm_messages.read', false);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('pm_messages.attachments', 0, '>');
                } else {
                    $table->and()->where('pm_messages.attachments', 0);
                }
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $filters['term'] = '%' . $filters['term'] . '%';
                $table->and()->openWhere('pm_messages.subject', $filters['term'], 'like')->or();
                $table->closeWhere('pm_messages.body', $filters['term'], 'like');
            }
        }

        $result = $table->orderBy('pm_messages.insert_time desc')->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get Messages Statistics
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   integer  $folder    Folder
     * @param   array    $filters   Search filters
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetMessagesStatistics($user, $folder = null, $filters = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select('count(id):integer');

        if (!empty($folder)) {
            $table->and()->where('pm_messages.folder', $folder);
        }
        switch ($folder) {
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS :
                $table->and()->where('pm_messages.to', (int)$user);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX :
                $table->and()->where('pm_messages.to', (int)$user);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX :
                $table->and()->where('pm_messages.from', (int)$user)->and()->where('pm_messages.to', 0);
                break;
            case PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT :
                $table->and()->where('pm_messages.from', (int)$user)->and()->where('pm_messages.to', 0);
                break;
            default :
                $table->and()->openWhere()->openWhere('pm_messages.from', (int)$user)->and()
                    ->closeWhere('pm_messages.to', 0);
                $table->or()->where('pm_messages.to', (int)$user)->closeWhere();
                break;
        }


        if (!empty($filters)) {
            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_messages.read', true);
                } else {
                    $table->and()->where('pm_messages.read', false);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('pm_messages.attachments', 0, '>');
                } else {
                    $table->and()->where('pm_messages.attachments', 0);
                }
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $filters['term'] = '%' . $filters['term'] . '%';
                $table->and()->openWhere('pm_messages.subject', $filters['term'], 'like')->or();
                $table->closeWhere('pm_messages.body', $filters['term'], 'like');
            }
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}