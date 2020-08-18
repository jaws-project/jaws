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
     * @param   array   $params     [user, group, title, summary, verbose, priority, send]
     * @return  bool
     */
    function Execute($shouter, $params)
    {
        if (isset($params['send']) && $params['send'] === false) {
            return false;
        }

        if (!isset($params['name'])) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'Notification name required', 1);
            return false;
        }

        $model = $this->gadget->model->load('Notification');
        $shouter = empty($params['gadget'])? $shouter : $params['gadget'];

        $params['time'] = !isset($params['time']) ? (time() + 1) : $params['time'];

        // if time = 0 then delete the notifications
        if ($params['time'] < 0) {
            return $model->DeleteNotificationsByKey($params['name'], $params['key']);
        }

        $users = array();
        if (isset($params['group']) && !empty($params['group'])) {
            $group_users = $this->app->users->GetGroupUsers($params['group'], true, false, true);
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
                    $users[] = array('mobile' => $mobile);
                }
            }
        }

        if (isset($params['user']) && !empty($params['user'])) {
            $user = $this->app->users->GetUser($params['user'], true, false, true);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $users[] = $user;
            }
        }

        // FIXME: increase performance for getting users data
        if (isset($params['users']) && !empty($params['users'])) {
            foreach ($params['users'] as $userId) {
                if (!empty($userId)) {
                    $user = $this->app->users->GetUser($userId, true, false, true);
                    if (!Jaws_Error::IsError($user) && !empty($user)) {
                        $users[] = $user;
                    }
                }
            }
        }

        // add webpush subscription to users array
        foreach ($users as $user) {
            if (array_key_exists('id', $user)) {
                $sessions = $this->app->session->getSessions($user['id']);
                if (!Jaws_Error::isError($sessions) && !empty($sessions)) {
                    foreach ($sessions as $session) {
                        if (!empty(@unserialize($session['webpush']))) {
                            $users[] = array('webpush' => $session['webpush']);
                        }
                    }
                }
            }
        }

        if (empty($users)) {
            return false;
        }

        // get gadget driver settings
        $configuration = unserialize($this->gadget->registry->fetch('configuration'));
        $notificationsEmails  = array();
        $notificationsMobiles = array();
        $notificationsWebPush = array();

        // notification for this shouter was disabled
        if (empty($configuration[$shouter])) {
            return false;
        }

        if (array_key_exists('driver', $params) || $configuration[$shouter] != 1) {
            if (array_key_exists('driver', $params)) {
                $driverType = $params['driver'];
            } else {
                $objDModel = $this->gadget->model->load('Drivers');
                $objDriver = $objDModel->LoadNotificationDriver($configuration[$shouter]);
                if (Jaws_Error::IsError($objDriver)) {
                    return false;
                }
                $driverType = $objDriver->getType();
            }

            switch ($driverType) {
                case Jaws_Notification::EML_DRIVER:
                    // generate email array
                    $notificationsEmails = array_filter(array_column($users, 'email'));
                    break;

                case Jaws_Notification::SMS_DRIVER:
                    // generate mobile array
                    $notificationsMobiles = array_filter(array_column($users, 'mobile'));
                    break;

                case Jaws_Notification::WEB_DRIVER:
                    // generate webpush array
                    $notificationsWebPush = array_filter(array_column($users, 'webpush'));
                    break;

                default:
                    return false;
            }
        } else {
            $notificationsEmails  = array_filter(array_column($users, 'email'));
            $notificationsMobiles = array_filter(array_column($users, 'mobile'));
            $notificationsWebPush = array_filter(array_column($users, 'webpush'));
        }

        if (!empty($notificationsEmails) || !empty($notificationsMobiles) || !empty($notificationsWebPush)) {
            // initiate variables if not exist
            if (!array_key_exists('variables', $params)) {
                $params['variables'] = array();
            }

            $res = $model->InsertNotifications(
                array(
                    'emails'  => $notificationsEmails,
                    'mobiles' => $notificationsMobiles,
                    'webpush' => $notificationsWebPush
                ),
                $shouter,
                $params['name'],
                $params['key'],
                strip_tags($params['title']),
                $params['summary'],
                $params['verbose'],
                json_encode($params['variables']),
                $params['time'],
                isset($params['callback'])? $params['callback'] : '',
                isset($params['image'])? $params['image'] : ''
            );
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
            return true;
        }

        return false;
    }
}
