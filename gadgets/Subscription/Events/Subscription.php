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
     * @return  bool|void
     */
    function Execute($shouter, $params)
    {
        if (empty($params['action']) || empty($params['reference'])) {
            return false;
        }

        $sModel = $this->gadget->model->load('Subscription');
        $usersSubscriptions = $sModel->GetUsersSubscriptions($shouter, $params['action'], $params['reference']);
        if (Jaws_Error::IsError($usersSubscriptions)) {
            return $usersSubscriptions;
        }
        if (empty($usersSubscriptions)) {
            return false;
        }

        $users = array();
        $emails = array();
        $mobiles = array();
        $webPushes = array();
        foreach ($usersSubscriptions as $row) {
            if (!empty($row['user'])) {
                $users[] = $row['user'];
            }
            if (!empty($row['email'])) {
                $emails[] = $row['email'];
            }
            if (!empty($row['mobile_number'])) {
                $mobiles[] = $row['mobile_number'];
            }
            if (!empty($row['web_push'])) {
                $webPushes[] = $row['web_push'];
            }
        }

        $gadgetLogo = $this->app->getSiteURL('/gadgets/' . $shouter . '/Resources/images/logo.png', false);
        $params['title'] = _t('SUBSCRIPTION_NOTIFICATION_TITLE');
        $params['summary'] = $params['summary'];
        $params['description'] = $params['description'];
        $params['url'] = isset($params['url']) ? $params['url'] : '';
        $params['icon'] = !empty($params['icon']) ? $params['icon'] : $gadgetLogo;
        $params['image'] = isset($params['image']) ? $params['image'] : '';
        $params['gadget'] = $shouter;
        $params['users'] = $users;
        $params['emails'] = $emails;
        $params['mobiles'] = $mobiles;
        $params['web_pushes'] = $webPushes;
        $this->gadget->event->shout('Notify', $params);
    }
}
