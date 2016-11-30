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
        $processing = $this->gadget->registry->fetch('processing');
        $lastUpdate = (int)$this->gadget->registry->fetch('last_update');
        $queueMaxTime = (int)$this->gadget->registry->fetch('queue_max_time');
        if ($processing == 'true' && $lastUpdate + $queueMaxTime < time()) {
            return false;
        }

        $this->gadget->registry->update('last_update', time());
        $this->gadget->registry->update('processing', 'true');

        $model = $this->gadget->model->load('Notification');
        $emailLimit = (int)$this->gadget->registry->fetch('email_pop_count');
        // fetches email notification
        $result = $model->GetNotifications(Jaws_Notification::EML_DRIVER, $emailLimit);
        if (Jaws_Error::IsError($result)) {
            $this->gadget->registry->update('processing', 'false');
            return $result;
        }
        $messages[Jaws_Notification::EML_DRIVER] = $this->GroupByMessages($result);

        $mobileLimit = (int)$this->gadget->registry->fetch('mobile_pop_count');
        // fetches SMS notification
        $result = $model->GetNotifications(Jaws_Notification::SMS_DRIVER, $mobileLimit);
        if (Jaws_Error::IsError($result)) {
            $this->gadget->registry->update('processing', 'false');
            return $result;
        }
        $messages[Jaws_Notification::SMS_DRIVER] = $this->GroupByMessages($result);

        // send notification to drivers
        $objDModel = $this->gadget->model->load('Drivers');
        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $objDriver = $objDModel->LoadNotificationDriver($driver);
            $dType = $objDriver->getType();
            if (!empty($messages[$dType])) {
                foreach ($messages[$dType]['grouped'] as $message => $contacts) {
                    $message = $model->GetNotificationMessage($message);
                    $res = $objDriver->notify(
                        $contacts,
                        $message['title'],
                        $message['summary'],
                        $message['description']
                    );
                }
                if (!Jaws_Error::IsError($res)) {
                    // delete notification
                    $model->DeleteNotificationsById($dType, $messages[$dType]['ids']);
                }
            }
        }

        // finish procession
        $this->gadget->registry->update('processing', 'false');
        return true;
    }


    /**
     * Group messages by message id
     *
     * @access  public
     * @param   array   $messages   Notification messages
     * @return  array   Grouped Messages
     */
    function GroupByMessages($messages)
    {
        $lastMessage = 0;
        $idsMessages = array();
        $groupedMessages = array();
        foreach ($messages as $message) {
            if ($lastMessage != $message['message']) {
                $lastMessage = $message['message'];
            }
            $idsMessages[] = $message['id'];
            $groupedMessages[$lastMessage][] = $message['contact'];
        }

        return array(
            'grouped' => $groupedMessages,
            'ids' => $idsMessages
        );
    }
}