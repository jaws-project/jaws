<?php
/**
 * Notification Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    Notification
 */
class Notification_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload function
     *
     * @access  private
     * @return  void
     */
    function Execute()
    {
            $this->AddVisitor();
    }

    /**
     * Send notifications
     *
     * @access  public
     * @return  void
     */
    function AddVisitor()
    {
        $gadget = $this->gadget->action->load('Notifications');
        return $gadget->SendNotifications();
    }

}