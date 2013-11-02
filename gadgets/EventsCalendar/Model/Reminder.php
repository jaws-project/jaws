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
class EventsCalendar_Model_Reminder extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events to be remind
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = null, $current_time, $repeat)
    {
        //_log_var_dump($start . ' - ' . $stop);
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('event.id', 'subject', 'owner', 'nickname',
            'start_date', 'start_time', 'stop_time');
        $table->join('ec_users', 'event.id', 'event');
        $table->join('users', 'owner', 'users.id');
        // $table->where($table->expr('stop_date' + 'stop_time'), $current_time, '>')->and();
        // $table->openWhere($table->expr('start_date' + 'start_time'), $current_time, '>=')->and();
        // $table->closeWhere($table->expr('start_date' + 'start_time'), $current_time, '>=')->and();

        // if ($user !== null){
            // $table->where('ec_users.user', $user)->and();
        // }

        // if (isset($repeat['day'])){
            // $table->openWhere('day', 0)->or();
            // $table->closeWhere('day', $repeat['day'])->and();
        // }

        // if (isset($repeat['wday'])){
            // $table->openWhere('wday', 0)->or();
            // $table->closeWhere('wday', $repeat['wday'])->and();
        // }

        // if (isset($repeat['month'])){
            // $table->openWhere('month', 0)->or();
            // $table->closeWhere('month', $repeat['month'])->and();
        // }

        return $table->fetchAll();
    }
}