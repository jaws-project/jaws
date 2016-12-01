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
class EventsCalendar_Model_Today extends Jaws_Gadget_Model
{
    /**
     * Fetches list of today events
     *
     * @access  public
     * @param   int     $user User ID
     * @param   int     $start  Today start
     * @param   int     $stop   Today end
     * @return  array   Query result
     */
    function GetUserEvents($user, $start, $stop)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'shared:boolean', 'type', 'priority', 'events.public',
            'recs.start_time', 'recs.stop_time', 'ec_users.user', 'owner', 'location');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->join('users', 'ec_users.user', 'users.id');
        $table->where('ec_users.user', $user)->and();
        $table->where('recs.start_time', $stop, '<')->and();
        $table->where('recs.stop_time', $start, '>');
        $table->orderBy('recs.start_time', 'priority');

        return $table->fetchAll();
    }

    /**
     * Fetches list of today events
     *
     * @access  public
     * @param   int     $start  Today start
     * @param   int     $stop   Today end
     * @return  array   Query result
     */
    function GetPublicEvents($start, $stop)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'shared:boolean', 'type', 'priority', 'events.public',
            'recs.start_time', 'recs.stop_time', 'ec_users.user', 'owner', 'location');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->where('ec_users.owner', 0)->and();
        $table->where('recs.start_time', $stop, '<')->and();
        $table->where('recs.stop_time', $start, '>');
        $table->orderBy('recs.start_time', 'priority');

        return $table->fetchAll();
    }
}