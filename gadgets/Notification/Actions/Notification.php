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
     * @return  void
     */
    function SendNotifications()
    {
        $processing = $this->gadget->registry->fetch('processing');
        $lastUpdate = $this->gadget->registry->fetch('last_update');
        if ($processing == 'true' && $lastUpdate + 1800 < time()) {
            return false;
        }

        $this->gadget->registry->update('last_update', time());
        $this->gadget->registry->update('processing', 'true');

        $model = $this->gadget->model->load('Notification');
        $emailItems = $model->GetNotifications('email');
        if (Jaws_Error::IsError($emailItems)) {
            return $emailItems;
        }

        $mobileItems = $model->GetNotifications('mobile');
        if (Jaws_Error::IsError($mobileItems)) {
            return $mobileItems;
        }

        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $options = unserialize($this->gadget->registry->fetch($driver . '_options'));
            $driverObj = Jaws_Notification::getInstance($driver, $options);
            if ($options['type'] == 'email') {
                $driverObj->notify($emailItems);
            } else if ($options['type'] == 'mobile') {
                $driverObj->notify($mobileItems);
            }
        }
    }
}