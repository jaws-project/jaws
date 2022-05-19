<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Group extends Jaws_Gadget_Model
{
    /**
     * Get group attributes
     *
     * @access  public
     * @param   mixed   $group      The group ID/Name
     * @param   int     $owner      The owner of group
     * @param   array   $fieldsets  Users fields sets // default
     * @return  mixed   Returns an array with the info of the group and Jaws_Error on error
     */
    function getGroup($group, $owner = 0, $fieldsets = array())
    {
        $columns = array(
            'default'  => array(
                /*'groups.domain:integer', */
                'groups.id:integer', 'groups.owner:integer', 'groups.name', 'groups.title',
                'enabled:boolean'
            ),
        );
        $fieldsets['default'] = true;
        $selectedColumns = array();
        foreach ($fieldsets as $key => $keyValue) {
            if ($keyValue) {
                $selectedColumns = array_merge($selectedColumns, $columns[$key]);
            }
        }

        $objORM = Jaws_ORM::getInstance()
            ->table('groups')
            ->select($selectedColumns)
            //->where('domain', (int)$domain, '=', empty($domain))
            //->and()
            ->where('owner', (int)$owner);
        if (is_int($group)) {
            $objORM->and()->where('id', $group);
        } else {
            $objORM->and()->where('name', Jaws_UTF8::strtolower($group));
        }

        return $objORM->fetchRow();
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   array   $gData  Group information data
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group  was successfully added, false if not
     */
    function addGroup($gData, $owner = 0)
    {
        // name
        $gData['name'] = trim($gData['name'], '-_.@');
        if (!preg_match('/^[[:alnum:]\-_.@]{3,32}$/', $gData['name'])) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INVALID_GROUPNAME'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }
        $gData['name']  = strtolower($gData['name']);
        $gData['owner'] = (int)$owner;

        // title
        $gData['title'] = Jaws_UTF8::trim($gData['title']);
        if (empty($gData['title'])) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $gData['removable'] = isset($gData['removable'])? (bool)$gData['removable'] : true;
        $gData['enabled'] = isset($gData['enabled'])? (bool)$gData['enabled'] : true;
        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $result = $groupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_GROUPS_ALREADY_EXISTS', $gData['name']));
            }
            return $result;
        }

        // Let everyone know a group has been added
        $res = $this->app->listener->Shout(
            'Users',
            'GroupChanges',
            array('action' => 'AddGroup', 'group' => $result)
        );
        if (Jaws_Error::IsError($res)) {
            //do nothing
        }

        return $result;
    }

    /**
     * Update the info of a group
     *
     * @access  public
     * @param   int     $id     Group ID
     * @param   array   $gData  Group information data
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group was sucessfully updated, false if not
     */
    function editGroup($id, $gData, $owner = 0)
    {
        // unset invalid keys
        $invalids = array_diff(array_keys($gData), array('name', 'title', 'description', 'enabled'));
        foreach ($invalids as $invalid) {
            unset($gData[$invalid]);
        }

        // name
        if (isset($gData['name'])) {
            $gData['name'] = trim($gData['name'], '-_.@');
            if (!preg_match('/^[[:alnum:]\-_.@]{3,32}$/', $gData['name'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_GROUPNAME'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $gData['name']  = strtolower($gData['name']);
        }
        $gData['owner'] = (int)$owner;

        // title
        if (isset($gData['title'])) {
            $gData['title'] = Jaws_UTF8::trim($gData['title']);
            if (empty($gData['title'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INCOMPLETE_FIELDS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        if (isset($gData['enabled'])) {
            $gData['enabled'] = (bool)$gData['enabled'];
        }

        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $result = $groupsTable->update($gData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            if (MDB2_ERROR_CONSTRAINT == $result->getCode()) {
                $result->SetMessage(_t('USERS_GROUPS_ALREADY_EXISTS', $gData['name']));
            }
            return $result;
        }

        // Let everyone know a group has been added
        $res = $this->app->listener->Shout(
            'Users',
            'GroupChanges',
            array('action' => 'UpdateGroup', 'group' => $id)
        );
        if (Jaws_Error::IsError($res)) {
            //do nothing
        }

        return true;
    }

    /**
     * Deletes a group
     *
     * @access  public
     * @param   int     $id     Group's ID
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group was successfully deleted, false if not
     */
    function deleteGroup($id, $owner = 0)
    {
        $objORM = Jaws_ORM::getInstance();

        //Start Transaction
        $objORM->beginTransaction();

        $objORM->delete()->table('groups');
        $result = $objORM->where('id', $id)
            ->and()
            ->where('removable', true)
            ->and()
            ->where('owner', (int)$owner)
            ->exec();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        $result = $objORM->delete()->table('users_groups')->where('group', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->app->acl->deleteByGroup($id);

        //Commit Transaction
        $objORM->commit();

        // Let everyone know a group has been deleted
        $res = $this->app->listener->Shout(
            'Users',
            'GroupChanges',
            array('action' => 'DeleteGroup', 'group' => $id)
        );
        if (Jaws_Error::IsError($res)) {
            // nothing
        }

        return true;
    }

}