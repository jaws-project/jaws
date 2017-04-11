<?php
/**
 * EventsCalendar Gadget
 *
 * @category    GadgetModel
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Model_Admin_Events extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   array   $params     Search query
     * @param   bool    $count      Returns number of results
     * @return  array   Query result
     */
    function GetEvents($params, $count = false)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        if ($count) {
            if ($params['user'] === 0) {
                $table->select('count(event.id)');
                $table->join('ec_users', 'event.id', 'event');
            } else {
                $table->select('count(event.id)');
                $table->join('users', 'user', 'users.id');
            }
        } else {
            if ($params['user'] === 0) {
                $table->select('event.id', 'event.user', 'subject', 'location', 'description',
                    'start_time', 'stop_time', 'public:boolean', 'shared:boolean');
                $table->join('ec_users', 'event.id', 'event');
            } else {
                $table->select('event.id', 'event.user', 'subject', 'location', 'description',
                    'start_time', 'stop_time', 'event.public:boolean', 'shared:boolean', 'nickname', 'username');
                $table->join('users', 'user', 'users.id');
            }
        }

        if (isset($params['user'])) {
            $table->where('event.user', $params['user'])->and();
        }

        $objDate = Jaws_Date::getInstance();
        $filters = $params['search'];
        if (!empty($filters) && count($filters) > 0) {
            if (isset($filters['start_time']) && !empty($filters['start_time'])) {
                if (!is_numeric($filters['start_time'])) {
                    $filters['start_time'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['start_time']), 'U')
                    );
                }
                $table->and()->where('start_time', $filters['start_time'], '>=');
            }
            if (isset($filters['stop_time']) && !empty($filters['stop_time'])) {
                if (!is_numeric($filters['stop_time'])) {
                    $filters['stop_time'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['stop_time']), 'U')
                    );
                }
                $table->and()->where('stop_time', $filters['stop_time'], '<=');
            }
            if (isset($filters['subject']) && !empty($filters['subject'])) {
                $table->and()->where('subject', $filters['subject'], 'like');
            }
            if (isset($filters['location']) && !empty($filters['location'])) {
                $table->and()->where('location', $filters['location'], 'like');
            }
            if (isset($filters['description']) && !empty($filters['description'])) {
                $table->and()->where('description', $filters['description'], 'like');
            }
            if (isset($filters['shared']) && $filters['shared'] >= 0) {
                $table->and()->where('shared', $filters['shared']);
            }
            if (isset($filters['type']) && !empty($filters['type'])) {
                $table->and()->where('type', $filters['type']);
            }
            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $table->and()->where('priority', $filters['priority']);
            }
        }

        $table->limit($params['limit'], $params['offset']);

        if (!$count) {
            $orderBy = 'id desc';
            if (!empty($params['sort'])){
                $orderBy = $params['sort'][0]['field'] . ' ' . $params['sort'][0]['direction'];
            }
            $table->orderBy($orderBy, 'subject asc');
            $table->limit($params['limit'], $params['offset']);
        }

        return $count? $table->fetchOne() : $table->fetchAll();
    }
}