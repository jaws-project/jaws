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

            $model = $this->gadget->model->load('Notification');
            foreach ($drivers as $driver) {
                $objDriver = $objDModel->LoadNotificationDriver($driver['name']);
                $dType = $objDriver->getType();
                switch ($dType) {
                    case Jaws_Notification::EML_DRIVER:
                        $limit = (int)$this->gadget->registry->fetch('eml_fetch_limit');
                        break;

                    case Jaws_Notification::SMS_DRIVER:
                        $limit = (int)$this->gadget->registry->fetch('sms_fetch_limit');
                        break;

                    case Jaws_Notification::WEB_DRIVER:
                        $limit = (int)$this->gadget->registry->fetch('web_fetch_limit');
                        break;
                }

                // fetch notifications
                $messages = $model->GetNotifications($dType, 1, $limit);
                if (Jaws_Error::IsError($messages)) {
                    return $messages;
                }

                if (!empty($messages)) {
                    // set notifications status to sending(2)
                    $model->UpdateNotificationsStatusById($dType, array_column($messages, 'id'), 2, true);

                    // group notifications by message id
                    $messages = $this->GroupByMessages($messages);
                    foreach ($messages as $msgid => $message) {
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

                        // set notifications status
                        $model->UpdateNotificationsStatusById(
                            $dType,
                            $message['ids'],
                            Jaws_Error::IsError($res)? 1 : 3
                        );
                    }
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