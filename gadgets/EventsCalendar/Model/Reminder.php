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
class EventsCalendar_Model_Reminder extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events to be remind
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = null, $current_time)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'type', 'priority',
            'recs.start_time', 'owner', 'nickname');
        $table->join('ec_recurrences as recs', 'events.id', 'recs.event');
        $table->join('ec_users', 'events.id', 'ec_users.event');
        $table->join('users', 'owner', 'users.id');
        $table->where('reminder', 0, '<>')->and();
        $table->where('recs.stop_time', $current_time, '>')->and();
        $table->where($table->expr('recs.start_time - reminder'), $current_time, '<=');

        if ($user !== null){
            $table->and()->where('ec_users.user', $user);
        }

        $table->orderBy('recs.start_time', 'priority');
        return $table->fetchAll();
    }
}