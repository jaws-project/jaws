<?php
/**
 * Notification Notify event
 *
 * @category    Gadget
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Notification_Events_Notify extends Jaws_Gadget_Event
{
    /**
     * Grabs notification and sends it out via available drivers
     *
     * @access  public
     * @params  string  $shouter    The shouting gadget
     * @params  array   $params     [user, group, title, summary, description, priority, send]
     */
    function Execute($shouter, $params)
    {
        if (isset($params['send']) && $params['send'] === false) {
            return;
        }

        $users = array();
        $jUser = new Jaws_User;
        if (isset($params['group']) && !empty($params['group'])) {
            $group_users = $jUser->GetGroupUsers($params['group'], true, false, true);
            if (!Jaws_Error::IsError($group_users) && !empty($group_users)) {
                $users = $group_users;
            }
        }
        if (isset($params['user']) && !empty($params['user'])) {
            $user = $jUser->GetUser($params['user'], true, false, true);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $users[] = $user;
            }
        }
        if (empty($users)) {
            return;
        }

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
        }
    }
}
