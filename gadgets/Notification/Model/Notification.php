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
     * @param   integer     $driver     Driver Id
     * @param   string      $type       Notification type (email, mobile, ...)
     * @param   int         $status     Message status(0: all status)
     * @param   bool|int    $limit      Count of messages to be returned
     * @param   int         $offset     Offset of data array
     * @return bool True or error
     */
    function GetNotifications($driver, $type, $status = 0, $limit = false, $offset = null)
    {
        return Jaws_ORM::getInstance()
            ->table('notification_recipient', 'nr')
            ->select(
                'nr.id:integer', 'nr.driver:integer', 'nr.type:integer', 'message', 'contact', 'nm.time:integer', 'nm.expiry:integer',
                'nm.shouter', 'nm.name', 'nm.title',
                'nm.summary', 'nm.verbose', 'nm.variables', 'nm.callback',
                'nm.image'
            )
            ->join('notification_message as nm', 'nm.id', 'nr.message')
            ->where('driver', $driver)
            ->and()
            ->where('type', $type)
            ->and()
            ->where('nr.status', (int)$status, '=', empty($status))
            ->and()
            ->where('nm.time', time(), '<=')
            ->limit((int)$limit, $offset)
            ->orderBy('nm.time, message asc')->fetchAll();
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
        return Jaws_ORM::getInstance()->table('notification_message', 'nm')
            ->select(
                'shouter', 'name', 'title', 'summary', 'verbose',
                'callback', 'image', 'nr.driver:integer', 'nr.type:integer', 'nr.status:integer', 'nr.status_comment',
                'nr.contact', 'nm.time:integer',
                'nr.attempts:integer', 'nr.time as attempt_time:integer'
            )->join('notification_recipient as nr', 'nr.message', 'nm.id')
            ->where('nr.id', (int)$id)
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
        $filters, $limit = false, $offset = null, $orderBy = 'nm.time desc'
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
                preg_split('/[\/\- :]/', $filters['to_date'] . ' 23:59:59')
            );
            $filters['to_date'] = $this->app->UserTime2UTC($filters['to_date']['timestamp']);
        }

        return Jaws_ORM::getInstance()
            ->table('notification_recipient', 'nr')
            ->select(
                'nr.id:integer','nm.shouter', 'nm.name', 'nm.title as message_title',
                'nm.summary', 'nm.callback', 'nm.image', 'nr.driver:integer', 'nr.type:integer',
                'nr.attempts:integer', 'nr.status:integer','nm.time:integer', 'nm.expiry:integer',
                'nd.title as driver_title'
            )
            ->join('notification_message as nm', 'nm.id', 'nr.message')
            ->join('notification_driver as nd', 'nd.id', 'nr.driver', 'left')
            ->and()->where(
                'nm.shouter',
                $filters['shouter'],
                '=',
                empty($filters['shouter'])
            )->and()->where(
                'nr.driver',
                (int)$filters['driver'],
                '=',
                empty($filters['driver'])
            )->and()->where(
                'nr.status',
                (int)$filters['status'],
                '=',
                empty($filters['status'])
            )->and()->where(
                'nr.contact',
                $filters['contact'],
                'like',
                empty($filters['contact'])
            )->and()->where(
                'nm.verbose',
                $filters['verbose'],
                'like',
                empty($filters['verbose'])
            )->and()->where(
                'nm.time',
                $filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'nm.time',
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
                preg_split('/[\/\- :]/', $filters['to_date'] . ' 23:59:59')
            );
            $filters['to_date'] = $this->app->UserTime2UTC($filters['to_date']['timestamp']);
        }

        return Jaws_ORM::getInstance()
            ->table('notification_message', 'nm')
            ->select('count(nm.id):integer')
            ->join('notification_recipient as nr', 'nr.message', 'nm.id')
            ->and()->where(
                'shouter',
                $filters['shouter'],
                '=',
                empty($filters['shouter'])
            )->and()->where(
                'nr.driver',
                (int)$filters['driver'],
                '=',
                empty($filters['driver'])
            )->and()->where(
                'nr.type',
                (int)$filters['type'],
                '=',
                empty($filters['type'])
            )->and()->where(
                'nr.status',
                (int)$filters['status'],
                '=',
                empty($filters['status'])
            )->and()->where(
                'nr.contact',
                $filters['contact'],
                'like',
                empty($filters['contact'])
            )->and()->where(
                'nm.verbose',
                $filters['verbose'],
                'like',
                empty($filters['verbose'])
            )->and()->where(
                'nm.time',
                $filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'nm.time',
                $filters['to_date'],
                '<=',
                empty($filters['to_date'])
            )->fetchOne();
    }

    /**
     * Insert notifications to db
     *
     * @access  public
     * @param   string      $shouter            Shouter(gadget) name
     * @param   string      $name               Notifications name
     * @param   string      $key                Notifications key
     * @param   string      $title              Title
     * @param   string      $summary            Summary
     * @param   string      $verbose            Verbose
     * @param   string      $variables          Variables
     * @param   integer     $time               Publish timestamps
     * @param   integer     $expiry             Expire time
     * @param   string      $callback           Callback URL
     * @param   string      $image              Path of image
     * @return  mixed       Message Id or Jaws_Error
     */
    function addMessage(
        $shouter, $name, $key, $title, $summary,
        $verbose, $variables, $time, $expiry, $callback, $image
    ) {
        $key = hash64($name.'.'.(string)$key);
        return Jaws_ORM::getInstance()->table('notification_message')
            ->upsert(array(
                'hash'     => $key,
                'shouter'  => $shouter,
                'name'     => $name,
                'title'    => $title,
                'summary'  => $summary,
                'verbose'  => $verbose,
                'variables'=> $variables,
                'callback' => $callback,
                'image'    => $image,
                'time'     => $time,
                'expiry'   => $expiry
            ))
            ->where('hash', $key)
            ->and()
            ->where('time', time(), '>')
            ->exec();
    }

    /**
     * Add notification contacts to db
     *
     * @access  public
     * @param   integer     $messageId  Message Id
     * @param   integer     $driverId   Driver Id
     * @param   integer     $driverType Driver type
     * @param   array       $contacts   Notification contacts
     * @return  bool        True or error
     */
    function addNotifications($messageId, $driverId, $driverType, $contacts)
    {
        if (empty($contacts) || empty($driverId) || empty($messageId)) {
            return false;
        }

        $objORM = Jaws_ORM::getInstance()->table('notification_recipient');
        foreach ($contacts as $contact) {
            // FIXME : increase performance by adding upsertAll method in core
            $hash = hash64($contact);
            $res = $objORM->upsert(
                    array(
                        'message' => $messageId,
                        'driver'  => $driverId,
                        'type'    => $driverType,
                        'contact' => $contact,
                        'hash' => $hash
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

        return true;
    }


    /**
     * Delete notifications by key
     *
     * @access  public
     * @param   string  $name   Notification name
     * @param   string  $key    Notification key
     * @return  bool    True or error
     */
    function DeleteNotificationsByKey($name, $key)
    {
        if (empty($key)) {
            return false;
        }

        $key = hash64($name.'.'.(string)$key);
        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $messageId = $objORM->table('notification_message')
            ->select('id:integer')
            ->where('hash', $key)->and()->where('time', time(), '>')
            ->fetchOne();
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }
        if (empty($messageId)) {
            return false;
        }

        // delete recipient records
        $res = $objORM->table('notification_recipient')
            ->delete()
            ->where('message', $messageId)
            ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // delete notification message
        $res = $objORM->table('notification_message')
            ->delete()
            ->where('id', $messageId)
            ->exec();
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
     * Delete orphaned message
     *
     * @access  public
     * @param   int     $messageId  Message id
     * @return  bool    True or error
     */
    function DeleteOrphanedMessage($messageId)
    {
        $rcpTable = Jaws_ORM::getInstance()
            ->table('notification_recipient')
            ->select('notification_recipient.message')
            ->where('notification_message.id', $messageId);

        return Jaws_ORM::getInstance()->table('notification_message')
            ->delete()
            ->where('id', $messageId)
            ->and()
            ->where('', $rcpTable, 'not exists')
            ->exec();
    }

    /**
     * Delete message recipient
     *
     * @access  public
     * @param   int     $recipientId            Recipient id
     * @param   bool    $deleteSimilarMessage   Delete similar messages?
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

        $res = $this->DeleteOrphanedMessage($messageId);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return true;
    }

    /**
     * Update notifications status by id
     *
     * @access  public
     * @param   array   $ids            Notifications Id
     * @param   array   $options        array include status(1: not send, 2: sending, 3: sent), incAttempts, comment
     * @return  bool    True or error
     */
    function UpdateNotificationsStatusById($ids, $options = array())
    {
        if (empty($ids)) {
            return true;
        }

        $defaultOptions = array(
            'status' => 3,
            'comment' => '',
            'incAttempts' => false,
        );
        $options = array_filter(array_merge($defaultOptions, $options));

        $data = array(
            'time' => time(),
            'status' => (int)$options['status']
        );
        if (isset($options['comment'])) {
            $data['status_comment'] = $options['comment'];
        }

        if (isset($options['incAttempts']) && (bool)$options['incAttempts']) {
            $data['attempts'] = Jaws_ORM::getInstance()->expr('attempts + ?', 1);
        }

        return Jaws_ORM::getInstance()
            ->table('notification_recipient')
            ->update($data)
            ->where('id', $ids, 'in')
            ->exec();
    }
}