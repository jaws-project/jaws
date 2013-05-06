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
    function fetch($key_name, $component = '')
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;
        $params['key_name']  = $key_name;

        $sql = '
            SELECT
                [component], [key_name], [key_value]
            FROM [[acl]]
            WHERE
                [component] = {component}
              AND
                [key_name]  = {key_name}
              AND
                [user]      = {user}
              AND
                [group]     = {group}';

        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row) || empty($row) ||
            $row['component'] !== $component || $row['key_name'] !== $key_name)
        {
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
    function fetchAll($component = '')
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;

        $sql = '
            SELECT
                [component], [key_name], [key_value]
            FROM [[acl]]
            WHERE
                [component] = {component}
              AND
                [user]      = {user}
              AND
                [group]     = {group}';

        $keys = $GLOBALS['db']->queryAll($sql, $params);
        return $keys;
    }

    /**
     * Insert a new key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function insert($key_name, $key_value, $component = '')
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['component'] = $component;
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
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
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $key_value, $component = '')
    {
        $params = array();
        $params['user']      = 0;
        $params['group']     = 0;
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
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
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $component = '')
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
            $params["value_$ndx"] = $key_value;

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
     * Deletes a key
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
     * Get the real/full permission of a gadget (and group if it has) for a
     * certain task
     *
     * @access  public
     * @param   string   $user   Username
     * @param   int      $groups array of group's ID or empty string
     * @param   string   $gadget Gadget to use
     * @param   string   $task   Task to use
     * @param   bool     $is_super_admin
     * @return  bool     Permission value: Granted (true) or Denied (false)
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
                return false;
            }
        }

        if (defined('JAWS_GODUSER_ACLS') && defined('JAWS_GODUSER') && JAWS_GODUSER !== $user) {
            static $goduser_acls;
            if (!isset($goduser_acls)) {
                $goduser_acls = array_filter(array_map('trim', explode(',', strtolower(JAWS_GODUSER_ACLS))));
            }
            if (in_array(strtolower("$gadget:$task"), $goduser_acls)) {
                return false;
            }
        }

        if ($is_super_admin === true) {
            return true;
        }

        $this->LoadKeysOf($user, 'users');

        // 1. Check for user permission
        $perm['user'] = $this->Get('/ACL/users/'.$user.'/gadgets/'.$gadget.'/'.$task);
        if (!is_null($perm['user'])) {
            return (bool)$perm['user'];
        }

        // 2. Check for groups permission
        $perm['groups'] = null;
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $gPerm = $this->GetGroupPermission($group, $gadget, $task);
                if (!is_null($gPerm)) {
                    $perm['groups'] = is_null($perm['groups'])? $gPerm : ($perm['groups'] || $gPerm);
                }
            }
        }

        if (!is_null($perm['groups'])) {
            return (bool)$perm['groups'];
        }

        // 3. Check for default
        // If there is no key then it must return false
        $perm['default'] = false;
        if (!is_null($this->Get('/ACL/gadgets/'.$gadget.'/'.$task))) {
            $perm['default'] = $this->Get('/ACL/gadgets/'.$gadget.'/'.$task);
        }

        return (bool)$perm['default'];
    }

    /**
     * Get a permission to a given Gadget -> Task/Method of a group
     *
     * @access  public
     * @param   string  $group          Group's ID
     * @param   string  $gadget         Gadget name
     * @param   string  $task           Task or method name
     * @return  bool    True if permission is granted
     */
    function GetGroupPermission($group, $gadget, $task)
    {
        $this->LoadKeysOf($group, 'groups');

        return $this->Get('/ACL/groups/'.$group.'/gadgets/'.$gadget.'/'.$task);
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
        $this->LoadKeysOf($user, 'users');
        $result = $this->GetAsQuery();
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
        $this->LoadKeysOf($id, 'groups');
        $result = $this->GetAsQuery();
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
     * Get all group ACL permissions of an user
     *
     * @access  public
     * @param   string $username  Username
     * @return  array  Struct that contains all needed info about the ACL for a given user.
     */
    function GetGroupAclPermissionsOfUsername($username)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $groups = $userModel->GetGroupsOfUser($username);
        if (Jaws_Error::IsError($groups)) {
            return false;
        }

        $aclGroups = array();
        foreach ($groups as $group) {
            $acls = $this->GetGroupAclPermissions($group);
            if (!Jaws_Error::IsError($acls)) {
                $aclGroups = $acls;
            }
        }

        return $aclGroups;
    }

    /**
     * Delete all user ACLs
     *
     * @access  public
     * @param   string  $user  Username
     */
    function DeleteUserACL($user)
    {
        $params         = array();
        $params['name'] = '/ACL/users/'.$user.'/%';

        $sql = 'DELETE FROM [[acl]] WHERE [key_name] LIKE {name}';
        $GLOBALS['db']->query($sql, $params);

        return true;
    }

    /**
     * Delete all group ACLs
     *
     * @access  public
     * @param   string  $group  Group's ID
     */
    function DeleteGroupACL($group)
    {
        $params         = array();
        $params['name'] = '/ACL/groups/'.$group.'/%';

        $sql = 'DELETE FROM [[acl]] WHERE [key_name] LIKE {name}';
        $GLOBALS['db']->query($sql, $params);

        return true;
    }

    /**
     * Get the simple array
     *
     * @access  public
     * @return  array   Returns the SimpleArray
     */
    function GetSimpleArray()
    {
        return $this->_Registry;
    }

    /**
     * Loads the keys of a component and optionally it returns the keys found in the file
     *
     * @access  public
     * @param   string  $component Component's name
     */
    function LoadFile($component, $type = 'gadgets')
    {
        return true;
    }

    /**
     * Loads all the component files
     *
     * @access  public
     */
    function LoadAllFiles()
    {
        $gadgets = array_filter(explode(',', $GLOBALS['app']->Registry->fetch('gadgets_installed_items')));
        foreach ($gadgets as $gadget) {
            $this->LoadFile($gadget);
        }
    }

    /**
     * Loads all ACL keys of an user
     *
     * @access  public
     * @param   string   $target  Target to search (can be a username or a GID)
     * @param   string   $where   Where to search? users or groups?
     */
    function LoadKeysOf($target, $where)
    {
        if ($target === '' || in_array($target, $this->_LoadedTargets[$where])) {
            return;
        }
        $sql = "SELECT [key_name], [key_value] FROM [[acl]] WHERE [key_name] LIKE '/ACL/".$where."/".$target."/%'";
        $result = $GLOBALS['db']->queryAll($sql, array(), null, null, true);
        if (Jaws_Error::isError($result)) {
            return false;
        }
        $this->_LoadedTargets[$where][$target] = $target;
    }

}