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
     * @param   string      $contactType    Notification type (email, mobile, ...)
     * @param   int         $status         Message status(0: all status)
     * @param   bool|int    $limit          Count of messages to be returned
     * @param   int         $offset         Offset of data array
     * @return bool True or error
     */
    function GetNotifications($contactType, $status = 0, $limit = false, $offset = null)
    {
        $objORM = Jaws_ORM::getInstance();
        $objORM = $objORM->table('notification_recipient');

        return Jaws_ORM::getInstance()
            ->table('notification_recipient')
            ->select(
                'notification_recipient.id:integer', 'message', 'contact', 'notification_message.time:integer',
                'notification_message.shouter', 'notification_message.name', 'notification_message.title',
                'notification_message.summary', 'notification_message.verbose', 'notification_message.callback',
                'notification_message.image'
            )
            ->join('notification_message', 'notification_message.id', 'notification_recipient.message')
            ->where('driver', $contactType)
            ->and()
            ->where('notification_recipient.status', (int)$status, '=', empty($status))
            ->and()
            ->where('notification_message.time', time(), '<=')
            ->limit((int)$limit, $offset)
            ->orderBy('notification_message.time, message asc')->fetchAll();
    }

    /**
     * Get notification message details
     *
     * @access  public
     * @param   int     $id      Message id
     * @return bool True or error
     */
    function GetNotificationMessageDetails($id)
    {
        return Jaws_ORM::getInstance()->table('notification_message')
            ->select(
                'shouter', 'name', 'title', 'summary', 'verbose',
                'callback', 'image', 'notification_recipient.driver:integer', 'notification_recipient.status:integer',
                'notification_recipient.contact', 'notification_recipient.time as last_try_time:integer',
                'notification_recipient.attempts:integer'
            )->join('notification_recipient', 'notification_recipient.message', 'notification_message.id')
            ->where('notification_recipient.id', (int)$id)
            ->fetchRow();
    }

    /**
     * Get notification messages
     *
     * @access  public
     * @param   array       $filters
     * @param   bool|int    $limit     Count of quotes to be returned
     * @param   int         $offset    Offset of data array
     * @param   string      $orderBy   Order by
     * @return bool True or error
     */
    function GetNotificationMessages(
        $filters, $limit = false, $offset = null, $orderBy = 'notification_message.time desc'
    ) {
        // from date
        $objDate = Jaws_Date::getInstance();
        if (!empty($filters['from_date'])) {
            $filters['from_date'] = $objDate->ToBaseDate(
                preg_split('/[\/\- :]/', $filters['from_date'] . ' 0:0:0')
            );
            $filters['from_date'] = $this->app->UserTime2UTC($filters['from_date']['timestamp']);
        }
        // to date
        if (!empty($filters['to_date'])) {
            $filters['to_date'] = $objDate->ToBaseDate(
                preg_split('/[\/\- :]/', $filters['to_date'] . ' 0:0:0')
            );
            $filters['to_date'] = $this->app->UserTime2UTC($filters['to_date']['timestamp']);
        }

        return Jaws_ORM::getInstance()
            ->table('notification_message')
            ->select(
                'notification_recipient.id:integer','shouter', 'name', 'title as message_title', 'summary',
                'verbose', 'callback', 'image', 'notification_recipient.driver:integer',
                'notification_recipient.attempts:integer', 'notification_recipient.status:integer',
                'notification_message.time:integer'
            )->join('notification_recipient', 'notification_recipient.message', 'notification_message.id')
            ->and()->where(
                'shouter',
                $filters['shouter'],
                '=',
                empty($filters['shouter'])
            )->and()->where(
                'notification_recipient.driver',
                (int)$filters['driver'],
                '=',
                empty($filters['driver'])
            )->and()->where(
                'notification_recipient.status',
                (int)$filters['status'],
                '=',
                empty($filters['status'])
            )->and()->where(
                'notification_recipient.contact',
                $filters['contact'],
                'like',
                empty($filters['contact'])
            )->and()->where(
                'notification_message.verbose',
                trim(json_encode($filters['verbose']), '"'),
                'like',
                empty($filters['verbose'])
            )->and()->where(
                'notification_message.time',
                $filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'notification_message.time',
                $filters['to_date'],
                '<=',
                empty($filters['to_date'])
            )->orderBy($orderBy)
            ->limit((int)$limit, $offset)
            ->fetchAll();
    }

    /**
     * Get notification messages
     *
     * @access  public
     * @param   array       $filters
     * @return bool True or error
     */
    function GetMessagesCount($filters)
    {
        // from date
        $objDate = Jaws_Date::getInstance();
        if (!empty($filters['from_date'])) {
            $filters['from_date'] = $objDate->ToBaseDate(
                preg_split('/[\/\- :]/', $filters['from_date'] . ' 0:0:0')
            );
            $filters['from_date'] = $this->app->UserTime2UTC($filters['from_date']['timestamp']);
        }
        // to date
        if (!empty($filters['to_date'])) {
            $filters['to_date'] = $objDate->ToBaseDate(
                preg_split('/[\/\- :]/', $filters['to_date'] . ' 0:0:0')
            );
            $filters['to_date'] = $this->app->UserTime2UTC($filters['to_date']['timestamp']);
        }

        return Jaws_ORM::getInstance()
            ->table('notification_message')
            ->select('count(notification_message.id):integer')
            ->join('notification_recipient', 'notification_recipient.message', 'notification_message.id')
            ->and()->where(
                'shouter',
                $filters['shouter'],
                '=',
                empty($filters['shouter'])
            )->and()->where(
                'notification_recipient.driver',
                (int)$filters['driver'],
                '=',
                empty($filters['driver'])
            )->and()->where(
                'notification_recipient.status',
                (int)$filters['status'],
                '=',
                empty($filters['status'])
            )->and()->where(
                'notification_recipient.contact',
                $filters['contact'],
                'like',
                empty($filters['contact'])
            )->and()->where(
                'notification_message.verbose',
                trim(json_encode($filters['verbose']), '"'),
                'like',
                empty($filters['verbose'])
            )->and()->where(
                'notification_message.time',
                $filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'notification_message.time',
                $filters['to_date'],
                '<=',
                empty($filters['to_date'])
            )->fetchOne();
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
                'image'    => $image,
                'time'     => $time
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
                            'contact' => $email, 'hash' => $hash
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
                            'contact' => $mobile, 'hash' => $hash
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
                            'contact' => $webpush, 'hash' => $hash
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
     * @param   int     $messageId  Message id (if passed we need check and delete only this message)
     * @return  bool    True or error
     */
    function DeleteOrphanedMessages($messageId = 0)
    {
        $msgTable = Jaws_ORM::getInstance()->table('notification_message');
        $rcpTable = Jaws_ORM::getInstance()->table('notification_recipient');
        $rcpTable->select('notification_recipient.message')->where(
            'notification_message.id', $msgTable->expr('notification_recipient.message')
        );

        $msgTable->delete()->where('', $rcpTable, 'not exists');
        if ($messageId > 0) {
            $msgTable->and()->where('id', $messageId);
        }
        return $msgTable->exec();
    }

    /**
     * Delete message recipient
     *
     * @access  public
     * @param   int     $recipientId            Recipient id
     * @param   bool    $deleteSimilarMessage   Delete similar messages ?
     * @return  bool    True or error
     */
    function DeleteMessageRecipient($recipientId, $deleteSimilarMessage = false)
    {
        $objORM = Jaws_ORM::getInstance();
        $messageId = $objORM->table('notification_recipient')
            ->select('message:integer')
            ->where('id', (int)$recipientId)->fetchOne();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }

        $rcpTable = $objORM->table('notification_recipient')->delete();
        if ($deleteSimilarMessage) {
            $rcpTable->where('message', $messageId);
        } else {
            $rcpTable->where('id', (int)$recipientId);
        }
        $res = $rcpTable->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $res = $this->DeleteOrphanedMessages($messageId);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return true;
    }

    /**
     * Update notifications status by id
     *
     * @access  public
     * @param   string  $contactType    Contact type (email, mobile, ...)
     * @param   array   $ids            Notifications Id
     * @param   int     $status         Message status(1: not send, 2: sending, 3: sent)
     * @param   bool    $incAttempts    Increase attempts count
     * @return  bool    True or error
     */
    function UpdateNotificationsStatusById($contactType, $ids, $status = 3, $incAttempts = false)
    {
        if (empty($ids)) {
            return true;
        }

        return Jaws_ORM::getInstance()
            ->table('notification_recipient')
            ->update(
                array(
                    'attempts' => Jaws_ORM::getInstance()->expr('attempts + ?', $incAttempts? 1 : 0),
                    'time'     => time(),
                    'status'   => (int)$status
                )
            )
            ->where('id', $ids, 'in')
            ->and()
            ->where('driver', $contactType)
            ->exec();
    }
}