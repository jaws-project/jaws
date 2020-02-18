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
        $objORM = $objORM->table('notification_recipient');

        return $objORM->select('id:integer', 'message', 'contact', 'time:integer')
            ->where('driver', $contactType)
            ->and()
            ->where('time', time(), '<=')
            ->limit($limit)
            ->orderBy('time, message asc')->fetchAll();
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
        return Jaws_ORM::getInstance()->table('notification_message')
            ->select('shouter', 'name', 'title', 'summary', 'verbose', 'callback', 'image')
            ->where('id', $id)->fetchRow();
    }


    /**
     * Insert notifications to db
     *
     * @access  public
     * @param   int         $key                Notifications key
     * @param   array       $notifications      Notifications items (for example array('emails'=>array(...))
     * @param   string      $shouter            Shouter(gadget) name
     * @param   string      $name               Notifications name
     * @param   string      $title              Title
     * @param   string      $summary            Summary
     * @param   string      $verbose            Verbose
     * @param   integer     $time               Publish timestamps
     * @param   string      $callback           Callback URL
     * @param   string      $image              Path of image
     * @return  bool        True or error
     */
    function InsertNotifications(
        $key, $notifications, $shouter, $name, $title, $summary, $verbose, $time, $callback, $image
    ) {
        if (empty($notifications) || (
            empty($notifications['emails']) &&
            empty($notifications['webpush']) &&
            empty($notifications['mobiles'])
        )) {
            return false;
        }

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $mTable = $objORM->table('notification_message');
        $messageId = $mTable->upsert(
            array(
                'key'      => $key,
                'shouter'  => $shouter,
                'name'     => $name,
                'title'    => $title,
                'summary'  => $summary,
                'verbose'  => $verbose,
                'callback' => $callback,
                'image'    => $image
            )
        )->and()->where('key', $key)->exec();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }

        // insert email items
        if (!empty($notifications['emails'])) {
            $objORM = $objORM->table('notification_recipient');
            foreach ($notifications['emails'] as $email) {
                // FIXME : increase performance by adding upsertAll method in core
                $hash = hash64($email);
                $res = $objORM->upsert(
                        array(
                            'message' => $messageId, 'driver' => Jaws_Notification::EML_DRIVER,
                            'contact' => $email, 'hash' => $hash, 'time' => $time
                        )
                    )->and()
                    ->where('message', $messageId)
                    ->and()
                    ->where('hash', $hash)
                    ->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        // insert mobile items
        if(!empty($notifications['mobiles'])) {
            $objORM = $objORM->table('notification_recipient');
            foreach ($notifications['mobiles'] as $mobile) {
                // FIXME : increase performance by adding upsertAll method in core
                $hash = hash64($mobile);
                $row['message'] = $messageId;
                $res = $objORM->upsert(
                        array(
                            'message' => $messageId, 'driver' => Jaws_Notification::SMS_DRIVER,
                            'contact' => $mobile, 'hash' => $hash, 'time' => $time
                        )
                    )->and()
                    ->where('message', $messageId)
                    ->and()
                    ->where('hash', $hash)
                    ->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        // insert web_push items
        if(!empty($notifications['webpush'])) {
            $objORM = $objORM->table('notification_recipient');
            foreach ($notifications['webpush'] as $webpush) {
                // FIXME : increase performance by adding upsertAll method in core
                $hash = hash64($webpush);
                $row['message'] = $messageId;
                $res = $objORM->upsert(
                        array(
                            'message' => $messageId, 'driver' => Jaws_Notification::WEB_DRIVER,
                            'contact' => $webpush, 'hash' => $hash, 'time' => $time
                        )
                    )->and()
                    ->where('message', $messageId)
                    ->and()
                    ->where('hash', $hash)
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

        $messageId = $objORM->table('notification_message')->select('id:integer')->where('key', $key)->fetchOne();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }
        if (empty($messageId)) {
            return false;
        }

        // delete recipient records
        $table = $objORM->table('notification_recipient');
        $res = $table->delete()->where('message', $messageId)->exec();
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
        $objORM = $objORM->table('notification_recipient');
        return $objORM->delete()
            ->where('id', $ids, 'in')
            ->and()
            ->where('driver', $contactType)
            ->exec();
    }


    /**
     * Delete orphaned messages
     *
     * @access  public
     * @return  bool    True or error
     */
    function DeleteOrphanedMessages()
    {
        $msgTable = Jaws_ORM::getInstance()->table('notification_message');
        $rcpTable = Jaws_ORM::getInstance()->table('notification_recipient');
        $rcpTable->select('notification_recipient.message')->where(
            'notification_message.id', $msgTable->expr('notification_recipient.message')
        );

        return $msgTable->delete()->where('', $rcpTable, 'not exists')->exec();
    }

}