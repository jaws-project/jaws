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
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname', 'pm_recipients.read:boolean', 'users.username as from_username',
            'pm_recipients.id as message_recipient_id:integer'
        );
        $table->join('users', 'pm_messages.user', 'users.id');
        $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message');
        $table->where('pm_recipients.recipient', $user)->and()->where('pm_messages.published', true);

        if (!empty($filters)) {
            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_recipients.read', true);
                } else {
                    $table->and()->where('pm_recipients.read', false);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('pm_messages.attachments', 0, '>');
                } else {
                    $table->and()->where('pm_messages.attachments', 0);
                }
            }
            if (isset($filters['filter']) && !empty($filters['filter'])) {
                $table->and()->where('pm_messages.subject', '%' . $filters['filter'] . '%', 'like');
            }
        }

        $result = $table->orderBy('insert_time desc')->limit($limit, $offset)->fetchAll();
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
     * @param   array    $filters   Search filters
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetInboxStatistics($user, $filters = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->select('count(message):integer')->where('recipient', $user);
        $table->join('pm_messages', 'pm_messages.id', 'pm_recipients.message');
        $table->and()->where('pm_messages.published', true);

        if (!empty($filters)) {
            if (isset($filters['read']) && !empty($filters['read'])) {
                if ($filters['read'] == 'yes') {
                    $table->and()->where('pm_recipients.read', true);
                } else {
                    $table->and()->where('pm_recipients.read', false);
                }
            }
            if (isset($filters['attachment']) && !empty($filters['attachment'])) {
                if ($filters['attachment'] == 'yes') {
                    $table->and()->where('pm_messages.attachments', 0, '>');
                } else {
                    $table->and()->where('pm_messages.attachments', 0);
                }
            }
            if (isset($filters['filter']) && !empty($filters['filter'])) {
                $table->and()->where('pm_messages.subject', '%' . $filters['filter'] . '%', 'like');
            }
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

 }