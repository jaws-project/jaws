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

        // generate notification array
        $notifications = array();
        foreach($users as $user) {
            $notifications['email'] = $user['email'];
            $notifications['mobile_number'] = $user['mobile_number'];
            $notifications['url'] = $params['url'];
            $notifications['unsubscribe_url'] = $params['unsubscribe_url'];
            $notifications['title'] = strip_tags($params['title']);
            $notifications['summary'] = strip_tags($params['summary']);
            $notifications['description'] = $params['description'];
        }

        $model = $this->gadget->model->load('Notification');
        return $model->InsertNotifications($notifications);
        
        
/*
          if (!isset($params['summary'])) {
            $params['summary'] = '';
        }

        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $options = unserialize($this->gadget->registry->fetch($driver . '_options'));
            $driverObj = Jaws_Notification::getInstance($driver, $options);
            $driverObj->notify(
                $users,
                strip_tags($params['title']),
                strip_tags($params['summary']),
                $params['description']
            );
        }*/
    }
}
