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
     * Update users subscriptions
     *
     * @access  public
     * @param   string  $contactType        Notification type (email, mobile, ...)
     * @param   int     $limit              Pop limitation count
     * @return bool True or error
     */
    function GetNotifications($contactType, $limit)
    {
        if ($contactType == Notification_Info::NOTIFICATION_TYPE_EMAIL) {
            $nTable = Jaws_ORM::getInstance()->table('notification_email');
        } else if ($contactType == Notification_Info::NOTIFICATION_TYPE_SMS) {
            $nTable = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $nTable->select('*')->limit($limit)->where('publish_time', time(), '<=')->fetchAll();
    }


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

        if ($contactType == Notification_Info::NOTIFICATION_TYPE_EMAIL) {
            $table = Jaws_ORM::getInstance()->table('notification_email');
        } else if ($contactType == Notification_Info::NOTIFICATION_TYPE_SMS) {
            $table = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        foreach ($notifications as $row) {
            // FIXME : increase performance by adding upsertAll method in core
            $res = $table
                ->upsert($row)
                ->and()->where('key', $row['key'])
                ->and()->where('contact_value', $row['contact_value'])
                ->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
/*            $notificationsItems[] = array(
                'key' => $row['key'],
                'contact_value' => $row['contact_value'],
                'title' => $row['title'],
                'summary' => $row['summary'],
                'description' => $row['description'],
                'publish_time' => $row['publish_time'],
            );*/
        }

        return true;

/*        return $table->insertAll(
                        array('key', 'contact_value', 'title', 'summary', 'description', 'publish_time'),
                        $notificationsItems)->exec();*/

    }


    /**
     * Delete notifications by key
     *
     * @access  public
     * @param   int     $key            Notification key
     * @return  bool    True or error
     */
    function DeleteNotificationsByKey($key)
    {
        if (empty($key)) {
            return false;
        }
        $objORM = Jaws_ORM::getInstance();

        $table = $objORM->table('notification_email');
        $res = $table->delete()->where('key', $key)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $table = $objORM->table('notification_mobile');
        $res = $table->delete()->where('key', $key)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }


    /**
     * Delete notifications by id
     *
     * @access  public
     * @param   string  $contactType    Contact type (email, mobile, ...)
     * @param   array   $ids            Notifications Id
     * @return  bool    True or error
     */
    function DeleteNotificationsById($contactType, $ids)
    {
        if (empty($contactType) || empty($ids)) {
            return false;
        }

        if ($contactType == Notification_Info::NOTIFICATION_TYPE_EMAIL) {
            $table = Jaws_ORM::getInstance()->table('notification_email');
        } else if ($contactType == Notification_Info::NOTIFICATION_TYPE_SMS) {
            $table = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $table->delete()->where('id', $ids, 'in')->exec();
    }
}