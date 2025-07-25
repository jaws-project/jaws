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
        $this->gadget->export(
            'webpush_enabled',
            $this->gadget->registry->fetch('webpush_enabled')
        );
        // set webpush public key for using in webpush subscription
        $this->gadget->export(
            'webpush_pub_key',
            $this->gadget->registry->fetch('webpush_pub_key')
        );

        // is webpush subscription available?
        $this->gadget->export(
            'webpush_subscription',
            !empty($this->app->session->webpush)
        );

        // it's better this action call by time-based job scheduler
        if ($this->gadget->registry->fetch('internal_auto_send')) {
            $this->SendNotifications();
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

}