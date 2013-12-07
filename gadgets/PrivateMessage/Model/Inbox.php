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
     * @param   array    $filters   Search filters
     * @param   int      $limit     Count of posts to be returned
     * @param   int      $offset    Offset of data array
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetInbox($user, $filters = null, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages', 'message');
        $table->select(
            'message.id:integer','message.subject', 'message.body', 'message.insert_time',
            'users.nickname as from_nickname', 'pm_recipients.read:boolean', 'users.username as from_username',
            'message.attachments:integer', 'pm_recipients.id as message_recipient_id:integer'
        );
        $table->join('users', 'message.user', 'users.id');
        $table->join('pm_recipients', 'message.id', 'pm_recipients.message');
        $table->and()->where('pm_recipients.recipient', $user);
        $table->and()->where('message.published', true);

        if (!empty($filters)) {

            if (isset($filters['type']) && ($filters['type'] !== "")) {
                $table->and()->where('message.type', $filters['type']);
            }

            if (isset($filters['archived']) && ($filters['archived'] !== "")) {
                $table->and()->where('pm_recipients.archived', $filters['archived']);
            }

            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_recipients.read', true);
                } else {
                    $table->and()->where('pm_recipients.read', false);
                }
            }
            if (isset($filters['replied']) && !empty($filters['replied'])) {
//                $subTable = Jaws_ORM::getInstance()->table('pm_messages')->select('count(id):integer')->where('parent', $table->expr('message.id'));
                $subTable = Jaws_ORM::getInstance()->table('pm_messages')->select('count(id):integer')->where('parent', array('message.id', 'expr'));
                if ($filters['replied'] == 'yes') {
                    $table->and()->where($subTable, 0, '>');
                } else {
                    $table->and()->where($subTable, 0);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('message.attachments', 0, '>');
                } else {
                    $table->and()->where('message.attachments', 0);
                }
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $filters['term'] = '%' . $filters['term'] . '%';
                $table->and()->openWhere('message.subject', $filters['term'] , 'like')->or();
                $table->closeWhere('message.body', $filters['term'] , 'like');
            }
        }

        $result = $table->orderBy('insert_time desc')->limit($limit, $offset)->fetchAll();
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
        $table = Jaws_ORM::getInstance()->table('pm_messages', 'message');
        $table->select('count(message.id):integer');
        $table->join('pm_recipients', 'message.id', 'pm_recipients.message');
        $table->and()->where('pm_recipients.recipient', $user);
        $table->and()->where('message.published', true);

        if (!empty($filters)) {

            if (isset($filters['type']) && ($filters['type'] !== "")) {
                $table->and()->where('message.type', $filters['type']);
            }

            if (isset($filters['archived']) && ($filters['archived'] !== "")) {
                $table->and()->where('pm_recipients.archived', $filters['archived']);
            }

            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_recipients.read', true);
                } else {
                    $table->and()->where('pm_recipients.read', false);
                }
            }
            if (isset($filters['replied']) && !empty($filters['replied'])) {
                $subTable = Jaws_ORM::getInstance()->table('pm_messages')->select('count(id):integer')->where(
                    'parent', array('message.id', 'expr'));
                if ($filters['replied'] == 'yes') {
                    $table->and()->where($subTable, 0, '>');
                } else {
                    $table->and()->where($subTable, 0);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('message.attachments', 0, '>');
                } else {
                    $table->and()->where('message.attachments', 0);
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