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
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was successfully added to the group, false if not
     */
    function add($user, $group, $owner = 0)
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
            ->insert(array('user' => $user, 'group' => $group['id']))
            ->exec();
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
                array('action' => 'AddUserToGroup', 'user' => $user,'group' => $group['id'])
            );
            if (Jaws_Error::IsError($res)) {
                // nothing
            }
        }

        return $result;
    }

    /**
     * Deletes an user from a group
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   int     $group  Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if user was sucessfully deleted from a group, false if not
     */
    function delete($user, $group, $owner = 0)
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
            array('action' => 'DeleteUserFromGroup', 'user' => $user, 'group' => $group)
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

}