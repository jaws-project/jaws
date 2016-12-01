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
        $objORM = Jaws_ORM::getInstance();
        switch ($contactType) {
            case Jaws_Notification::EML_DRIVER:
                $objORM = $objORM->table('notification_email');
                break;
            case Jaws_Notification::SMS_DRIVER:
                $objORM = $objORM->table('notification_mobile');
                break;
            default:
                return Jaws_Error::raiseError(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $objORM->select('id:integer', 'message', 'contact', 'publish_time:integer')
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
            ->select('title', 'summary', 'description')
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
     * @param   integer     $publish_time       Publish timestamps
     * @return  bool        True or error
     */
    function InsertNotifications($notifications, $key, $title, $summary, $description, $publish_time)
    {
        if (empty($notifications) || (empty($notifications['emails']) && empty($notifications['mobiles']))) {
            return false;
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $mTable = $objORM->table('notification_messages');
        $messageId = $mTable->upsert(
            array(
                'key' => $key,
                'title' => $title,
                'summary' => $summary,
                'description' => $description
            )
        )->and()->where('key', $key)->exec();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }

        // insert email items
        if (!empty($notifications['emails'])) {
            $objORM = $objORM->table('notification_email');
            foreach ($notifications['emails'] as $email) {
                // FIXME : increase performance by adding upsertAll method in core
                $res = $objORM->upsert(
                        array('message' => $messageId, 'contact' => $email, 'publish_time' => $publish_time)
                    )
                    ->and()
                    ->where('message', $messageId)
                    ->and()
                    ->where('contact', $email)
                    ->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        // insert mobile items
        if(!empty($notifications['mobiles'])) {
            $objORM = $objORM->table('notification_mobile');
            foreach ($notifications['mobiles'] as $mobile) {
                // FIXME : increase performance by adding upsertAll method in core
                $row['message'] = $messageId;
                $res = $objORM->upsert(
                        array('message' => $messageId, 'contact' => $mobile, 'publish_time' => $publish_time)
                    )
                    ->and()
                    ->where('message', $messageId)
                    ->and()
                    ->where('contact', $mobile)
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
        if (empty($ids)) {
            return true;
        }

        $objORM = Jaws_ORM::getInstance();
        switch ($contactType) {
            case Jaws_Notification::EML_DRIVER:
                $objORM = $objORM->table('notification_email');
                break;
            case Jaws_Notification::SMS_DRIVER:
                $objORM = $objORM->table('notification_mobile');
                break;
            default:
                return Jaws_Error::raiseError(_t('NOTIFICATION_ERROR_INVALID_CONTACT_TYPE'));
        }

        return $objORM->delete()->where('id', $ids, 'in')->exec();
    }


    /**
     * Delete orphaned messages
     *
     * @access  public
     * @return  bool    True or error
     */
    function DeleteOrphanedMessages()
    {
        $msgTable = Jaws_ORM::getInstance()->table('notification_messages');
        $emlTable = Jaws_ORM::getInstance()->table('notification_email')->select('message')->distinct();
        $smsTable = Jaws_ORM::getInstance()->table('notification_mobile')->select('message')->distinct();

        return $msgTable->delete()
            ->where('id', $emlTable, 'not in')
            ->and()
            ->where('id', $smsTable, 'not in')
            ->exec();
    }

}