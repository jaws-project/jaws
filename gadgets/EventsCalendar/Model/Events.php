<?php
/**
 * EventsCalendar Gadget
 *
 * @category    GadgetModel
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Model_Events extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = null, $query = null, $shared = null, $foreign = null,
        $start = null, $stop = null, $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('event.id', 'event.user', 'subject', 'location', 'description',
            'start_date', 'stop_date', 'start_time', 'stop_time', 'shared', 'nickname', 'username');
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
            $query = "%$query%";
            $table->openWhere('subject', $query, 'like')->or();
            $table->where('location', $query, 'like')->or();
            $table->closeWhere('description', $query, 'like')->and();
        }

        $jdate = $GLOBALS['app']->loadDate();
        if (!empty($start)){
            $start = $jdate->ToBaseDate(preg_split('/[- :]/', $start), 'Y-m-d');
            $start = $GLOBALS['app']->UserTime2UTC($start);
            $table->where('stop_date', $start, '>')->and();
        }
        if (!empty($stop)){
            $stop = $jdate->ToBaseDate(preg_split('/[- :]/', $stop), 'Y-m-d');
            $stop = $GLOBALS['app']->UserTime2UTC($stop);
            $table->where('start_date', $stop, '<');
        }

        $table->limit($limit, $offset);
        $table->orderBy('createtime desc', 'subject asc');
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
            $query = "%$query%";
            $table->openWhere('subject', $query, 'like')->or();
            $table->where('location', $query, 'like')->or();
            $table->closeWhere('description', $query, 'like')->and();
        }

        $jdate = $GLOBALS['app']->loadDate();
        if (!empty($start)){
            $start = $jdate->ToBaseDate(preg_split('/[- :]/', $start), 'Y-m-d');
            $start = $GLOBALS['app']->UserTime2UTC($start);
            $table->where('stop_date', $start, '>')->and();
        }
        if (!empty($stop)){
            $stop = $jdate->ToBaseDate(preg_split('/[- :]/', $stop), 'Y-m-d');
            $stop = $GLOBALS['app']->UserTime2UTC($stop);
            $table->where('start_date', $stop, '<');
        }

        return $table->fetchOne();
    }
}