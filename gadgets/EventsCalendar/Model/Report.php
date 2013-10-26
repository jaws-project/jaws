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
class EventsCalendar_Model_Report extends Jaws_Gadget_Model
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
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('event.id', 'subject', 'shared', 'start_time', 'stop_time');

        if ($user !== null){
            $table->where('user', $user)->and();
        }

        if ($shared === true){
            $table->where('shared', true)->and();
            $table->where('user', $user)->and();
        }

        if ($foreign === true){
            $table->where('user', $user, '<>')->and();
        }

        $table->where('start_date', $stop, '<')->and();
        $table->where('stop_date', $start, '>');

        return $table->fetchAll();
    }
}