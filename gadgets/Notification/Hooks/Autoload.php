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

        // Delete orphaned messages
        if (mt_rand(1, 32) == mt_rand(1, 32)) {
            $this->DeleteOrphanedMessages();
        }
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


    /**
     * Delete orphaned messages
     *
     * @access  public
     * @return  void
     */
    function DeleteOrphanedMessages()
    {
        $model = $this->gadget->model->load('Notification');
        $model->DeleteOrphanedMessages();
    }

}