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
        $emailItems = $model->GetNotifications(Jaws_Notification::EML_DRIVER, $emailLimit);
        if (Jaws_Error::IsError($emailItems)) {
            $this->gadget->registry->update('processing', 'false');
            return $emailItems;
        }

        $mobileLimit = (int)$this->gadget->registry->fetch('mobile_pop_count');
        $mobileItems = $model->GetNotifications(Jaws_Notification::SMS_DRIVER, $mobileLimit);
        if (Jaws_Error::IsError($mobileItems)) {
            $this->gadget->registry->update('processing', 'false');
            return $mobileItems;
        }

        // send notification to drivers
        $objDModel = $this->gadget->model->load('Drivers');
        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $objDriver = $objDModel->LoadNotificationDriver($driver);
            if (!empty($emailItems) && $objDriver->getType() == Jaws_Notification::EML_DRIVER) {
                $emailItemsChunk = $this->GroupSameMessages($emailItems);
                foreach ($emailItemsChunk as $messageId => $emails) {
                    $message = $model->GetNotificationMessage($messageId);
                    $res = $objDriver->notify($emails, $message['title'], $message['summary'], $message['description']);
                }

                // delete notification
                // FIXME : we can increase the performance
                if (!Jaws_Error::IsError($res)) {
                    $itemsId = array();
                    foreach ($emailItems as $item) {
                        $itemsId[] = $item['id'];
                    }
                    $model->DeleteNotificationsById(Notification_Info::NOTIFICATION_TYPE_EMAIL, $itemsId);
                }
            } else if (!empty($mobileItems) && $objDriver->getType() == Jaws_Notification::SMS_DRIVER) {
                $mobileItemsChunk = $this->GroupSameMessages($mobileItems);
                foreach ($mobileItemsChunk as $messageId => $mobiles) {
                    $message = $model->GetNotificationMessage($messageId);
                    $res = $objDriver->notify(
                        $mobiles,
                        $message['title'],
                        $message['summary'],
                        $message['description']
                    );
                }

                // delete notification
                // FIXME : we can increase the performance
                if (!Jaws_Error::IsError($res)) {
                    $itemsId = array();
                    foreach ($mobileItems as $item) {
                        $itemsId[] = $item['id'];
                    }
                    $model->DeleteNotificationsById(Notification_Info::NOTIFICATION_TYPE_MOBILE, $itemsId);
                }
            }
        }

        // finish procession
        $this->gadget->registry->update('processing', 'false');
        return true;
    }


    /**
     * Group same messages
     *
     * @access  public
     * @param   array       $items    Notification items
     * @return bool
     */
    function GroupSameMessages($items)
    {
        if (empty($items)) {
            return array();
        }

        $messageRecipients = array();
        $lastMessageId = 0;
        foreach ($items as $item) {
            if ($lastMessageId != $item['message']) {
                $lastMessageId = $item['message'];
            }
            $messageRecipients[$lastMessageId][] = $item['contact'];
        }

        return $messageRecipients;
    }
}