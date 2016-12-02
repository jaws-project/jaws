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

        $jDate = Jaws_Date::getInstance();
        $search = $params['search'];
        if (!empty($search)){
            foreach ($search as $s) {
                if (isset($s['term'])) {
                    $table->openWhere('subject', $s['term'], 'like')->or();
                    $table->where('location', $s['term'], 'like')->or();
                    $table->closeWhere('description', $s['term'], 'like')->and();
                    break;
                } else {
                    switch ($s['field']) {
                        case 'subject':
                        case 'location':
                        case 'description':
                            $table->where($s['field'], $s['value'], 'like')->and();
                            break;

                        case 'public':
                            $table->where($s['field'], $s['value'])->and();
                            break;

                        case 'shared':
                            $table->where($s['field'], $s['value'])->and();
                            break;

                        case 'type':
                        case 'priority':
                            $table->where($s['field'], $s['value'])->and();
                            break;

                        case 'date':
                            if (!empty($s['value'][0])) {
                                $start = $jDate->ToBaseDate(preg_split('/[- :]/', $s['value'][0]), 'Y-m-d');
                                $start = $GLOBALS['app']->UserTime2UTC($start);
                                $table->where('stop_time', $start, '>')->and();
                            }
                            if (!empty($s['value'][1])) {
                                $stop = $jDate->ToBaseDate(preg_split('/[- :]/', $s['value'][1]), 'Y-m-d');
                                $stop = $GLOBALS['app']->UserTime2UTC($stop);
                                $table->where('start_time', $stop, '<');
                            }
                            break;
                    }
                }
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