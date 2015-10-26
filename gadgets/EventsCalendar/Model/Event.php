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
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        $table->select('events.id', 'subject', 'location', 'description', 
            'start_time', 'stop_time', 'recurrence',
            'month', 'day', 'wday', 'type', 'priority', 'reminder', 'shared', 
            'createtime', 'updatetime', 'nickname', 'username', 'ec_users.user', 'owner');
        $table->join('ec_users', 'events.id', 'event');
        $table->join('users', 'owner', 'users.id');
        $table->where('events.id', $id);
        if ($user !== null){
            $table->and()->where('ec_users.user', $user);
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
        $jdate = Jaws_Date::getInstance();

        $start_time = $jdate->ToBaseDate(
            preg_split('/[- :]/', $event['start_date'] . ' ' . $event['start_time'])
        );
        $event['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time['timestamp']);
        unset($event['start_date']);

        $stop_time = $jdate->ToBaseDate(
            preg_split('/[- :]/', $event['stop_date'] . ' ' . $event['stop_time'])
        );
        $event['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time['timestamp']);
        unset($event['stop_date']);

        $event['createtime'] = $event['updatetime'] = time();
        $event['reminder'] *= 60;

        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $id = $table->insert($event)->exec();
        if (Jaws_Error::IsError($id)) {
            return $id;
        }

        // add user
        $data = array(
            'event' => $id,
            'user' => $event['user'],
            'owner' => (int)$GLOBALS['app']->Session->GetAttribute('user')
        );
        $table = Jaws_ORM::getInstance()->table('ec_users');
        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // create recurrences
        $res = $this->InsertRecurrences($id, $event);
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
     * @param   array   $event   Event event
     * @return  mixed   Query result
     */
    function UpdateEvent($id, $event, $old_event)
    {
        $jdate = Jaws_Date::getInstance();
        $startArr = preg_split('/[- :]/', $event['start_date'] . ' ' . $event['start_time']);

        $start_time = $jdate->ToBaseDate(
            preg_split('/[- :]/', $event['start_date'] . ' ' . $event['start_time'])
        );
        $event['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time['timestamp']);
        unset($event['start_date']);

        $stop_time = $jdate->ToBaseDate(
            preg_split('/[- :]/', $event['stop_date'] . ' ' . $event['stop_time'])
        );
        $event['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time['timestamp']);
        unset($event['stop_date']);

        $event['updatetime'] = time();
        $event['reminder'] *= 60;

        // update event
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $res = $table->update($event)->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // create recurrences
        if ($event['start_time'] != $old_event['start_time'] ||
            $event['stop_time'] != $old_event['stop_time'] ||
            $event['recurrence'] != $old_event['recurrence'] ||
            $event['month'] != $old_event['month'] ||
            $event['wday'] != $old_event['wday'] ||
            $event['day'] != $old_event['day'])
        {
            $res = $this->DeleteRecurrences($id);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
            $res = $this->InsertRecurrences($id, $event);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        $table->commit();
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

        // Delete recurrences
        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $res = $table->delete()->where('event', $id_set, 'in')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Delete shares
        $table = Jaws_ORM::getInstance()->table('ec_users');
        return $table->delete()->where('event', $id_set, 'in')->exec();
    }

    /**
     * Inserts event recurrences
     *
     * @access  public
     * @param   array   $data   Event data
     * @return  mixed   Query result
     */
    function InsertRecurrences($event_id, $event)
    {
        $recArr = array();
        $jdate = Jaws_Date::getInstance();
        $start_info = $jdate->GetDateInfo($event['start_time']);
        $stop_info = $jdate->GetDateInfo($event['stop_time']);
        if ($event['recurrence'] == 0) {
            $recArr[] = $event['start_time'];
        } else {
            switch ((int)$event['recurrence']) {
                case 1: // daily
                    $step = 24 * 60 * 60;
                    if ($event['start_time'] + $step > $event['stop_time']) {
                        $recArr[] = $event['start_time'];
                    } else {
                        $recArr = range($event['start_time'], $event['stop_time'], $step);
                    }
                    break;
                case 2: // weekly
                    $step = 7 * 24 * 60 * 60;
                    // calculate first ocurrence
                    $diff = $event['wday'] - $start_info['wday'] - 1;
                    if ($diff < 0) {
                        $diff += 7;
                    }
                    $start = $event['start_time'] + $diff * 86400;
                    if ($start + $step > $event['stop_time']) {
                        if ($start <= $event['stop_time']) {
                            $recArr[] = $start;
                        }
                    } else {
                        $recArr = range($start, $event['stop_time'], $step);
                    }
                    break;
                case 3: // monthly
                    $startArr[2] = $event['day'];
                    $endMonth = ($stop_info['year'] - $start_info['year']) * 12 + $stop_info['mon'];
                    for ($i = (int)$start_info['mon']; $i <= $endMonth; $i++) {
                        $startArr[1] = $i;
                        $time = $jdate->ToBaseDate($startArr);
                        $iso = $GLOBALS['app']->UserTime2UTC($time['timestamp']);
                        if ($iso < $event['stop_time']) {
                            $recArr[] = $iso;
                        }
                    }
                    break;
                case 4: // yearly
                    $startArr[1] = $event['month'];
                    $startArr[2] = $event['day'];
                    for ($i = $start_info['year']; $i <= $stop_info['year']; $i++) {
                        $startArr[0] = $i;
                        $time = $jdate->ToBaseDate($startArr);
                        $iso = $GLOBALS['app']->UserTime2UTC($time['timestamp']);
                        if ($iso < $event['stop_time']) {
                            $recArr[] = $iso;
                        }
                    }
                    break;
            }
        }

        $time1 = $start_info['hours'] * 3600 + $start_info['minutes'] * 60;
        $time2 = $stop_info['hours'] * 3600 + $start_info['minutes'] * 60;
        $duration = $time2 - $time1;
        
        $data = array();
        foreach ($recArr as $rec) {
            $data[] = array(
                'event' => $event_id,
                'start_time' => $rec,
                'stop_time' => $rec + $duration
            );
        }

        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $table->beginTransaction();
        $res = $table->insertAll(array('event', 'start_time', 'stop_time'), $data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $table->commit();
    }

    /**
     * Deletes event recurrences
     *
     * @access  public
     * @param   array   $data   Event data
     * @return  mixed   Query result
     */
    function DeleteRecurrences($event_id)
    {
        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $table->beginTransaction();
        $res = $table->delete()->where('event', $event_id)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $table->commit();
    }
}