<?php
/**
 * Notification Notify event
 *
 * @category    Gadget
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Notification_Events_Notify extends Jaws_Gadget_Event
{
    /**
     * Stores and sends the notification
     *
     */
    function Execute($gadget, $action, $user, $title, $desc, $priority = 3, $send = true)
    {
        $notification = array(
            'gadget' => $gadget,
            'action' => $action,
            'user' => $user,
            'title' => $title,
            'description' => $desc,
            'priority' => $priority
        );
        $model = $this->gadget->model->load('Write');
        $res = $model->InsertNotification($notification);
    }
}
