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
class EventsCalendar_Model_Events extends Jaws_Gadget_Model
{
    /**
     * Fetches list of events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetEvents($user = null, $shared = null, $foreign = null, $query = null,
        $limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        // $table->select('event.id', 'user', 'subject', 'location', 'description', 'shared',
            // 'createtime', 'updatetime', 'nickname', 'username');
        // $table->join('ec_users', 'event.id', 'event');
        // $table->join('users', 'owner', 'users.id');
        $table->select('event.id', 'user', 'subject', 'location', 'description', 'shared',
            'start_time');

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

        if ($query !== null){
            $query = "%$query%";
            $table->openWhere('subject', $query, 'like')->or();
            $table->closeWhere('content', $query, 'like');
        }

        $table->limit($limit, $offset);
        $table->orderBy('createtime desc', 'subject asc');
        return $table->fetchAll();
    }

    /**
     * Fetches number of total events
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetNumberOfEvents($user = null, $shared = null, $foreign = null, $query = null)
    {
        $table = Jaws_ORM::getInstance()->table('ec_events as event');
        $table->select('count(event.id)');
        $table->join('ec_users', 'event.id', 'event');
        $table->join('users', 'owner', 'users.id');

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

        if ($query !== null){
            $query = "%$query%";
            $table->openWhere('subject', $query, 'like')->or();
            $table->closeWhere('description', $query, 'like');
        }

        $table->orderBy('createtime desc', 'subject asc');
        return $table->fetchOne();
    }

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
        // $table->select('event.id', 'user', 'subject', 'description', 'shared',
            // 'createtime', 'updatetime', 'nickname', 'username');
        // $table->join('ec_users', 'event.id', 'event');
        // $table->join('users', 'owner', 'users.id');
        $table->select('event.id', 'user', 'subject', 'location', 'description', 
            'type', 'priority', 'reminder', 'shared',
            'minute', 'hour', 'week_day', 'month_day', 'month',
            'start_time', 'stop_time', 'createtime', 'updatetime');
        $table->where('event.id', $id)->and();
        if ($user !== null){
            $table->where('user', $user)->and();
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
     * @param   array   $data   Event data
     * @return  mixed   Query result
     */
    function Insert($data)
    {
        $date = $GLOBALS['app']->loadDate();
        $start_date = $date->ToBaseDate(preg_split('/[- :]/', $data['start_date']), 'Y-m-d');
        $data['start_date'] = $GLOBALS['app']->UserTime2UTC($start_date);
        if (empty($data['stop_date'])) {
            $data['stop_date'] = $data['start_date'];
        } else {
            $stop_date = $date->ToBaseDate(preg_split('/[- :]/', $data['stop_date']), 'Y-m-d');
            $data['stop_date'] = $GLOBALS['app']->UserTime2UTC($stop_date);
        }
        $data['createtime'] = $data['updatetime'] = time();
        $data['start_time'] = $data['start_time'] * 3600;
        $data['stop_time'] = $data['stop_time'] * 3600;

        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $id = $table->insert($data)->exec();
        if (Jaws_Error::IsError($id)) {
            return $id;
        }

        $data = array(
            'event' => $id,
            'user' => $data['user'],
            'owner' => (int)$GLOBALS['app']->Session->GetAttribute('user')
        );
        $table = Jaws_ORM::getInstance()->table('ec_users');
        $res = $table->insert($data)->exec();
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
        $date = $GLOBALS['app']->loadDate();
        $start_time = $date->ToBaseDate(preg_split('/[- :]/', $data['start_time']), 'Y-m-d H:i');
        $data['start_time'] = $GLOBALS['app']->UserTime2UTC($start_time);
        if (empty($data['stop_time'])) {
            $data['stop_time'] = $data['start_time'];
        } else {
            $stop_time = $date->ToBaseDate(preg_split('/[- :]/', $data['stop_time']), 'Y-m-d H:i');
            $data['stop_time'] = $GLOBALS['app']->UserTime2UTC($stop_time);
        }
        $data['updatetime'] = time();
        unset($data['repeat']);

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