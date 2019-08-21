<?php
/**
 * Notification LoginUser event
 *
 * @category    Gadget
 * @package     Notification
 */
class Notification_Events_LoginUser extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $user)
    {
        if (isset($user['defaults']['webpush_subscription'])) {
            $subscription = json_decode(base64_decode($user['defaults']['webpush_subscription']), true);
            $this->gadget->action->load('Notification')->UpdateWebPushSubscription($subscription);
        }

        return true;
    }

}