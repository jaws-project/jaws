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
    function get($group, $owner = 0, $fieldsets = array())
    {
        $columns = array(
            'default'  => array(
                /*'groups.domain:integer', */
                'groups.id:integer', 'groups.owner:integer', 'groups.name', 'groups.title',
                'groups.description', 'groups.department:boolean', 'groups.email', 'groups.mobile',
                'groups.removable:boolean', 'groups.enabled:boolean'
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
     * Get list of groups
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $owner      The owner of group
     * @param   int     $user       User ID   // 0: all users
     * @param   array   $filters    Groups filters // enabled, term
     *                  ex: array(
     *                      'enabled'  => true,
     *                      'term' => 'operators'
     *                  )
     * @param   array   $fieldsets  Users fields sets // default
     *                  ex: array('enabled'  => true)
     * @param   array   $orderBy    Field to order by
     *                  ex: array(
     *                      'id'   => true  // ascending,
     *                      'name' => false // descending
     *                  )
     * @param   int     $limit
     * @param   int     $offset
     * @return  array|Jaws_Error    Returns an array of the available groups or Jaws_Error on error
     */
    function list(
        $domain = 0, $owner = 0, $user = 0,
        $filters = array(), $fieldsets = array(),
        $orderBy = array(), $limit = 0, $offset = null
    ) {
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
        // user
        if (!empty($user)) {
            $objORM->join('users_groups', 'users_groups.group', 'groups.id');
            $objORM->and()->where('users_groups.user', (int)$user);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'enabled'    => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // enabled
        $objORM->and()->where('enabled', (bool)$filters['enabled'], '=', is_null($filters['enabled']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(name)', $term, 'like')
                ->or()
                ->closeWhere('lower(title)', $term, 'like');
        }

        // Order by
        $orders = array();
        if (empty($orderBy)) {
            $orderBy = array('id' => true);
        }
        foreach ($orderBy as $field => $ascending) {
            $orders[] = 'groups.'. $field . ' '. ($ascending? 'asc' : 'desc');
        }
        call_user_func_array(array($objORM, 'orderBy'), $orders);

        return $objORM->limit($limit, $offset)->fetchAll();
    }

    /**
     * Get count of groups
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $owner      The owner of group
     * @param   int     $user       User ID   // 0: all users
     * @param   array   $filters    Groups filters // enabled, term
     *                  ex: array(
     *                      'enabled'  => true,
     *                      'term' => 'operators'
     *                  )
     * @return  array|Jaws_Error    Returns an count of the available groups or Jaws_Error on error
     */
    function listCount($domain = 0, $owner = 0, $user = 0, $filters = array())
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('groups')
            ->select('count(groups.id):integer')
            //->where('domain', (int)$domain, '=', empty($domain))
            //->and()
            ->where('owner', (int)$owner);
        // user
        if (!empty($user)) {
            $objORM->join('users_groups', 'users_groups.group', 'groups.id');
            $objORM->and()->where('users_groups.user', (int)$user);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'enabled'    => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // enabled
        $objORM->and()->where('enabled', (bool)$filters['enabled'], '=', is_null($filters['enabled']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(name)', $term, 'like')
                ->or()
                ->closeWhere('lower(title)', $term, 'like');
        }

        return $objORM->fetchOne();
    }

    /**
     * Adds a new group
     *
     * @access  public
     * @param   array   $gData  Group information data
     * @param   int     $owner  The owner of group
     * @return  bool    Returns true if group  was successfully added, false if not
     */
    function add($gData, $owner = 0)
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
        $this->gadget->acl->insert('GroupManage', $result, false);

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
    function update($id, $gData, $owner = 0)
    {
        // unset invalid keys
        $invalids = array_diff(
            array_keys($gData),
            array('name', 'title', 'description', 'email', 'mobile', 'department', 'removable', 'enabled')
        );
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
    function delete($id, $owner = 0)
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

        $this->gadget->acl->delete('GroupManage', $id);
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