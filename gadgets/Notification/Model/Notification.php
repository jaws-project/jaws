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

        return $nTable->select(array('id', 'message', 'contact'))
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
     * @param   int         $key                Notifications key
     * @param   string      $title              Title
     * @param   string      $summary            Summary
     * @param   string      $description        Description
     * @return  bool        True or error
     */
    function InsertNotifications($notifications, $key, $title, $summary, $description)
    {
        if (empty($notifications) || (empty($notifications['emails']) && empty($notifications['mobiles']))) {
            return false;
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $mTable = $objORM->table('notification_messages');
        $messageId = $mTable->upsert(
            array('key' => $key, 'title' => $title, 'summary' => $summary, 'description' => $description))
            ->and()->where('key', $key)
            ->exec();

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
                    ->and()->where('message', $row['message'])
                    ->and()->where('contact', $row['contact'])
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
                    ->and()->where('message', $row['message'])
                    ->and()->where('contact', $row['contact'])
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
        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        $messageId = $objORM->table('notification_messages')->select('id:integer')->where('key', $key)->fetchOne();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }
        if (empty($messageId)) {
            return false;
        }

        // delete email records
        $table = $objORM->table('notification_email');
        $res = $table->delete()->where('message', $messageId)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // delete mobile records
        $table = $objORM->table('notification_mobile');
        $res = $table->delete()->where('message', $messageId)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // delete message
        $table = $objORM->table('notification_messages');
        $res = $table->delete()->where('id', $messageId)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // commit Transaction
        $objORM->commit();
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


    /**
     * Delete orphaned messages
     *
     * @access  public
     * @return  bool    True or error
     */
    function DeleteOrphanedMessages()
    {
        $table = Jaws_ORM::getInstance()->table('notification_messages');
        $eTable = Jaws_ORM::getInstance()->table('notification_email')->select('message')->distinct();
        $mTable = Jaws_ORM::getInstance()->table('notification_mobile')->select('message')->distinct();

        return $table->delete()->where('id', $eTable, 'not in')->and()->where('id', $mTable, 'not in')->exec();
    }
}