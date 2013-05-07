<?php
/**
 * Manage Access control lists
 *
 * @category    ACL
 * @package     Core
 * @author      Ivan Chavero <imcsk8@gluch.org.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ACL
{
    /**
     * Loaded users/groups so we don't query the DB each
     * time we need a value of them
     *
     * @access  private
     * @var     array
     */
    var $_LoadedTargets;

    /**
     * Constructor
     *
     * @access  public
     */
    function Jaws_ACL()
    {
        $this->_LoadedTargets = array(
            'users'  => array(),
            'groups' => array()
        );
    }

    /**
     * Fetch the key value
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $component  Component name
     * @return  string  The value of the key
     */
    function fetch($key_name, $component)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $tblACL->select('component', 'key_name', 'key_value');
        $tblACL->where('component', $component)->and()->where('key_name', $key_name)->and();
        $row = $tblACL->where('user', 0)->and()->where('group', 0)->getRow();
        if (Jaws_Error::IsError($row) || empty($row) || $row['component'] !== $component) {
            return null;
        }

        return $row['key_value'];
    }

    /**
     * Fetch all acl keys of the gadget
     *
     * @access  public
     * @param   string  $component   Component name
     * @return  mixed   Array of keys if successful or Jaws_Error on failure
     */
    function fetchAll($component)
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;

        $sql = '
            SELECT
                [key_name], [key_value]
            FROM [[acl]]
            WHERE
                [component] = {component}
              AND
                [user]      = {user}
              AND
                [group]     = {group}';

        return $GLOBALS['db']->queryAll($sql, $params);
    }

    /**
     * Fetch the ACL key value by user
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $key_name   Key name
     * @param   string  $component  Component name
     * @return  mixed   Value of the key if success otherwise Null
     */
    function fetchByUser($user, $key_name, $component)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $value  = $tblACL->select('key_value')
            ->where('component', $component)
            ->and()
            ->where('key_name', $key_name)
            ->and()
            ->where('user', (int)$user)
            ->getOne();
        if (Jaws_Error::IsError($value) || empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Fetch all ACL keys/values releated to the user
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  mixed   Array of ACLs if success otherwise Null
     */
    function fetchAllByUser($user)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->select('component', 'key_name', 'key_value')
            ->where('user', (int)$user)
            ->getAll();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Fetch the ACL key value by groups
     *
     * @access  public
     * @param   array   $groups     Array of groups IDs
     * @param   string  $key_name   Key name
     * @param   string  $component  Component name
     * @return  mixed   Array of values if success otherwise Null
     */
    function fetchByGroups($groups, $key_name, $component)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $values = $tblACL->select('key_value')
            ->where('component', $component)
            ->and()
            ->where('key_name', $key_name)
            ->and()
            ->where('group', $group, 'in')
            ->getCol();
        if (Jaws_Error::IsError($values) || empty($values)) {
            return null;
        }

        return $values;
    }

    /**
     * Fetch all ACL keys/values releated to the group
     *
     * @access  public
     * @param   int     $group  Group ID
     * @return  mixed   Array of ACLs if success otherwise Null
     */
    function fetchAllByGroup($group)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->select('component', 'key_name', 'key_value')
            ->where('group', (int)$group)
            ->getAll();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Insert a new key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   int     $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function insert($key_name, $key_value, $component)
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;
        $params['key_name']  = $key_name;
        $params['key_value'] = (int)$key_value;
        $params['now']       = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[acl]]
                ([component], [key_name], [key_value], [user], [group], [updatetime])
            VALUES
                ({component}, {key_name}, {key_value}, {user}, {group}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $component)
    {
        if (empty($keys)) {
            return true;
        }

        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;
        $params['now']       = $GLOBALS['db']->Date();

        $sqls = '';
        $dbDriver  = $GLOBALS['db']->getDriver();
        $dbVersion = $GLOBALS['db']->getDBVersion();
        for ($ndx = 0; $ndx < count($keys); $ndx++) {
            list($key_name, $key_value) = each($keys);
            $params["name_$ndx"]  = $key_name;
            $params["value_$ndx"] = (int)$key_value;

            // Ugly hack to support all databases
            switch ($dbDriver) {
                case 'oci8':
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {component}, {name_$ndx}, {value_$ndx}, {user}, {group}, {now} FROM DUAL";
                    break;
                case 'ibase':
                    $sqls[] = " VALUES ({component}, {name_$ndx}, {value_$ndx}, {user}, {group}, {now})";
                    break;
                case 'pgsql':
                    if (version_compare($dbVersion, '8.2.0', '>=')) {
                        $sqls .= (empty($sqls)? "\n VALUES" : ",").
                                 "\n ({component}, {name_$ndx}, {value_$ndx}, {user}, {group}, {now})";
                    } else {
                        $sqls[] = " VALUES ({component}, {name_$ndx}, {value_$ndx}, {user}, {group}, {now})";
                    }
                    break;
                default:
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {component}, {name_$ndx}, {value_$ndx}, {user}, {group}, {now}";
                    break;
            }
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = '
                    INSERT INTO [[acl]]
                        ([component], [key_name], [key_value], [user], [group], [updatetime])
                    '. $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = '
                INSERT INTO [[acl]]
                    ([component], [key_name], [key_value], [user], [group], [updatetime])
                '. $sqls;
            $result = $GLOBALS['db']->query($qsql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   int     $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $key_value, $component)
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['key_name']  = $key_name;
        $params['key_value'] = (int)$key_value;
        $params['component'] = $component;

        $sql = '
            UPDATE [[acl]] SET
                [key_value] = {key_value}
            WHERE
                [component] = {component}
              AND
                [key_name]  = {key_name}
              AND
                [user]      = {user}
              AND
                [group]     = {group}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Get the real/full permission of a gadget (and group if it has) for a certain task
     *
     * @access  public
     * @param   int     $user           User ID
     * @param   int     $groups         Array of group's ID or empty string
     * @param   string  $gadget         Gadget to use
     * @param   string  $task           Task to use
     * @param   bool    $is_super_admin
     * @return  integer Permission value: Granted (1) or Denied (0)
     */
    function GetFullPermission($user, $groups, $gadget, $task, $is_super_admin = false)
    {
        // is in forbidden acls?
        if (defined('JAWS_FORBIDDEN_ACLS')) {
            static $forbidden_acls;
            if (!isset($forbidden_acls)) {
                $forbidden_acls = array_filter(array_map('trim', explode(',', strtolower(JAWS_FORBIDDEN_ACLS))));
            }

            if (in_array(strtolower("$gadget:$task"), $forbidden_acls)) {
                return 0;
            }
        }

        if (defined('JAWS_GODUSER_ACLS') && defined('JAWS_GODUSER') && JAWS_GODUSER !== $user) {
            static $goduser_acls;
            if (!isset($goduser_acls)) {
                $goduser_acls = array_filter(array_map('trim', explode(',', strtolower(JAWS_GODUSER_ACLS))));
            }
            if (in_array(strtolower("$gadget:$task"), $goduser_acls)) {
                return 0;
            }
        }

        if ($is_super_admin === true) {
            return 0xff;
        }

        // 1. Check for user permission
        $perm['user'] = $this->fetchByUser($user, $task, $gadget);
        if (!is_null($perm['user'])) {
            return $perm['user'];
        }

        // 2. Check for groups permission
        $perm['groups'] = null;
        if (!empty($groups)) {
            $perm['groups'] = @max($this->fetchByGroups($groups, $task, $gadget));
        }

        if (!is_null($perm['groups'])) {
            return $perm['groups'];
        }

        // 3. Check for default
        $perm['default'] = $this->fetch($task, $gadget);
        return $perm['default'];
    }

    /**
     * Get ACL permissions for a given user(name)
     *
     * @access  public
     * @param   string  $user Username
     * @return  array   Struct that contains all needed info about the ACL of a given user.
     */
    function GetAclPermissions($user)
    {
        $perms = array();
        foreach ($result as $r) {
            if (preg_match('#/ACL/gadgets/(.*?)/(.*?)#si', $r['name'])) {
                $item = array();
                $item['name'] = str_replace('/ACL/gadgets/', '/ACL/users/'.$user.'/gadgets/', $r['name']);

                $gadget = preg_replace("@\/ACL/gadgets\/(\w+)\/(\w+)@", "\$1", $r['name']);
                $task = str_replace('/ACL/users/'.$user.'/gadgets/'.$gadget.'/', '', $item['name']);

                $item['value'] = $this->Get($item['name']);
                $item['default'] = false;
                $perms[$gadget][] = $item;
            }
        }
        return $perms;

    }

    /**
     * Get ACL permissions for a given group
     *
     * @access  public
     * @param   string  $id               Group's ID
     * @return  array Struct that contains all needed info about the ACL for a given user.
     */
    function GetGroupAclPermissions($id)
    {
        $perms = array();
        foreach ($result as $r) {
            if (preg_match('#/ACL/gadgets/(.*?)/(.*?)#si', $r['name'])) {
                $item = array();
                $item['name'] = str_replace('/ACL/gadgets/', '/ACL/groups/'.$id.'/gadgets/', $r['name']);
                $gadgetName = preg_replace("@\/ACL/gadgets\/(\w+)\/(\w+)@", "\$1", $r['name']);
                $task = str_replace('/ACL/groups/'.$id.'/gadgets/'.$gadgetName.'/', '', $item['name']);

                $item['value'] = $this->Get($item['name']);
                $item['default'] = false;
                $perms[$gadgetName][] = $item;
            }
        }
        return $perms;
    }

    /**
     * Deletes a key or all ACLS related to the component
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   string  $key_name   Key name
     * @return  bool    True is set otherwise False
     */
    function delete($component, $key_name = '')
    {
        $params = array();
        $params['component'] = $component;
        $params['key_name']  = $key_name;

        $sql = '
            DELETE
                FROM [[acl]]
            WHERE
                [component] = {component}
            ';
        if (!empty($key_name)) {
            $sql.= ' AND [key_name] = {key_name}';
        }

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Delete all ACLs related to the user
     *
     * @access  public
     * @param   int     $user  User ID
     * @return  bool    True if success otherwise False
     */
    function deleteByUser($user)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->delete()->where('user', (int)$user)->exec();
        return !Jaws_Error::IsError($result);
    }

    /**
     * Delete all ACLs related to the group
     *
     * @access  public
     * @param   int     $group  Group ID
     * @return  bool    True if success otherwise False
     */
    function deleteByGroup($group)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->delete()->where('group', (int)$group)->exec();
        return !Jaws_Error::IsError($result);
    }

}