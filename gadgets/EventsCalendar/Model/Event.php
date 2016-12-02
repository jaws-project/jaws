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
class EventsCalendar_Model_Event extends Jaws_Gadget_Model
{
    /**
     * Fetches data of passed event
     *
     * @access  public
     * @param   int     $eventId    Event ID
     * @param   int     $userId     User ID
     * @return  mixed   Query result
     */
    function GetEvent($eventId, $userId = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as events');
        if (empty($userId)) {
            $table->select('events.id', 'subject', 'location', 'description',
                'start_time', 'stop_time', 'recurrence', 'month', 'day', 'wday',
                'events.public:boolean', 'type', 'priority', 'reminder', 'shared:boolean',
                'createtime', 'updatetime', 'ec_users.user', 'owner');
            $table->join('ec_users', 'events.id', 'event');
        } else {
            $table->select('events.id', 'subject', 'location', 'description',
                'start_time', 'stop_time', 'recurrence', 'month', 'day', 'wday',
                'events.public:boolean', 'type', 'priority', 'reminder', 'shared',
                'createtime', 'updatetime', 'nickname', 'username', 'ec_users.user', 'owner');
            $table->join('ec_users', 'events.id', 'event');
            $table->join('users', 'owner', 'users.id');
        }
        $table->where('events.id', $eventId);
        if (!empty($userId)){
            $table->and()->where('ec_users.user', $userId);
        }

        return $table->fetchRow();
    }

    /**
     * Checks the user of the specified events
     *
     * @access  public
     * @param   array   $idSet      Set of event IDs
     * @param   int     $userId     User ID
     * @return  array   Query result
     */
    function CheckEvents($idSet, $userId)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->select('id');
        $table->where('id', $idSet, 'in')->and();
        $table->where('user', $userId);
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
        $jDate = Jaws_Date::getInstance();

