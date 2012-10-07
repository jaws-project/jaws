<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Admin_UsersGroup extends Jaws_Model
{
    /**
     * Add a group of user (by they ids) to a certain group
     *
     * @access  public
     * @param   int     $guid  Group's ID
     * @param   array   $users Array with user id
     * @return  array   Response (notice or error)
     */
    function AddUsersToGroup($guid, $users)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();

        $group = $userModel->GetGroup((int)$guid);
        if (!$group) {
            return new Jaws_Error(_t('USERS_GROUPS_GROUP_NOT_EXIST'), _t('USERS_NAME'));
        }

        $postedUsers = array();
        foreach ($users as $k => $v) {
            $postedUsers[$v] = $v;
        }

        $list = $userModel->GetUsers();
        foreach ($list as $user) {
            if ($userModel->UserIsInGroup($user['id'], $guid)) {
                if (!isset($postedUsers[$user['id']])) {
                    if (!$GLOBALS['app']->Session->IsSuperAdmin() && $user['superadmin']) {
                        continue;
                    }
                    $userModel->DeleteUserFromGroup($user['id'], $guid);
                }
            } else {
                if (isset($postedUsers[$user['id']])) {
                    $userModel->AddUserToGroup($user['id'], $guid);

                }
            }
        }
        return true;
    }

}