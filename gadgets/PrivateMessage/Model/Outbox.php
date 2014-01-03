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
class PrivateMessage_Model_Outbox extends Jaws_Gadget_Model
{
    /**
     * Get Outbox
     *
     * @access  public
     * @param   integer  $user          User id
     * @param   array    $filters       Search filters
     * @param   int      $limit         Count of posts to be returned
     * @param   int      $offset        Offset of data array
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetOutbox($user, $filters = null, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname','attachments:integer'
        );
        $table->join('users', 'pm_messages.user', 'users.id');
        $table->where('pm_messages.user', $user);

        if (!empty($filters)) {
            if (isset($filters['published']) && ($filters['published'] !== "")) {
                $table->and()->where('published', $filters['published']);
            }
            if (isset($filters['replied']) && !empty($filters['replied'])) {
                $subTable = Jaws_ORM::getInstance()->table('pm_messages');
                $subTable->select('count(id)')->where('parent', 'pm_messages.id');

                if ($filters['replied'] == 'yes') {
                    $table->and()->where($subTable, 0, '>');
                } else {
                    $table->and()->where($subTable, 0);
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

        $result = $table->orderBy('insert_time desc')->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get Outbox Statistics
     *
     * @access  public
     * @param   integer  $user          User id
     * @param   array    $filters   Search filters
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetOutboxStatistics($user, $filters)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select('count(id):integer')->where('user', $user);

        if (!empty($filters)) {
            if (isset($filters['published']) && ($filters['published'] !== "")) {
                $table->and()->where('published', $filters['published']);
            }
            if (isset($filters['replied']) && !empty($filters['replied'])) {
                $subTable = Jaws_ORM::getInstance()->table('pm_messages');
                $subTable->select('count(id)')->where('parent', 'pm_messages.id');

                if ($filters['replied'] == 'yes') {
                    $table->and()->where($subTable, 0, '>');
                } else {
                    $table->and()->where($subTable, 0);
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

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

}