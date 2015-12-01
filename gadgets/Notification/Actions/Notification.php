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
        $emailItems = $model->GetNotifications(Notification_Info::NOTIFICATION_TYPE_EMAIL, $emailLimit);
        if (Jaws_Error::IsError($emailItems)) {
            $this->gadget->registry->update('processing', 'false');
            return $emailItems;
        }

        $mobileLimit = (int)$this->gadget->registry->fetch('mobile_pop_count');
        $mobileItems = $model->GetNotifications(Notification_Info::NOTIFICATION_TYPE_MOBILE, $mobileLimit);
        if (Jaws_Error::IsError($mobileItems)) {
            $this->gadget->registry->update('processing', 'false');
            return $mobileItems;
        }

        // send notification to drivers
        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $options = unserialize($this->gadget->registry->fetch($driver . '_options'));
            $driverObj = Jaws_Notification::getInstance($driver, $options);
            if ($options['type'] == Notification_Info::NOTIFICATION_TYPE_EMAIL) {
                $driverObj->notify($emailItems);
            } else if ($options['type'] == Notification_Info::NOTIFICATION_TYPE_MOBILE) {
                $driverObj->notify($mobileItems);
            }
        }

        // finish procession
        $this->gadget->registry->update('processing', 'false');
        return true;
    }
}