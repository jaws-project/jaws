<?php
/**
 * Notification Gadget
 *
 * @category    GadgetModel
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notification_Model_Read extends Jaws_Gadget_Model
{
    /**
     * Fetches notifications
     *
     * @access  public
     * @param   array   $params  Filtering criteria:
     *                  [gadget, action, user, priority, send, limit, offset]
     * @return  array   Array of notifications or Jaws_Error on any error
     */
    function GetNotifications($params)
    {
        $table = Jaws_ORM::getInstance()->table('notification');
        $table->select('id:integer', 'title', 'read:boolean', 'create_time:integer');

        return $table->fetchAll();
    }

    /**
     * Fetches notification properties
     *
     * @access  public
     * @param   int     $id  Notification ID
     * @return  array   Associated array of notification properties or Jaws_Error on error
     */
    function GetNotification($id)
    {
        $table = Jaws_ORM::getInstance()->table('notification');
        $table->select(
            'id:integer', 'user:integer', 'gadget', 'action', 'title',
            'description', 'read:boolean', 'priority:integer', 'create_time:integer'
        );
        return $table->where('id', $id)->fetchRow();
    }

    /**
     * Fetches number of unread notifications for specified user
     *
     * @access  public
     * @param   array   $user   User ID
     * @return  int     Number of unread notifications
     */
    function GetUnreadCount($user)
    {
        $table = Jaws_ORM::getInstance()->table('notification');
        $table->select('count(id):integer');
        $table->where('user', $user);
        $table->and()->where('read', false);
        return $table->fetchOne();
    }

}