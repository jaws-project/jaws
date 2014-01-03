<?php
/**
 * EventsCalendar Gadget
 *
 * @category    GadgetModel
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Model_Share extends Jaws_Gadget_Model
{
    /**
     * Fetches list of users the event is shared for
     *
     * @access  public
     * @param   int     $id  Event ID
     * @return  mixed   Query result
     */
    function GetEventUsers($id)
    {
        $table = Jaws_ORM::getInstance()->table('ec_users');
        $table->select('user', 'username', 'nickname');
        $table->join('users', 'user', 'users.id');
        $table->where('event', $id);
        return $table->fetchAll();
    }

    /**
     * Updates users of a event
     *
     * @access  public
     * @param   int     $id     Event ID
     * @param   array   $users  Set of User IDs
     * @return  mixed   True or Jaws_Error
     */
    function UpdateEventUsers($id, $users)
    {
        // Update shared status of the event
        $shared = !empty($users);
        $table = Jaws_ORM::getInstance()->table('ec_events');
        $table->beginTransaction();
        $table->update(array('shared' => $shared));
        $res = $table->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Delete current users except owner
        $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $table = Jaws_ORM::getInstance()->table('ec_users');
        $table->delete()->where('event', $id)->and();
        $res = $table->where('user', $uid, '<>')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Insert users
        if (!empty($users)) {
            foreach ($users as &$user) {
                $user = array(
                    'event' => $id,
                    'user' => $user,
                    'owner' => $uid
                );
            }
            $table = Jaws_ORM::getInstance()->table('ec_users');
            $table->reset();
            $table->insertAll(array('event', 'user', 'owner'), $users);
            $res = $table->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        $table->commit();
    }
}