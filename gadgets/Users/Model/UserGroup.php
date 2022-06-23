<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_UserGroup extends Jaws_Gadget_Model
{
    /**
     * Adds an user to a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   string  $alias  User's alias name in the given group
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was successfully added to the group, false if not
     */
    function add($user, $group, $alias = '', $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();
        $group = $objORM->table('groups')
            ->select('id:integer', 'name')
            ->where('owner', (int)$owner)
            ->and()
            ->where('id', $group)
            ->fetchRow();
        if (Jaws_Error::IsError($group) || empty($group)) {
            return $group;
        }

        $result = $objORM->table('users_groups')
            ->insert(
                array(
                    'user' => $user, 'group' => $group['id'], 'alias' => $alias
                )
            )->exec();
        if (!Jaws_Error::IsError($result)) {
            if (isset($this->app) && property_exists($this->app, 'session') &&
                $this->app->session->user->id == $user
            ) {
                // update logged user session
                $user_groups = $this->app->session->user->groups;
                $user_groups[$group['id']] = $group['name'];
                $this->app->session->user = array('groups' => $user_groups);
            }

            // Let everyone know user added to a group
            $res = $this->app->listener->Shout(
                'Users',
                'UserGroupsChanges',
                array(
                    'action' => 'AddUserToGroup',
                    'user' => $user,
                    'group' => $group['id'],
                    'alias' => $alias
                )
            );
            if (Jaws_Error::IsError($res)) {
                // nothing
            }
        }

        return $result;
    }

    /**
     * Adds an user to a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   string  $alias  User's alias name in the given group
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was successfully added to the group, false if not
     */
    function update($user, $group, $alias = '', $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();
        $group = $objORM->table('groups')
            ->select('id:integer', 'name')
            ->where('owner', (int)$owner)
            ->and()
            ->where('id', $group)
            ->fetchRow();
        if (Jaws_Error::IsError($group) || empty($group)) {
            return $group;
        }

        $result = $objORM->table('users_groups')
            ->insert(
                array(
                    'user' => $user, 'group' => $group['id'], 'alias' => $alias
                )
            )->exec();
    }

    /**
     * Deletes an user from a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   string  $alias  User's alias name in the given group
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was sucessfully deleted from a group, false if not
     */
    function delete($user, $group, $alias = '', $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();
        $result = $objORM->table('groups')
            ->select('id')
            ->where('owner', (int)$owner)
            ->and()
            ->where('id', $group)
            ->fetchOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return $result;
        }

        $result = $objORM->table('users_groups')
            ->delete()
            ->where('user', $user)
            ->and()
            ->where('group', $group)
            ->and()
            ->where('alias', $alias)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if ($this->app->session->user->id == $user) {
            // update logged user session
            $user_groups = $this->app->session->user->groups;
            unset($user_groups[$group]);
            $this->app->session->user = array('groups' => $user_groups);
        }

        // Let everyone know user added to a group
        $res = $this->app->listener->Shout(
            'Users',
            'UserGroupsChanges',
            array(
                'action' => 'DeleteUserFromGroup',
                'user'  => $user,
                'group' => $group['id'],
                'alias' => $alias
            )
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

    /**
     * Checks if a user is in a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @return  bool    Returns true if user in in the group or false if not
     */
    function exists($user, $group)
    {
        $usrgrpTable = Jaws_ORM::getInstance()->table('users_groups');
        $usrgrpTable->select('count(user):integer');
        $usrgrpTable->where('user', $user)->and()->where('group', $group);
        $howmany = $usrgrpTable->fetchOne();
        if (Jaws_Error::IsError($howmany)) {
            return false;
        }

        return (bool)$howmany;
    }

    /**
     * get current user's  granted groups
     *
     * @access  public
     * @param   string  $term  limit group result by custom term
     * @return  mixed   Array of groups on success or Jaws_Error on failure
     */
    function getGrantedGroups($term = '')
    {
        $groupsAccess = array();
        $groups = $this->gadget->model->load('Group')->list(0, 0, $this->app->session->user->id);
        foreach ((array)$groups as $group) {
            if ($this->gadget->GetPermission('GroupManage', $group['id'])) {
                if (empty($term) || stripos($group['title'], $term) !== false ||
                    stripos($group['name'], $term) !== false) {
                    $groupsAccess[] = $group;
                }
            }
        }
        return $groupsAccess;
    }

}