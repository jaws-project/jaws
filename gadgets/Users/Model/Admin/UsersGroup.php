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
        $group = $this->gadget->model->load('Group')->get((int)$guid);
        if (!$group) {
            return new Jaws_Error($this::t('GROUPS_GROUP_NOT_EXIST'));
        }

        $postedUsers = array();
        foreach ($users as $k => $v) {
            $postedUsers[$v] = $v;
        }

        // FIXME: only fetch users of given group's domain
        $list = $this->gadget->model->load('User')->list();
        foreach ($list as $user) {
            if ($this->gadget->model->load('UserGroup')->exists($user['id'], $guid)) {
                if (!isset($postedUsers[$user['id']])) {
                    if (!$this->app->session->user->superadmin && $user['superadmin']) {
                        continue;
                    }
                    $this->gadget->model->load('UserGroup')->delete($user['id'], $guid);
                }
            } else {
                if (isset($postedUsers[$user['id']])) {
                    $this->gadget->model->load('UserGroup')->add($user['id'], $guid);

                }
            }
        }
        return true;
    }

}