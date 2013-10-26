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
        // $table->select('event.id', 'user', 'subject', 'description', 'shared',
            // 'createtime', 'updatetime', 'nickname', 'username');
        // $table->join('ec_users', 'event.id', 'event');
        // $table->join('users', 'owner', 'users.id');
        $table->select('event.id', 'user', 'subject', 'location', 'description', 
            'start_time', 'stop_time', 'start_date', 'stop_date',
            'month', 'day', 'wday', 'type', 'priority',
            'reminder', 'shared', 'createtime', 'updatetime');
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
        $jdate = $GLOBALS['app']->loadDate();
        $start_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['start_date']), 'Y-m-d');
        $data['start_date'] = $GLOBALS['app']->UserTime2UTC($start_date);
        $stop_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['stop_date']), 'Y-m-d 23:59:59');
        $data['stop_date'] = $GLOBALS['app']->UserTime2UTC($stop_date);
        $data['start_time'] = $data['start_time'] * 3600;
        $data['stop_time'] = $data['stop_time'] * 3600;
        $data['createtime'] = $data['updatetime'] = time();

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
        $jdate = $GLOBALS['app']->loadDate();
        $start_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['start_date']), 'Y-m-d');
        $data['start_date'] = $GLOBALS['app']->UserTime2UTC($start_date);
        $stop_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['stop_date']), 'Y-m-d 23:59:59');
        $data['stop_date'] = $GLOBALS['app']->UserTime2UTC($stop_date);
        $data['start_time'] = $data['start_time'] * 3600;
        $data['stop_time'] = $data['stop_time'] * 3600;
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