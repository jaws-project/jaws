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
class EventsCalendar_Model_Calendar extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = 0, $shared = null, $foreign = null, $start, $stop)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'shared:boolean', 'type', 'priority', 'events.public',
            'recs.start_time', 'recs.stop_time', 'ec_users.user', 'owner');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
//        $table->join('users', 'ec_users.user', 'users.id');

        if ($user === 0){
            $table->where('ec_users.owner', 0)->and();
        } else {
            // fetch user events plus public events of other users
            $table->openWhere('ec_users.user', $user)->or();
            $table->closeWhere('events.public', true)->and();
        }


        if ($shared === true){
            $table->where('shared', true)->and();
            $table->where('events.user', $user)->and();
        }

        if ($foreign === true){
            $table->where('ec_users.owner', $user, '<>')->and();
        }

        $table->where('recs.start_time', $stop, '<')->and();
        $table->where('recs.stop_time', $start, '>');

        $table->orderBy('recs.start_time', 'priority');
        return $table->fetchAll();
    }

    /**
     * Fetches number of events by month for a specific year
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetYearEvents($user = 0, $shared = null, $foreign = null, $year)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $jDate = Jaws_Date::getInstance();
        $eventsByMonth = array();
        for ($m = 1; $m <= 12; $m++) {
            $table->reset();
            $table->select('events.id');
            $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
            $table->join('ec_users', 'events.id', 'ec_users.event');

            if ($user === 0){
                $table->where('ec_users.owner', 0)->and();
            } else {
                // fetch user events plus public events of other users
                $table->openWhere('ec_users.user', $user)->or();
                $table->closeWhere('events.public', true)->and();
            }

//            if ($shared === true){
//                $table->where('shared', true)->and();
//                $table->where('events.user', $user)->and();
//            }

//            if ($foreign === true){
//                $table->where('ec_users.owner', $user, '<>')->and();
//            }

            $daysInMonth = $jDate->monthDays($year, $m);
            $start = $jDate->ToBaseDate($year, $m, 1);
            $start = $GLOBALS['app']->UserTime2UTC($start['timestamp']);
            $stop = $jDate->ToBaseDate($year, $m, $daysInMonth, 23, 59, 59);
            $stop = $GLOBALS['app']->UserTime2UTC($stop['timestamp']);
            $table->where('recs.start_time', $stop, '<')->and();
            $table->where('recs.stop_time', $start, '>');
            $table->groupBy('events.id');
            $eventsByMonth[$m] = count($table->fetchAll());
        }

        return $eventsByMonth;
    }
}