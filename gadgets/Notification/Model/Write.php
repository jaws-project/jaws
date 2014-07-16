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
class Notification_Model_Write extends Jaws_Gadget_Model
{
    /**
     * Inserts a new notification
     *
     * @access  public
     * @param   array   $notification   Notification properties:
     *                  [gadget, action, user, title, desc, priority, send]
     * @return  mixed   ID if successful or Jaws_Error on failure
     */
    function InsertNotification($notification)
    {
        $notification['read'] = false;
        $notification['create_time'] = time();
        $table = Jaws_ORM::getInstance()->table('notification');
        return $table->insert($notification)->exec();
    }

    /**
     * Deletes notification
     *
     * @access  public
     * @param   int     $id  Notification ID
     * @return  mixed   True if successful or Jaws_Error on failure
     */
    function DeleteNotification($id)
    {
        $table = Jaws_ORM::getInstance()->table('notification');
        return $table->delete()->where('id', $id)->exec();
    }

    /**
     * Sets notification as read
     *
     * @access  public
     * @param   int     $id  Notification ID
     * @return  mixed   True if successful or Jaws_Error on failure
     */
    function MarkAsRead($id)
    {
        $table = Jaws_ORM::getInstance()->table('notification');
        return $table->update(array('read' => true))->where('id', $id)->exec();
    }

}