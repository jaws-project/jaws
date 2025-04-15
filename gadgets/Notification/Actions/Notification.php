<?php
/**
 * Notification Gadget
 *
 * @category    Gadget
 * @package     Subscription
 */
class Notification_Actions_Notification extends Jaws_Gadget_Action
{
    /**
     * Send notifications in queue
     *
     * @access  public
     * @return  boolean
     */
    function SendNotifications()
    {
        try {
            // start processing
            if (!Jaws_Mutex::getInstance('Notification')->acquire(true)) {
                $lastUpdate = (int)$this->gadget->registry->fetch('last_update');
                $queueMaxTime = (int)$this->gadget->registry->fetch('queue_max_time');
                if ($lastUpdate + $queueMaxTime < time()) {
                    throw new Exception('time out');
                }

                return false;
            }

            // last entering process
            $this->gadget->registry->update('last_update', time());

            // send notification to drivers
            $objDModel = $this->gadget->model->load('Drivers');
            $drivers = $objDModel->GetNotificationDrivers(true);
            if (Jaws_Error::IsError($drivers)) {
                throw new Exception($drivers->getMessage(), $drivers->getCode());
            }

            $fetch_limits = array(
                Jaws_Notification::EML_DRIVER => (int)$this->gadget->registry->fetch('eml_fetch_limit'),
                Jaws_Notification::SMS_DRIVER => (int)$this->gadget->registry->fetch('sms_fetch_limit'),
                Jaws_Notification::WEB_DRIVER => (int)$this->gadget->registry->fetch('web_fetch_limit'),
                Jaws_Notification::APP_DRIVER => (int)$this->gadget->registry->fetch('app_fetch_limit'),
            );

            $model = $this->gadget->model->load('Notification');
            foreach ($drivers as $driver) {
                $objDriver = $objDModel->LoadNotificationDriver($driver['name']);
                if (Jaws_Error::IsError($objDriver)) {
                    continue;
                }
                $dType = $objDriver->getType();

                // fetch notifications
                $messages = $model->GetNotifications($driver['id'], $dType, 1, $fetch_limits[$dType]);
                if (Jaws_Error::IsError($messages)) {
                    throw new Exception($messages->getMessage(), $messages->getCode());
                }

                if (empty($messages)) {
                    continue;
                }

                // set notifications status to sending
                $model->UpdateNotificationsStatusById(
                    array_column($messages, 'id'),
                    array(
                        'status' => Notification_Info::MESSAGE_STATUS_SENDING,
                        'incAttempts' => true,
                    )
                );

                // group notifications by message id
                $messages = $this->GroupByMessages($messages);
                foreach ($messages as $msgid => $message) {
                    $updateParams = array();
                    // if expired
                    if (!empty($message['expiry']) && $message['expiry'] <= time()) {
                        $updateParams = array(
                            'status' => Notification_Info::MESSAGE_STATUS_EXPIRED
                        );
                    } else {
                        $res = $objDriver->notify(
                            $message['shouter'],
                            $message['name'],
                            $message['contacts'],
                            $message['title'],
                            $message['summary'],
                            $message['verbose'],
                            json_decode($message['variables'], true),
                            $message['time'],
                            $message['callback'],
                            $message['image']
                        );
                        if (Jaws_Error::IsError($res)) {
                            $updateParams = array(
                                'status' => $res->getCode(),
                                'comment' => $res->getMessage(),
                            );
                        } else {
                            $updateParams = array(
                                'status' => Notification_Info::MESSAGE_STATUS_SENT,
                                'comment' => '',
                            );
                        }
                    }

                    // set notifications status
                    $model->UpdateNotificationsStatusById(
                        $message['ids'],
                        $updateParams
                    );
                }
            }
        } catch (Exception $e) {
            //
        }

        // finish procession
        Jaws_Mutex::getInstance('Notification')->release();
        return true;
    }


    /**
     * Group messages by message id
     *
     * @access  public
     * @param   array   $messages   Notification messages
     * @return  array   Grouped Messages
     */
    function GroupByMessages(&$messages)
    {
        $lastMessage = 0;
        $groupedMessages = array();
        foreach ($messages as $message) {
            if ($lastMessage != $message['message']) {
                $lastMessage = $message['message'];
                $groupedMessages[$lastMessage]['time']     = $message['time'];
                $groupedMessages[$lastMessage]['expiry']   = $message['expiry'];
                $groupedMessages[$lastMessage]['shouter']  = $message['shouter'];
                $groupedMessages[$lastMessage]['name']     = $message['name'];
                $groupedMessages[$lastMessage]['title']    = $message['title'];
                $groupedMessages[$lastMessage]['summary']  = $message['summary'];
                $groupedMessages[$lastMessage]['verbose']  = $message['verbose'];
                $groupedMessages[$lastMessage]['variables']= $message['variables'];
                $groupedMessages[$lastMessage]['callback'] = $message['callback'];
                $groupedMessages[$lastMessage]['image']    = $message['image'];
            }
            $groupedMessages[$lastMessage]['ids'][] = $message['id'];
            $groupedMessages[$lastMessage]['contacts'][] = $message['contact'];
        }

        return $groupedMessages;
    }

    /**
     * Update session WebPush subscription endpoint
     *
     * @access  public
     * @return  void
     */
    function UpdateWebPushSubscription($subscription = null)
    {
        if (empty($subscription)) {
            $subscription = $this->gadget->request->fetch(
                array('endpoint', 'keys:array', 'contentEncoding'),
                'post'
            );
        }

        $this->app->session->webpush = $subscription;
        return true;
    }

}