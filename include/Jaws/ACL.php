<?php
/**
 * Manage Access control lists
 *
 * @category    ACL
 * @package     Core
 * @author      Ivan Chavero <imcsk8@gluch.org.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ACL
{
    /**
     * All default ACLs
     *
     * @var     array
     * @access  private
     */
    private $default_acls = array();

    /**
     * All users's ACLs
     *
     * @var     array
     * @access  private
     */
    private $users_acls = array();

    /**
     * All groups's ACLs
     *
     * @var     array
     * @access  private
     */
    private $groups_acls = array();

    /**
     * Loads the data from the DB
     *
     * @access  public
     * @param   string  $user   User ID
     * @param   string  $groups Array of groups ID
     * @return  void
     */
    function Init($user = 0, $groups = array())
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        // fetch default ACLs
        $result = $tblACL->select('component', 'key_name', 'key_subkey', 'key_value:integer')
            ->where('user', 0)->and()->where('group', 0)
            ->orderBy('component', 'key_name', 'key_subkey')
            ->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return null;
        }

        // store default ACLs
        foreach ($result as $acl) {
            $this->default_acls[$acl['component']][$acl['key_name']][$acl['key_subkey']] = $acl['key_value'];
        }

        // fetch passed user's ACLs
        if (!empty($user)) {
            $result = $tblACL->select('component', 'key_name', 'key_subkey', 'key_value:integer')
                ->where('user', (int)$user)->and()->where('group', 0)
                ->orderBy('component', 'key_name', 'key_subkey')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return null;
            }

            // store passed user's ACLs
            $this->users_acls[$user] = array();
            foreach ($result as $acl) {
                $this->users_acls[$user][$acl['component']][$acl['key_name']][$acl['key_subkey']] =
                    $acl['key_value'];
            }
        }

        // fetch passed groups's ACLs
        foreach ($groups as $group) {
            $result = $tblACL->select('component', 'key_name', 'key_subkey', 'key_value:integer')
                ->where('user', 0)->and()->where('group', (int)$group)
                ->orderBy('component', 'key_name', 'key_subkey')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return null;
            }

            // store passed group's ACLs
            $this->groups_acls[$group] = array();
            foreach ($result as $acl) {
                $this->groups_acls[$group][$acl['component']][$acl['key_name']][$acl['key_subkey']] =
                    $acl['key_value'];
            }
        }
    }

    /**
     * Fetch the key value
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_subkey Subkey name
     * @param   string  $component  Component name
     * @return  string  The value of the key
     */
    function fetch($key_name, $subkey, $component)
    {
        return @$this->default_acls[$component][$key_name][$subkey];
    }

    /**
     * Fetch all acl keys of the gadget
     *
     * @access  public
     * @param   string  $component  Component name
     * @return  mixed   Array of keys if successful or Jaws_Error on failure
     */
    function fetchAll($component)
    {
        return @$this->default_acls[$component];
    }

    /**
     * Fetch the ACL key value by user
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $key_name   Key name
     * @param   string  $key_subkey Subkey name
     * @param   string  $component  Component name
     * @return  mixed   Value of the key if success otherwise Null
     */
    function fetchByUser($user, $key_name, $subkey, $component)
    {
        if (isset($this->users_acls[$user])) {
            return @$this->users_acls[$user][$component][$key_name][$subkey];
        } else {
            $tblACL = Jaws_ORM::getInstance()->table('acl');
            $uvalue = $tblACL->select('key_value:integer')
                ->where('component', $component)
                ->and()
                ->where('key_name', $key_name)
                ->and()
                ->where('key_subkey', (string)$subkey)
                ->and()
                ->where('user', (int)$user)
                ->and()
                ->where('group', 0)
                ->fetchOne();
            if (!Jaws_Error::IsError($uvalue)) {
                return $uvalue;
            }
        }

        return null;
    }

    /**
     * Fetch all ACL keys/values releated to the user
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $component  Component name
     * @return  mixed   Array of ACLs if success otherwise Null
     */
    function fetchAllByUser($user, $component = '')
    {
        if (isset($this->users_acls[$user])) {
            // load from cache
            if (empty($component)) {
                return @$this->users_acls[$user];
            }

            return @$this->users_acls[$user][$component];
        } else {
            // load from database
            $tblACL = Jaws_ORM::getInstance()->table('acl');
            $tblACL->select('component', 'key_name', 'key_subkey', 'key_value:integer');
            $tblACL->where('user', (int)$user)->and()->where('group', 0);
            if (!empty($component)) {
                $tblACL->and()->where('component', $component);
            }
            $tblACL->orderBy('component', 'key_name', 'key_subkey');
            $result = $tblACL->fetchAll();
            if (!Jaws_Error::IsError($result)) {
                // uniformed result with output of cached ACLs
                foreach ($result as $acl) {
                    $acls[$acl['component']][$acl['key_name']][$acl['key_subkey']] = $acl['key_value'];
                }
                if (empty($component)) {
                    return $acls;
                }

                return @$acls[$component];
            }
        }

        return null;
    }

    /**
     * Fetch the ACL key value by group
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   string  $key_name   Key name
     * @param   string  $key_subkey Subkey name
     * @param   string  $component  Component name
     * @return  mixed   Value of the key if success otherwise Null
     */
    function fetchByGroup($group, $key_name, $subkey, $component)
    {
        if (isset($this->groups_acls[$group])) {
            return @$this->groups_acls[$group][$component][$key_name][$subkey];
        } else {
            $tblACL = Jaws_ORM::getInstance()->table('acl');
            $gvalue = $tblACL->select('key_value:integer')
                ->where('component', $component)
                ->and()
                ->where('key_name', $key_name)
                ->and()
                ->where('key_subkey', (string)$subkey)
                ->and()
                ->where('user', 0)
                ->and()
                ->where('group', (int)$group)
                ->fetchOne();
            if (!Jaws_Error::IsError($gvalue)) {
                return $gvalue;
            }
        }

        return null;
    }

    /**
     * Fetch all ACL keys/values related to the group
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   string  $component  Component name
     * @return  mixed   Array of ACLs if success otherwise Null
     */
    function fetchAllByGroup($group, $component = '')
    {
        if (isset($this->groups_acls[$group])) {
            // load from cache
            if (empty($component)) {
                return @$this->groups_acls[$group];
            }

            return @$this->groups_acls[$group][$component];
        } else {
            // load from database
            $tblACL = Jaws_ORM::getInstance()->table('acl');
            $tblACL->select('component', 'key_name', 'key_subkey', 'key_value:integer');
            $tblACL->where('user', 0)->and()->where('group', (int)$group);
            if (!empty($component)) {
                $tblACL->and()->where('component', $component);
            }
            $tblACL->orderBy('component', 'key_name', 'key_subkey');
            $result = $tblACL->fetchAll();
            if (!Jaws_Error::IsError($result)) {
                // uniformed result with output of cached ACLs
                foreach ($result as $acl) {
                    $acls[$acl['component']][$acl['key_name']][$acl['key_subkey']] = $acl['key_value'];
                }
                if (empty($component)) {
                    return $acls;
                }

                return @$acls[$component];
            }
        }

        return null;
    }

    /**
     * Insert a new key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $subkey     Subkey name
     * @param   int     $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function insert($key_name, $subkey, $key_value, $component)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $tblACL->insert(
            array(
                'component' => $component,
                'key_name'  => $key_name,
                'key_subkey' => (string)$subkey,
                'key_value' => (int)$key_value,
                'user'      => 0,
                'group'     => 0,
            )
        );
        $result = $tblACL->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->default_acls[$component][$key_name][$subkey] = (int)$key_value;
        return true;
    }

    /**
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $component  Component name
     * @param   int     $user       User ID
     * @param   int     $group      Group ID
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $component, $user = 0, $group = 0)
    {
        if (empty($keys)) {
            return true;
        }

        $data = array();
        $user = (int)$user;
        $group = (int)$group;
        $columns = array('component', 'key_name', 'key_subkey', 'key_value', 'user', 'group');
        foreach ($keys as $key) {
            list($key_name, $subkey, $key_value) = $key;
            $data[] = array($component, $key_name, $subkey, $key_value, $user, $group);
        }

        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->insertAll($columns, $data)->exec();
        return $result;
    }

    /**
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $subkey     Subkey name
     * @param   int     $key_value  Key value
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $subkey, $key_value, $component)
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->update(array('key_value' => (int)$key_value))
            ->where('component', $component)->and()
            ->where('key_name', $key_name)->and()
            ->where('key_subkey', (string)$subkey)->and()
            ->where('user', 0)->and()->where('group', 0)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->default_acls[$component][$key_name][$subkey] = (int)$key_value;
        return true;
    }

    /**
     * Renames a ACL key
     *
     * @access  public
     * @param   string  $old_name   Old key name
     * @param   string  $new_name   New key name
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function rename($old_name, $new_name, $component = '')
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $result = $tblACL->update(array('key_name' => $new_name))
            ->where('component', $component)
            ->and()
            ->where('key_name', $old_name)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->default_acls[$component][$new_name] = $this->default_acls[$component][$old_name];
        unset($this->default_acls[$component][$old_name]);
        return true;
    }

    /**
     * Get the real/full permission of a gadget (and group if it has) for a certain key/subkey
     *
     * @access  public
     * @param   int     $user           User ID
     * @param   int     $groups         Array of group's ID or empty string
     * @param   string  $gadget         Gadget to use
     * @param   string  $key            ACL key name
     * @param   string  $subkey         ACL subkey name
     * @param   bool    $is_super_admin
     * @return  integer Permission value: Granted (1) or Denied (0)
     */
    function GetFullPermission($user, $groups, $gadget, $key, $subkey = '', $is_super_admin = false)
    {
        // is in forbidden acls?
        if (defined('JAWS_FORBIDDEN_ACLS')) {
            static $forbidden_acls;
            if (!isset($forbidden_acls)) {
                $forbidden_acls = array_filter(array_map('trim', explode(',', strtolower(JAWS_FORBIDDEN_ACLS))));
            }

            if (in_array(strtolower("$gadget:$key"), $forbidden_acls)) {
                return 0;
            }
        }

        if (defined('JAWS_GODUSER_ACLS') && defined('JAWS_GODUSER') && JAWS_GODUSER !== $user) {
            static $goduser_acls;
            if (!isset($goduser_acls)) {
                $goduser_acls = array_filter(array_map('trim', explode(',', strtolower(JAWS_GODUSER_ACLS))));
            }
            if (in_array(strtolower("$gadget:$key"), $goduser_acls)) {
                return 0;
            }
        }

        // don't check permissions for administrators
        if ($is_super_admin === true) {
            return 0xff;
        }

        // session in restricted mode, only users permission will be checked
        if (defined('RESTRICTED_SESSION') && !in_array($gadget, array('Users', 'ControlPanel'))) {
            return 0;
        }

        // 1. Check for user permission
        $perm['user'] = $this->fetchByUser($user, $key, $subkey, $gadget);
        if (!is_null($perm['user'])) {
            return $perm['user'];
        }

        // 2. Check for groups permission
        if (!empty($groups)) {
            $perm['group'] = 0;
            foreach ($groups as $group) {
                $permGroup = $this->fetchByGroup($group, $key, $subkey, $gadget);
                if (is_null($permGroup)) {
                    if ($perm['group'] === 0) {
                        $perm['group'] = null;
                    }
                } elseif (is_null($perm['group'])) {
                    $perm['group'] = $permGroup === 0? null : $permGroup;
                } else {
                    $perm['group'] = max($perm['group'], $permGroup);
                }
            }

            if (!is_null($perm['group'])) {
                return $perm['group'];
            }
        }

        // 3. Check for default
        $perm['default'] = $this->fetch($key, $subkey, $gadget);
        if (is_null($perm['default']) && ($subkey !== '')) {
            $perm['default'] = $this->fetch($key, '', $gadget);
        }

        return (int)$perm['default'];
    }

    /**
     * Deletes a key or all ACLS related to the component
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   string  $key_name   Key name
     * @param   string  $subkey     Subkey name
     * @return  bool    True is set otherwise False
     */
    function delete($component, $key_name = '', $subkey = '')
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $tblACL->delete()->where('component', $component);
        if (!empty($key_name)) {
            $tblACL->and()->where('key_name', $key_name);
            if (!empty($subkey)) {
                $tblACL->and()->where('key_subkey', (string)$subkey);
            }
        }

        $result = $tblACL->exec();
        return !Jaws_Error::IsError($result);
    }

    /**
     * Delete all ACLs related to the user
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $component  Component name
     * @return  bool    True if success otherwise False
     */
    function deleteByUser($user, $component = '')
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $tblACL->delete()->where('user', (int)$user);
        if (!empty($component)) {
            $tblACL->and()->where('component', $component);
        }
        $result = $tblACL->exec();
        return !Jaws_Error::IsError($result);
    }

    /**
     * Delete all ACLs related to the group
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   string  $component  Component name
     * @return  bool    True if success otherwise False
     */
    function deleteByGroup($group, $component = '')
    {
        $tblACL = Jaws_ORM::getInstance()->table('acl');
        $tblACL->delete()->where('group', (int)$group);
        if (!empty($component)) {
            $tblACL->and()->where('component', $component);
        }
        $result = $tblACL->exec();
        return !Jaws_Error::IsError($result);
    }

}