        $start_time = $jDate->ToBaseDate(
            preg_split('/[- :]/', $event['start_date'] . ' ' . $event['start_time'])
        );
        $event['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time['timestamp']);
        unset($event['start_date']);

        $stop_time = $jDate->ToBaseDate(
            preg_split('/[- :]/', $event['stop_date'] . ' ' . $event['stop_time'])
        );
        $event['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time['timestamp']);
        unset($event['stop_date']);

        $event['createtime'] = $event['updatetime'] = time();
        $event['reminder'] *= 60;
        $owner = $event['owner'];
        unset($event['owner']);

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
            'owner' => $owner,
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

        return $id;
    }

    /**
     * Updates event
     *
     * @access  public
     * @param   int     $id         Event ID
     * @param   array   $event      Event data
     * @param   array   $oldEvent   Old Event data
     * @return  mixed   Query result
     */
    function UpdateEvent($id, $event, $oldEvent)
    {
        $jDate = Jaws_Date::getInstance();

        $start_time = $jDate->ToBaseDate(
            preg_split('/[- :]/', $event['start_date'] . ' ' . $event['start_time'])
        );
        $event['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time['timestamp']);
        unset($event['start_date']);

        $stop_time = $jDate->ToBaseDate(
            preg_split('/[- :]/', $event['stop_date'] . ' ' . $event['stop_time'])
        );
        $event['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time['timestamp']);
        unset($event['stop_date']);

        $event['updatetime'] = time();
        $event['reminder'] *= 60;

        // update event
        $user = $event['user'];
        unset($event['user']);
        unset($event['owner']);
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $res = $table->update($event)->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        // we need the user for sending notifications
        $event['user'] = $user;

        // create recurrences
        if ($event['start_time'] != $oldEvent['start_time'] ||
            $event['stop_time'] != $oldEvent['stop_time'] ||
            $event['recurrence'] != $oldEvent['recurrence'] ||
            $event['month'] != $oldEvent['month'] ||
            $event['wday'] != $oldEvent['wday'] ||
            $event['day'] != $oldEvent['day'])
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

        return $table->commit();
    }

    /**
     * Deletes events
     *
     * @access  public
     * @param   array   $idSet  Set of event IDs
     * @return  mixed   Query result
     */
    function DeleteEvents($idSet)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $res = $table->delete()->where('id', $idSet, 'in')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Delete recurrences
        $this->DeleteRecurrences($idSet);

        // Delete shares
        $table = Jaws_ORM::getInstance()->table('ec_users');
        return $table->delete()->where('event', $idSet, 'in')->exec();
    }

    /**
     * Inserts event recurrences
     *
     * @access  public
     * @param   int     $eventId    Event ID
     * @param   array   $event      Event data
     * @return  mixed   Query result
     */
    function InsertRecurrences($eventId, $event)
    {
        $recArr = array();
        $jDate = Jaws_Date::getInstance();
        $startInfo = $jDate->GetDateInfo($event['start_time']);
        $stopInfo = $jDate->GetDateInfo($event['stop_time']);
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
                    // calculate first occurrence
                    $diff = $event['wday'] - $startInfo['wday'] - 1;
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
                    $endMonth = ($stopInfo['year'] - $startInfo['year']) * 12 + $stopInfo['mon'];
                    for ($i = (int)$startInfo['mon']; $i <= $endMonth; $i++) {
                        $startArr[1] = $i;
                        $time = $jDate->ToBaseDate($startArr);
                        $iso = $GLOBALS['app']->UserTime2UTC($time['timestamp']);
                        if ($iso < $event['stop_time']) {
                            $recArr[] = $iso;
                        }
                    }
                    break;
                case 4: // yearly
                    $startArr[1] = $event['month'];
                    $startArr[2] = $event['day'];
                    for ($i = $startInfo['year']; $i <= $stopInfo['year']; $i++) {
                        $startArr[0] = $i;
                        $time = $jDate->ToBaseDate($startArr);
                        $iso = $GLOBALS['app']->UserTime2UTC($time['timestamp']);
                        if ($iso < $event['stop_time']) {
                            $recArr[] = $iso;
                        }
                    }
                    break;
            }
        }

        $time1 = $startInfo['hours'] * 3600 + $startInfo['minutes'] * 60;
        $time2 = $stopInfo['hours'] * 3600 + $startInfo['minutes'] * 60;
        $duration = $time2 - $time1;
        if ($duration < 0) {
            $duration += 24 * 3600;
        }

        $data = array();
        foreach ($recArr as $rec) {
            $data[] = array(
                'event' => $eventId,
                'start_time' => $rec,
                'stop_time' => $rec + $duration
            );
        }

        if (empty($data)) {
            return true;
        }

        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $table->beginTransaction();
        $res = $table->insertAll(array('event', 'start_time', 'stop_time'), $data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        $table->commit();

        // remind subscribed user(s)
        $recs = $this->GetRecurrences($eventId);
        foreach ($recs as $rec) {
            $notify = array(
                'id' => $rec['id'],
                'key' => crc32('Event' . $rec['id']),
                'subject' => $event['subject'],
                'description' => $event['description'],
                'time' => $rec['start_time'] - $event['reminder'],
                'url' => $event['user'] ?
                    $this->gadget->urlMap('ViewEvent', array('event' => $eventId, 'user' => $event['user']), true) :
                    $this->gadget->urlMap('ViewEvent', array('event' => $eventId), true)
            );
            $this->Notify($notify);
        }

        return true;
    }

    /**
     * Deletes event recurrences
     *
     * @access  public
     * @param   mixed   $eventId    Event ID or array of event IDs
     * @return  mixed   Query result
     */
    function DeleteRecurrences($eventId)
    {
        // delete pending notifications
        $recs = $this->GetRecurrences($eventId);
        foreach ($recs as $rec) {
            $notify = array(
                'id' => $rec['id'],
                'key' => crc32('Event' . $rec['id']),
                'subject' => 'removing event',
                'description' => '',
                'time' => -1,
                'url' => ''
            );
            $this->Notify($notify);
        }

        // delete recurrences
        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $table->beginTransaction();
        if (is_array($eventId)) {
            $res = $table->delete()->where('event', $eventId, 'in')->exec();
        } else {
            $res = $table->delete()->where('event', $eventId)->exec();
        }
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return $table->commit();
    }

    /**
     * Fetches recurrences the specified event
     *
     * @access  public
     * @param   mixed   $eventId    Event ID or array of event IDs
     * @return  mixed   Query result
     */
    function GetRecurrences($eventId)
    {
        $table = Jaws_ORM::getInstance()->table('ec_recurrences');
        $table->select('id', 'start_time');
        if (is_array($eventId)) {
            return $table->where('event', $eventId, 'in')->fetchAll();
        } else {
            return $table->where('event', $eventId)->fetchAll();
        }
    }

    /**
     * Reminds subscribed user(s) for a specific event
     *
     * @access  public
     * @param   array   $event   The event to notify for
     */
    function Notify($event)
    {
        $subscriptionParams = array(
            'action' => 'ViewYear',
            'reference' => $event['id'],
            'key' => $event['key'],
            'summary' => $event['subject'],
            'description' => $event['description'],
            'publish_time' => ($event['time'] == -1)? -1 : strtotime($event['time']),
            'url' => $event['url']
        );
        $this->gadget->event->shout('Subscription', $subscriptionParams);
    }
}