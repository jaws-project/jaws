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
class EventsCalendar_Model_Calendar extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = null, $shared = null, $foreign = null, $start, $stop)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'shared', 'type', 'priority',
            'recs.start_time', 'recs.stop_time', 'ec_users.user', 'owner');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->join('users', 'owner', 'users.id');

        if ($user !== null){
            $table->where('ec_users.user', $user)->and();
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
     * Fetches number of events per month
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetYearEvents($user = null, $shared = null, $foreign = null, $year)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $jdate = Jaws_Date::getInstance();
        $eventsByMonth = array();
        for ($m = 1; $m <= 12; $m++) {
            $table->reset();
            $table->select('events.id');
            $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
            $table->join('ec_users', 'events.id', 'ec_users.event');

            if ($user !== null){
                $table->where('ec_users.user', $user)->and();
            }

            if ($shared === true){
                $table->where('shared', true)->and();
                $table->where('events.user', $user)->and();
            }

            if ($foreign === true){
                $table->where('ec_users.owner', $user, '<>')->and();
            }

            $daysInMonth = $jdate->monthDays($year, $m);
            $start = $jdate->ToBaseDate($year, $m, 1);
            $start = $GLOBALS['app']->UserTime2UTC($start['timestamp']);
            $stop = $jdate->ToBaseDate($year, $m, $daysInMonth, 23, 59, 59);
            $stop = $GLOBALS['app']->UserTime2UTC($stop['timestamp']);
            $table->where('recs.start_time', $stop, '<')->and();
            $table->where('recs.stop_time', $start, '>');
            $table->groupBy('events.id');
            $eventsByMonth[$m] = count($table->fetchAll());
        }

        return $eventsByMonth;
    }
}