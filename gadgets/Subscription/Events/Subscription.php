<?php
/**
 * Subscription event
 *
 * @category    Gadget
 * @package     Subscription
 */
class Subscription_Events_Subscription extends Jaws_Gadget_Event
{
    /**
     * Grabs subscriptions and sends it out via notification gadget
     *
     * @access  public
     * @param   string  $shouter    The shouting gadget
     * @param   array   $params     [user, group, title, summary, description, priority, send]
     */
    function Execute($shouter, $params)
    {
        if (empty($params['action']) || empty($params['reference'])) {
            return;
        }

        $sModel = $this->gadget->model->load('Subscription');
        $usersSubscriptions = $sModel->GetUsersSubscriptions($shouter, $params['action'], $params['reference']);
        if (Jaws_Error::IsError($usersSubscriptions) || count($usersSubscriptions) < 1) {
            return;
        }

        $users = array();
        $emails = array();
        foreach ($usersSubscriptions as $row) {
            if (!empty($row['uid'])) {
                $users[] = $row['uid'];
            } else if (!empty($row['email'])) {
                $emails[] = $row['email'];
            }
        }

        $params = array();
        $params['title'] = _t('SUBSCRIPTION_NOTIFICATION_TITLE');
        $params['summary'] = $params['summary'];
        $params['description'] = $params['text'];
        $params['users'] = $users;
        $params['emails'] = $emails;
        $this->gadget->event->shout('Notify', $params);
    }
}
