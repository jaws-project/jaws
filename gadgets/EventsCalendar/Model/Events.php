<?php
/**
 * EventsCalendar Gadget
 *
 * @category    GadgetModel
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Model_Events extends Jaws_Gadget_Model
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
                $table->join('ec_users', 'event.id', 'event');
                $table->join('users', 'owner', 'users.id');
            }
        } else {
            if ($params['user'] === 0) {
                $table->select('event.id', 'event.user', 'subject', 'location', 'description',
                    'start_time', 'stop_time', 'public:boolean', 'shared:boolean');
                $table->join('ec_users', 'event.id', 'event');
            } else {
                $table->select('event.id', 'event.user', 'subject', 'location', 'description',
                    'start_time', 'stop_time', 'event.public:boolean', 'shared:boolean', 'nickname', 'username');
                $table->join('ec_users', 'event.id', 'event');
                $table->join('users', 'owner', 'users.id');
            }
        }
        $table->where('ec_users.user', $params['user'])->and();

        $jDate = Jaws_Date::getInstance();
        if (isset($params['search']) && !empty($params['search'])) {
            $search = $params['search'];
            foreach ($search as $key => $value) {
                switch ($key) {
                    case 'term':
                        $table->openWhere('subject', $value, 'like')->or();
                        $table->where('location', $value, 'like')->or();
                        $table->closeWhere('description', $value, 'like')->and();
                        break;

                    case 'public':
                        $table->where('event.public', $value)->and();
                        break;

                    case 'shared':
                        $table->where($key, $value)->and();
                        break;

                    case 'type':
                    case 'priority':
                        $table->where($key, $value)->and();
                        break;

                    case 'start':
                        $start = $jDate->ToBaseDate(preg_split('/[\/\- \:]/', $value), 'Y-m-d');
                        $start = $GLOBALS['app']->UserTime2UTC($start);
                        $table->where('stop_time', $start, '>')->and();
                        break;

                    case 'stop':
                        $stop = $jDate->ToBaseDate(preg_split('/[\/\- \:]/', $value), 'Y-m-d');
                        $stop = $GLOBALS['app']->UserTime2UTC($stop);
                        $table->where('start_time', $stop, '<');
                        break;
                }
            }
        }

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