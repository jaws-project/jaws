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
class EventsCalendar_Model_Reminder extends Jaws_Gadget_Model
{
    /**
     * Fetches list of user events to be remind
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   int     $time   Current timestamp
     * @return  array   Query result
     */
    function GetUserEvents($user = null, $time)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'type', 'priority',
            'location', 'recs.start_time', 'owner', 'nickname');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->join('users', 'owner', 'users.id');
        $table->where('reminder', 0, '!=')->and();
        $table->where('recs.stop_time', $time, '>')->and();
        $table->where($table->expr('recs.start_time - reminder'), $time, '<=');
        if ($user !== null){
            $table->and()->where('ec_users.user', $user);
        }
        $table->orderBy('recs.start_time', 'priority');

        return $table->fetchAll();
    }

    /**
     * Fetches list of public events to be remind
     *
     * @access  public
     * @param   int     $time   Current timestamp
     * @return  array   Query result
     */
    function GetPublicEvents($time)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'type', 'priority',
            'location', 'recs.start_time', 'owner');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->where('ec_users.user', 0)->and();
        $table->where('reminder', 0, '!=')->and();
        $table->where('recs.stop_time', $time, '>')->and();
        $table->where($table->expr('recs.start_time - reminder'), $time, '<=');
        $table->orderBy('recs.start_time', 'priority');

        return $table->fetchAll();
    }
}