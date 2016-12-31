<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Admin_UsersGroup extends Jaws_Gadget_Model
{
    /**
     * Adds a group of users(by their IDs) to a certain group
     *
     * @access  public
     * @param   int     $guid  Group's ID
     * @param   array   $users Array with user id
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function AddUsersToGroup($guid, $users)
    {
        $userModel = new Jaws_User();
        $group = $userModel->GetGroup((int)$guid);
        if (!$group) {
            return new Jaws_Error(_t('USERS_GROUPS_GROUP_NOT_EXIST'));
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