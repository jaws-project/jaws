<?php
/**
 * Notification Model
 *
 * @category    GadgetModel
 * @package     Notification
 */
class Notification_Model_Notification extends Jaws_Gadget_Model
{
    /**
     * Insert notifications to db
     *
     * @access  public
     * @param   array       $notifications    Notifications
     * @return  bool        True or error
     */
    function InsertNotifications($notifications)
    {
        if (empty($notifications)) {
            return false;
        }

        foreach ($notifications as $row) {
            $notificationsItems[] = array(
                'email' => $row['email'],
                'mobile_number' => $row['mobile_number'],
                'title' => $row['title'],
                'summary' => $row['summary'],
                'description' => $row['description'],
                'insert_time' => time(),
            );
        }

        return Jaws_ORM::getInstance()->table('notification')
            ->insertAll(
                        array('email', 'mobile_number', 'title', 'summary', 'description', 'insert_time'),
                        $notificationsItems)
            ->exec();
    }
}