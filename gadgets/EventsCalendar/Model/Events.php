<?php
/**
 * EventsCalendar Gadget
 *
 * @category    GadgetModel
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Model_Events extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   array   $params     Search query
     * @return  array   Query result
     */
//    function GetEvents($user = null, $query = null, $shared = null, $foreign = null,
//        $start = null, $stop = null, $limit = 0, $offset = null)
//    {
    function GetEvents($params)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('event.id', 'event.user', 'subject', 'location', 'description',
            'start_time', 'stop_time', 'shared:boolean', 'nickname', 'username');
        $table->join('ec_users', 'event.id', 'event');
        $table->join('users', 'owner', 'users.id');

        if ($params['user'] !== null){
            $table->where('event.user', $params['user'])->and();
        }

        if (isset($params['shared']) && $params['shared'] === true){
            $table->where('shared', true)->and();
            $table->where('event.user', $params['user'])->and();
        }

        if (isset($params['foreign']) && $params['foreign'] === true){
            $table->where('ec_users.owner', $params['user'], '<>')->and();
        }

        $search = $params['search'];
        if (!empty($search)){
            foreach ($search as $s) {
                if (isset($s['all'])) {
                    $table->openWhere('subject', $s['all'], 'like')->or();
                    $table->where('location', $s['all'], 'like')->or();
                    $table->closeWhere('description', $s['all'], 'like')->and();
                    break;
                } else {
                    switch ($s['field']) {
                        case 'subject':
                        case 'location':
                        case 'description':
                            $table->where($s['field'], $s['value'], 'like')->and();
                            break;

                        case 'shared':
                            $table->where($s['field'], $s['value'])->and();
                            break;

                        case 'type':
                        case 'priority':
                            $table->where($s['field'], $s['value'])->and();
                            break;

                        case 'date':
                        case 'time':
                            // TODO: implement search by date/time
                            break;
                    }
                }
            }
        }

        $jDate = Jaws_Date::getInstance();
        if (!empty($start)){
            $start = $jDate->ToBaseDate(preg_split('/[- :]/', $start), 'Y-m-d');
            $start = $GLOBALS['app']->UserTime2UTC($start);
            $table->where('stop_time', $start, '>')->and();
        }
        if (!empty($stop)){
            $stop = $jDate->ToBaseDate(preg_split('/[- :]/', $stop), 'Y-m-d');
            $stop = $GLOBALS['app']->UserTime2UTC($stop);
            $table->where('start_time', $stop, '<');
        }

        $table->limit($params['limit'], $params['offset']);

        $orderBy = 'id desc';
        if (!empty($params['sort'])){
            $orderBy = $params['sort'][0]['field'] . ' ' . $params['sort'][0]['direction'];
        }
        $table->orderBy($orderBy, 'subject asc');

        return $table->fetchAll();
    }

    /**
     * Fetches number of total events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetNumberOfEvents($user = null, $query = null,
        $shared = null, $foreign = null, $start = null, $stop = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('count(event.id)');
        $table->join('ec_users', 'event.id', 'event');
        $table->join('users', 'owner', 'users.id');

        if ($user !== null){
            $table->where('ec_users.user', $user)->and();
        }

        if ($shared === true){
            $table->where('shared', true)->and();
            $table->where('event.user', $user)->and();
        }

        if ($foreign === true){
            $table->where('ec_users.owner', $user, '<>')->and();
        }

       if ($query !== null){
            $table->openWhere('subject', $query, 'like')->or();
            $table->where('location', $query, 'like')->or();
            $table->closeWhere('description', $query, 'like')->and();
        }

        $jdate = Jaws_Date::getInstance();
        if (!empty($start)){
            $start = $jdate->ToBaseDate(preg_split('/[- :]/', $start), 'Y-m-d');
            $start = $GLOBALS['app']->UserTime2UTC($start);
            $table->where('stop_time', $start, '>')->and();
        }
        if (!empty($stop)){
            $stop = $jdate->ToBaseDate(preg_split('/[- :]/', $stop), 'Y-m-d');
            $stop = $GLOBALS['app']->UserTime2UTC($stop);
            $table->where('start_time', $stop, '<');
        }

        return $table->fetchOne();
    }
}