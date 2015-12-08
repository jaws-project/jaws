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
        $this->SendNotifications();

        $model = $this->gadget->model->load('Notification');
        $model->DeleteOrphanedMessages();
    }

    /**
     * Send notifications
     *
     * @access  public
     * @return  void
     */
    function SendNotifications()
    {
        $gadget = $this->gadget->action->load('Notification');
        return $gadget->SendNotifications();
    }

}