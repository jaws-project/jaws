<?php
/**
 * PrivateMessage Notify event
 *
 * @category    Gadget
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Events_Notify extends Jaws_Gadget_Event
{
    /**
     * Grabs notification and stores it in database
     *
     * @access  public
     * @params  array   $params  [user, title, description, priority, send]
     */
    function Execute($shouter, $params)
    {
        // prevent inner loop
        if ($shouter === 'PrivateMessage') {
            return;
        }

        $message = array();
        $message['subject'] = $params['title'];
        $message['body'] = $params['description'];
        $message['folder'] = PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS;
        $message['recipient_users'] = $params['user'];
        $message['read'] = false;

        $model = $this->gadget->model->load('Message');
        $model->SendMessage(0, $message);
    }
}
