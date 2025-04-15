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
            $GLOBALS['log']->Log(JAWS_ERROR, 'Notification name required', 1);
            return false;
        }

        $notifModel = $this->gadget->model->load('Notification');
        $shouter = empty($params['gadget'])? $shouter : $params['gadget'];

        $params['time'] = !isset($params['time']) ? (time() + 1) : $params['time'];
        // if time < 0 then delete the notifications
        if ($params['time'] < 0) {
            return $notifModel->DeleteNotificationsByKey($params['name'], $params['key']);
        }

        // expire time
        if (!isset($params['longevity'])) {
            $params['longevity'] = (int)$this->gadget->registry->fetch('default_longevity');
        }
        $params['expiry'] = time() + $params['longevity'];

        $users = array();
        if (isset($params['group']) && !empty($params['group'])) {
            $group_users = Jaws_Gadget::getInstance('Users')->model->load('User')->list(
                array(
                    'group' => $params['group'],
                ),
                array(),
                array('account')
            );
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

        if (isset($params['devices']) && !empty($params['devices'])) {
            foreach ($params['devices'] as $device) {
                if (!empty($device)) {
                    $users[] = array('device' => $device);
                }
            }
        }

        if (isset($params['user']) && !empty($params['user'])) {
            $user = Jaws_Gadget::getInstance('Users')->model->load('User')->get(
                $params['user'],
                0,
                array('default' => true, 'account' => true)
            );
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $users[] = $user;
            }
        }

        // FIXME: increase performance for getting users data
        if (isset($params['users']) && !empty($params['users'])) {
            foreach ($params['users'] as $userId) {
                if (!empty($userId)) {
                    $user = Jaws_Gadget::getInstance('Users')->model->load('User')->get(
                        $userId,
                        0,
                        array('default' => true, 'account' => true)
                    );
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

        // notification for this shouter was disabled
        if (empty($configuration[$shouter])) {
            return false;
        }

        if (array_key_exists('driver', $params)) {
            $driverName = $params['driver'];
        } else {
            $driverName = $configuration[$shouter];
        }

        // get driver/drivers
        if ($driverName != 1) {
            $driver = $this->gadget->model->load('Drivers')->GetNotificationDriver($driverName);
            if (Jaws_Error::IsError($driver) || empty($driver) || !$driver['enabled']) {
                return false;
            }
            $activeDrivers = [$driver];
        } else {
            $activeDrivers = $this->gadget->model->load('Drivers')->GetNotificationDrivers(true);
            if (Jaws_Error::IsError($activeDrivers) || empty($activeDrivers)) {
                return false;
            }
        }

        $messageId = $notifModel->addMessage(
            $shouter,
            $params['name'],
            $params['key'],
            strip_tags($params['title']),
            $params['summary'],
            $params['verbose'],
            json_encode($params['variables']?? []),
            $params['time'],
            $params['expiry'],
            $params['callback']?? '',
            $params['image']?? ''
        );
        if (Jaws_Error::IsError($messageId)) {
            return $messageId;
        }

        foreach ($activeDrivers as $driver) {
            $objDriver = $this->gadget->model->load('Drivers')->LoadNotificationDriver($driver['name']);
            if (Jaws_Error::IsError($objDriver)) {
                return false;
            }
            $driverType = $objDriver->getType();

            $notifications = array();
            switch ($driverType) {
                case Jaws_Notification::EML_DRIVER:
                    // generate email array
                    $notifications = array_filter(array_column($users, 'email'));
                    break;

                case Jaws_Notification::SMS_DRIVER:
                    // generate mobile array
                    $notifications = array_filter(array_column($users, 'mobile'));
                    break;

                case Jaws_Notification::WEB_DRIVER:
                    // generate webpush array
                    $notifications = array_filter(array_column($users, 'webpush'));
                    break;

                case Jaws_Notification::APP_DRIVER:
                    // generate devices array
                    $notifications = array_filter(array_column($users, 'device'));
                    break;
            }

            if (!empty($notifications)) {
                $res = $notifModel->addNotifications(
                    $messageId,
                    $driver['id'],
                    $driverType,
                    $notifications
                );
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        return true;
    }

}