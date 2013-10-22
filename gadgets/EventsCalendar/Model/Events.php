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
            'start_date', 'stop_date');

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
}