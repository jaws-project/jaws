<?php
/**
 * Manage Access control lists
 *
 * @category   ACL
 * @package    Core
 * @author     Ivan Chavero <imcsk8@gluch.org.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
     * Has the registry
     *
     * @var     array
     * @access  private
     * @see    GetSimpleArray()
     */
    var $_Registry = array();

    /**
     * Array that has a *registry* of files that have been called
     *
     * @var     array
     * @access  private
     */
    var $_LoadedFiles = array();

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
     * Checks if the key exists
     *
     * @access  public
     * @param   string  $name  The key
     * @return  bool    true when the key was found, else false
     */
    function KeyExists($name)
    {
        if (array_key_exists($name, $this->_Registry)) {
            return true;
        }

        return false;
    }

    /**
     * Looks for a key in the acl registry
     *
     * @access      private
     * @param   string  $name   Key to find
     * @return  bool     The value of the key, if not key found must return null
     */
    function Get($name)
    {
        $value = $this->KeyExists($name) ? $this->_Registry[$name] : null;
        if ($value == 'true') {
            return true;
        }

        if ($value === null) {
            return null;
        }

        return false;
    }

    /**
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $name  The key
     * @param   string  $value The value
     */
    function Set($name, $value)
    {
        if (!$this->KeyExists($name)) {
            return false;
        }

        $xss   = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $value = $xss->parse($value);

        $this->_Registry[$name] = $value;

        $params          = array();
        $params['name']  = $name;
        $params['value'] = $value;

        $sql = "
        UPDATE [[acl]] SET
            [key_value] = {value}
        WHERE [key_name] = {name}";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Search for a key in the setted registry table
     *
     * @access  public
     * @param   string  Key to find
     * @return  string  The value of the key
     */
    function GetFromTable($name)
    {
        $params         = array();
        $params['name'] = $name;

        $sql = "
            SELECT
                [key_value]
            FROM [[acl]]
            WHERE [key_name] = {name}
            ORDER BY [key_name]";

        $value = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($value)) {
            return null;
        }

        if (!empty($value)) {
            // lets update the internal array just in case
            $this->_Registry[$name] = $value;
            return $value;
        }

        return null;
    }

    /**
     * Creates a new key
     *
     * @access  public
     * @param   string  $name  The key
     * @param   string  $value The value
     */
    function NewKey($name, $value)
    {
        if ($this->KeyExists($name)) {
            return false; //already exists
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['name']  = $name;
        $params['value'] = $xss->parse($value);
        $params['now']   = $GLOBALS['db']->Date();

        $sql = "
            INSERT INTO [[acl]]
                ([key_name], [key_value], [updatetime])
            VALUES
                ({name}, {value}, {now})";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$name] = $value;
        return true;
    }

    /**
     * Creates a array of new keys
     *
     * @access  public
     */
    function NewKeyEx()
    {
        $sqls = '';
        $params = array();
        $reg_keys = func_get_args();

        // for support array of keys array
        if (isset($reg_keys[0][0]) && is_array($reg_keys[0][0])) {
            $reg_keys = $reg_keys[0];
        }

        if (empty($reg_keys) || empty($reg_keys[0])) {
            return true;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $dbDriver  = $GLOBALS['db']->getDriver();
        $dbVersion = $GLOBALS['db']->getDBVersion();
        foreach ($reg_keys as $idx => $reg_key) {
            if ($this->KeyExists($reg_key[0])) {
                unset($reg_keys[$idx]);
            } else {
                $params["name_$idx"]  = $reg_key[0];
                $params["value_$idx"] = $xss->parse($reg_key[1]);
                // Ugly hack to support all databases
                switch ($dbDriver) {
                    case 'oci8':
                        $sqls .= (empty($sqls)? '' : "\n UNION ALL") . "\n SELECT {name_$idx}, {value_$idx}, {now} FROM DUAL";
                        break;
                    case 'ibase':
                        $sqls[] = " VALUES ({name_$idx}, {value_$idx}, {now})";
                        break;
                    case 'pgsql':
                        if (version_compare($dbVersion, '8.2.0', '>=')) {
                            $sqls .= (empty($sqls)? "\n VALUES" : ",") . "\n ({name_$idx}, {value_$idx}, {now})";
                        } else {
                            $sqls[] = " VALUES ({name_$idx}, {value_$idx}, {now})";
                        }
                        break;
                    default:
                        $sqls .= (empty($sqls)? '' : "\n UNION ALL") . "\n SELECT {name_$idx}, {value_$idx}, {now}";
                        break;
                }
            }
        }

        if (empty($sqls)) {
            return false;
        }

        $params['now'] = $GLOBALS['db']->Date();

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = " INSERT INTO [[acl]]([key_name], [key_value], [updatetime])" . $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = " INSERT INTO [[acl]]([key_name], [key_value], [updatetime])" . $sqls;
            $result = $GLOBALS['db']->query($qsql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        foreach ($reg_keys as $idx => $reg_key) {
            if (empty($reg_keys[$idx])) continue;
            $this->_Registry[$reg_key[0]] = $reg_key[1];
        }

        return true;
    }

    /**
     * Deletes a key
     *
     * @access  public
     * @param   string  $name  The key
     */
    function DeleteKey($name)
    {
        if ($this->KeyExists($name)) {
            unset($this->_Registry[$name]);
        }

        $params         = array();
        $params['name'] = $name;

        $sql = "DELETE FROM [[acl]] WHERE [key_name] = {name}";

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

        $this->LoadFile($gadget);
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
        $this->LoadFile($gadget);
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
     * Get all the ACL permissions
     *
     * @access  public
     * @param   string $user Username
     * @return  array  Struct that contains all needed info about *ALL* ACL keys
     */
    function GetAllAclPermissions()
    {
        $result = $this->GetAsQuery();
        $perms = array();
        foreach ($result as $r) {
            if (preg_match('#/ACL/gadgets/(.*?)/(.*?)#si', $r['name'])) {
                $gadget = preg_replace("@\/ACL/gadgets\/(\w+)\/(\w+)@", "\$1", $r['name']);
                $item = array();
                $item['name'] = $r['name'];
                $item['default'] = false;
                $item['value']   = true;
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
        if (!array_key_exists($component, $this->_LoadedFiles)) {
            $type = strtolower($type);
            if ($res = $this->_regenerateInternalRegistry($component, $type)) {
                $this->_LoadedFiles[$component] = $component;
            }

            return $res;
        }

        return true;
    }

    /**
     * Loads all the component files
     *
     * @access  public
     */
    function LoadAllFiles()
    {
        $gs = array_filter(explode(',', $GLOBALS['app']->Registry->Get('gadgets_enabled_items')));
        foreach ($gs as $gadget) {
            $this->LoadFile($gadget);
        }

        $ci = array_filter(explode(',', $GLOBALS['app']->Registry->Get('gadgets_core_items')));
        foreach ($ci as $gadget) {
            $this->LoadFile($gadget);
        }
    }

    /**
     * Returns the SimpleArray in a query style:
     *
     * $array[0] = array('name'  => 'foo',
     *                   'value' => 'bar'),
     * $array[1] = array('name'  => 'bar',
     *                   'value' => 'foo');
     *
     * @access  public
     * @return  array   Array in a QueryStyle
     */
    function GetAsQuery()
    {
        $data = array();
        foreach ($this->_Registry as $key => $value) {
            $data[] = array(
                'name'   => $key,
                'value'  => $value,
            );
        }

        return $data;
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
        $this->_Registry = $result + $this->_Registry;
    }

    /**
     * Regenerates/updates the internal registry array ($this->_Registry)
     *
     * @access  protected
     * @param   string     $component  Component name
     * @param   string     $type       Type of component (gadget or plugin)
     * @return  bool       Success/Failure
     */
    function _regenerateInternalRegistry($component, $type = 'gadgets')
    {
        $type = strtolower($type);
        if (!in_array($type, array('gadgets', 'plugins'))) {
            return false;
        }

        $sql = "
            SELECT [key_name], [key_value]
            FROM [[acl]]
            WHERE [key_name] LIKE '/ACL/$type/$component/%'
            ORDER BY [id]";

        $result = $GLOBALS['db']->queryAll($sql, array(), null, null, true);
        if (Jaws_Error::isError($result)) {
            return false;
        }

        $this->_Registry = $result + $this->_Registry;
        return true;
    }

}