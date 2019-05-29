<?php
/**
 * Subscription UninstallGadget event
 *
 * @category    Gadget
 * @package     Subscription
 */
class Subscription_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        return $this->gadget->model->loadAdmin('Subscription')->DeleteGadgetSubscriptions($gadget);
    }

}