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
        $table->select('events.id', 'subject', 'shared',
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
        // FIXME: we don't have daysInMonth
        $daysInMonth = 30;
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $jdate = $GLOBALS['app']->loadDate();
        for ($m = 1; $m <= 12; $m++) {
            $table->reset();
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

            $start = $jdate->ToBaseDate($year, $m, 1);
            $stop = $jdate->ToBaseDate($year, $m, $daysInMonth, 23, 59, 59);
            $table->where('start_date', $stop['timestamp'], '<')->and();
            $table->where('stop_date', $start['timestamp'], '>')->and();
            $table->openWhere('month', 0)->or();
            $table->closeWhere('month', $m);
            $eventsByMonth[$m] = (int)$table->fetchOne();
        }

        return $eventsByMonth;
    }
}