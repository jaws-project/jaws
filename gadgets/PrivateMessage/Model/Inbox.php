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
class PrivateMessage_Model_Inbox extends Jaws_Gadget_Model
{
    /**
     * Get Inbox
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   array    $filters   Search filters
     * @param   int      $limit     Count of posts to be returned
     * @param   int      $offset    Offset of data array
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetInbox($user, $filters = null, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname', 'pm_messages.read:boolean', 'users.username as from_username',
            'pm_messages.attachments:integer'
        );
        $table->join('users', 'pm_messages.from', 'users.id');
        $table->and()->where('pm_messages.to', (int)$user);
        $table->and()->where('pm_messages.folder', PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX);

        if (!empty($filters)) {
            if (isset($filters['folder']) && ($filters['folder'] !== "")) {
                $table->and()->where('pm_messages.folder', $filters['folder']);
            }

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
                $table->and()->openWhere('pm_messages.subject', $filters['term'] , 'like')->or();
                $table->closeWhere('pm_messages.body', $filters['term'] , 'like');
            }
        }

        $result = $table->orderBy('pm_messages.insert_time desc')->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get Inbox Statistics
     *
     * @access  public
     * @param   integer  $user      User id
     * @param   array    $filters   Search filters
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetInboxStatistics($user, $filters = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select('count(id):integer');
        $table->and()->where('to', $user)->and()->where('folder', PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX);

        if (!empty($filters)) {
            if (isset($filters['folder']) && ($filters['folder'] !== "")) {
                $table->and()->where('folder', $filters['folder']);
            }

            if (isset($filters['archived']) && ($filters['archived'] !== "")) {
                $table->and()->where('pm_recipients.archived', $filters['archived']);
            }

            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('read', true);
                } else {
                    $table->and()->where('read', false);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('attachments', 0, '>');
                } else {
                    $table->and()->where('attachments', 0);
                }
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $filters['term'] = '%' . $filters['term'] . '%';
                $table->and()->openWhere('message.subject', $filters['term'] , 'like')->or();
                $table->closeWhere('message.body', $filters['term'] , 'like');
            }
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

 }