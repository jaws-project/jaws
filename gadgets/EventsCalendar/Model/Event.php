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
class EventsCalendar_Model_Event extends Jaws_Gadget_Model
{
    /**
     * Fetches data of passed event
     *
     * @access  public
     * @param   int     $id     Event ID
     * @param   int     $user   User ID
     * @return  mixed   Query result
     */
    function GetEvent($id, $user = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('event.id', 'subject', 'location', 'description', 
            'start_time', 'stop_time', 'recurrence',
            'month', 'day', 'wday', 'type', 'priority', 'reminder', 'shared', 
            'createtime', 'updatetime', 'nickname', 'username', 'ec_users.user');
        $table->join('ec_users', 'event.id', 'event');
        $table->join('users', 'owner', 'users.id');
        $table->where('event.id', $id)->and();
        if ($user !== null){
            $table->where('ec_users.user', $user)->and();
        }

        return $table->fetchRow();
    }

    /**
     * Checks the user of passed events
     *
     * @access  public
     * @param   int     $parent  Restricts results to a specified event
     * @return  array   Query result
     */
    function CheckEvents($id_set, $user)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->select('id');
        $table->where('id', $id_set, 'in')->and();
        $table->where('user', $user);
        return $table->fetchColumn();
    }

    /**
     * Inserts a new event
     *
     * @access  public
     * @param   array   $event   Event data
     * @return  mixed   Query result
     */
    function InsertEvent($event)
    {
        $jdate = $GLOBALS['app']->loadDate();

        $start_time = $jdate->ToBaseDate(
            preg_split('/[- :]/',
            $event['start_date'] . ' ' . $event['start_time']),
            'Y-m-d H:s'
        );
        $event['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time);
        unset($event['start_date']);

        $stop_time = $jdate->ToBaseDate(
            preg_split('/[- :]/',
            $event['stop_date'] . ' ' . $event['stop_time']),
            'Y-m-d H:s'
        );
        $event['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time);
        unset($event['stop_date']);

        //_log_var_dump($start_date);
        $event['createtime'] = $event['updatetime'] = time();

        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $id = $table->insert($event)->exec();
        if (Jaws_Error::IsError($id)) {
            return $id;
        }

        $event = array(
            'event' => $id,
            'user' => $event['user'],
            'owner' => (int)$GLOBALS['app']->Session->GetAttribute('user')
        );
        $table = Jaws_ORM::getInstance()->table('ec_users');
        $res = $table->insert($event)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $table->commit();
    }

    /**
     * Inserts event recurrences
     *
     * @access  public
     * @param   array   $data   Event data
     * @return  mixed   Query result
     */
    function InsertRecurrences($event_id, $first_occurrence, $interval, $duration, $count)
    {
        $data = array();
        for ($i = 0; $i <= $count - 1; $i++) {
            $start = $first_occurrence + ($i * $interval);
            $data[] = array(
                'event_id' => $event_id,
                'start' => $start,
                'stop' => $start + $duration
            );
        }

        $table = Jaws_ORM::getInstance()->table('ec_recurrence');
        $table->beginTransaction();
        $res = $table->insertAll(array('event_id', 'start', 'stop'), $data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $table->commit();
    }

    /**
     * Updates event
     *
     * @access  public
     * @param   int     $id     Event ID
     * @param   array   $data   Event data
     * @return  mixed   Query result
     */
    function Update($id, $data)
    {
        $jdate = $GLOBALS['app']->loadDate();
        $start_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['start_date']), 'Y-m-d');
        $data['start_date'] = $GLOBALS['app']->UserTime2UTC($start_date);
        $stop_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['stop_date']), 'Y-m-d 23:59:59');
        $data['stop_date'] = $GLOBALS['app']->UserTime2UTC($stop_date);
        $time = explode(':', $data['start_time']);
        $data['start_time'] = $time[0] * 3600 + $time[1];
        $time = explode(':', $data['stop_time']);
        $data['stop_time'] = $time[0] * 3600 + $time[1];
        $data['updatetime'] = time();

        $table = Jaws_ORM::getInstance()->table('ec_events');
        return $table->update($data)->where('id', $id)->exec();
    }

    /**
     * Deletes event(s)
     *
     * @access  public
     * @param   array   $id_set  Set of event IDs
     * @return  mixed   Query result
     */
    function Delete($id_set)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $res = $table->delete()->where('id', $id_set, 'in')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Delete shares
        $table = Jaws_ORM::getInstance()->table('ec_users');
        return $table->delete()->where('event', $id_set, 'in')->exec();
    }
}