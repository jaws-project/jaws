<?php
/**
 * Notification Notify event
 *
 * @category    Gadget
 * @package     Notification
 */
class Notification_Events_Notify extends Jaws_Gadget_Event
{
    /**
     * Grabs notification and sends it out via available drivers
     *
     * @access  public
     * @param   string  $shouter    The shouting gadget
     * @param   array   $params     [user, group, title, summary, description, priority, send]
     * @return  bool
     */
    function Execute($shouter, $params)
    {
        if (isset($params['send']) && $params['send'] === false) {
            return false;
        }

        $model = $this->gadget->model->load('Notification');
        $gadget = empty($params['gadget']) ? $shouter : $params['gadget'];

        // detect if publish_time = 0 then must delete the notifications
        if ($params['publish_time'] < 0) {
            return $model->DeleteNotificationsByKey($params['key']);
        }
        $publishTime = empty($params['publish_time']) ? time() : $params['publish_time'];

        $users = array();
        $jUser = new Jaws_User;
        if (isset($params['group']) && !empty($params['group'])) {
            $group_users = $jUser->GetGroupUsers($params['group'], true, false, true);
            if (!Jaws_Error::IsError($group_users) && !empty($group_users)) {
                $users = $group_users;
            }
        }

        if (isset($params['emails']) && !empty($params['emails'])) {
            foreach ($params['emails'] as $email) {
                if (!empty($email)) {
                    $users[] = array('email' => $email);
                }
            }
        }

        if (isset($params['mobiles']) && !empty($params['mobiles'])) {
            foreach ($params['mobiles'] as $mobile) {
                if (!empty($mobile)) {
                    $users[] = array('mobile_number' => $mobile);
                }
            }
        }

        if (isset($params['user']) && !empty($params['user'])) {
            $user = $jUser->GetUser($params['user'], true, false, true);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $users[] = $user;
            }
        }

        // FIXME: increase performance for getting users data
        if (isset($params['users']) && !empty($params['users'])) {
            foreach ($params['users'] as $userId) {
                if (!empty($userId)) {
                    $user = $jUser->GetUser($userId, true, false, true);
                    if (!Jaws_Error::IsError($user) && !empty($user)) {
                        $users[] = $user;
                    }
                }
            }
        }

        if (empty($users)) {
            return false;
        }

        // get gadget driver settings
        $configuration = unserialize($this->gadget->registry->fetch('configuration'));

        $notificationsEmails = array();
        $notificationsMobiles = array();

        // notification for this gadget was disabled
        if ($configuration[$gadget] == 0) {
            return false;
        }

        foreach ($users as $user) {
            // generate email array
            if ($configuration[$gadget] == 1 ||
                $configuration[$gadget] == 'Mail') {
                if (!empty($user['email'])) {
                    $notificationsEmails[] = array(
                        'key' => $params['key'],
                        'contact_value' => $user['email'],
                        'title' => strip_tags($params['title']),
                        'summary' => strip_tags($params['summary']),
                        'description' => $params['description'],
                        'publish_time' => $publishTime
                    );
                }
            }

            // generate mobile array
            if ($configuration[$gadget] == 1 ||
                $configuration[$gadget] == 'Mobile') {
                if (!empty($user['mobile_number'])) {
                    $notificationsMobiles[] = array(
                        'key' => $params['key'],
                        'contact_value' => $user['mobile_number'],
                        'title' => strip_tags($params['title']),
                        'summary' => strip_tags($params['summary']),
                        'description' => $params['description'],
                        'publish_time' => $publishTime
                    );
                }
            }
        }

        if (!empty($notificationsEmails)) {
            $res = $model->InsertNotifications(Notification_Info::NOTIFICATION_TYPE_EMAIL, $notificationsEmails);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        if (!empty($notificationsMobiles)) {
            return $model->InsertNotifications(Notification_Info::NOTIFICATION_TYPE_SMS, $notificationsMobiles);
        }

        return false;
    }
}
