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
     * @param   string      $contactType      Contact type (email, mobile, ...)
     * @param   array       $notifications    Notifications
     * @return  bool        True or error
     */
    function InsertNotifications($contactType, $notifications)
    {
        if (empty($contactType) || empty($notifications)) {
            return false;
        }

        foreach ($notifications as $row) {
            $notificationsItems[] = array(
                'key' => $row['key'],
                'contact_value' => $row['contact_value'],
                'title' => $row['title'],
                'summary' => $row['summary'],
                'description' => $row['description'],
                'publish_time' => $row['publish_time'],
            );
        }

        if ($contactType == 'email') {
            $table = Jaws_ORM::getInstance()->table('notification_email');
        } else if ($contactType == 'mobile') {
            $table = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_INVALID_CONTACT_TYPE'));
        }

        return $table->insertAll(
                        array('key', 'contact_value', 'title', 'summary', 'description', 'publish_time'),
                        $notificationsItems)->exec();
    }
}