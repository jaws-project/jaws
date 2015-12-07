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
     * Get notifications
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
        } else if ($contactType == Notification_Info::NOTIFICATION_TYPE_MOBILE) {
            $nTable = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $nTable->select(array('id', 'message', 'value'))
            ->limit($limit)
            ->where('publish_time', time(), '<=')
            ->orderBy('publish_time, message asc')->fetchAll();
    }


    /**
     * Get notification message
     *
     * @access  public
     * @param   int     $id      Message id
     * @return bool True or error
     */
    function GetNotificationMessage($id)
    {
        return Jaws_ORM::getInstance()->table('notification_messages')
            ->select(array('title', 'summary', 'description'))
            ->where('id', $id)->fetchRow();
    }


    /**
     * Insert notifications to db
     *
     * @access  public
     * @param   array       $notifications      Notifications items (for example array('emails'=>array(...))
     * @param   string      $title              Title
     * @param   string      $summary            Summary
     * @param   string      $description        Description
     * @return  bool        True or error
     */
    function InsertNotifications($notifications, $title, $summary, $description)
    {
        if (empty($notifications) || (empty($notifications['emails']) && empty($notifications['mobiles']))) {
            return false;
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $mTable = $objORM->table('notification_messages');
        $messageId = $mTable->insert(
            array('title' => $title, 'summary' => $summary, 'description' => $description)
        )->exec();

        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }

        // insert email items
        if (!empty($notifications['emails'])) {
            $table = $objORM->table('notification_email');
            foreach ($notifications['emails'] as $row) {
                // FIXME : increase performance by adding upsertAll method in core
                $row['message'] = $messageId;
                $res = $table
                    ->upsert($row)
                    ->and()->where('key', $row['key'])
                    ->and()->where('value', $row['value'])
                    ->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        // insert mobile items
        if(!empty($notifications['mobiles'])) {
            $table = $objORM->table('notification_mobile');
            foreach ($notifications['mobiles'] as $row) {
                // FIXME : increase performance by adding upsertAll method in core
                $row['message'] = $messageId;
                $res = $table
                    ->upsert($row)
                    ->and()->where('key', $row['key'])
                    ->and()->where('value', $row['value'])
                    ->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        //Commit Transaction
        $objORM->commit();

        return true;
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
        } else if ($contactType == Notification_Info::NOTIFICATION_TYPE_MOBILE) {
            $table = Jaws_ORM::getInstance()->table('notification_mobile');
        } else {
            return new Jaws_Error(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $table->delete()->where('id', $ids, 'in')->exec();
    }
}