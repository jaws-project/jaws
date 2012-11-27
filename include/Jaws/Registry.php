<?php
/**
 * Class to manage jaws registry
 *
 * @category   Registry
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Registry
{
    /**
     * Has the registry
     *
     * @var     array
     * @access  private
     * @see    GetSimpleArray()
     */
    var $_Registry = array();

    /**
     * Date of last update
     *
     * @var    date
     * @access  private
     * @see    GetLastUpdate();
     */
    var $_LastUpdate;

    /**
     * Array that has a *registry* of files that have been called
     *
     * @var     array
     * @access  private
     */
    var $_LoadedFiles = array();

    /**
     * Loads the data from the DB
     *
     * @access  public
     */
    function Init()
    {
        // Fetch the enabled/version part
        $sql = "
            SELECT [key_name], [key_value] FROM [[registry]]
            WHERE
                [key_name] LIKE '%/enabled'
            OR
                [key_name] LIKE '%/version'";

        $result = $GLOBALS['db']->queryAll($sql, array(), null, null, true);
        if (Jaws_Error::isError($result)) {
            Jaws_Error::Fatal("Failed to fetch enabled data for registry<br />" .
                             $result->getMessage());
        }
        $this->_Registry = $result;

        $this->LoadFile('core');
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
            FROM [[registry]]
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
     * Search for a key in the registry
     *
     * @access  public
     * @param   string  $name       Key to find
     * @return  string  The value of the key
     */
    function Get($name)
    {
        $value = $this->KeyExists($name) ? $this->_Registry[$name] : null;
        return $value;
    }

    /**
     * Get the date of last update
     * @access  public
     */
    function GetLastUpdate()
    {
        return $this->_LastUpdate;
    }

    /**
     * Updates the original registry
     *
     * @access  private
     */
    function UpdateLastUpdate()
    {
        $params = array();
        $params['now'] = $GLOBALS['db']->Date();

        $sql = "
            UPDATE [[registry]] SET
                [key_value] = {now}
            WHERE [key_name] = '/last_update'";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_LastUpdate = $this->_Registry['/last_update'] = $params['now'];
        return true;
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
        $params = array();
        $params['name']  = $name;
        $params['value'] = $value;

        $sql = "
        UPDATE [[registry]] SET
            [key_value] = {value}
        WHERE [key_name] = {name}";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$name] = $value;
        return $this->UpdateLastUpdate();
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

        $params = array();
        $params['name']  = $name;
        $params['value'] = $value;
        $params['now']   = $GLOBALS['db']->Date();

        $sql = "
            INSERT INTO [[registry]]
                ([key_name], [key_value], [updatetime])
            VALUES
                ({name}, {value}, {now})";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$name] = $value;
        return $this->UpdateLastUpdate();
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

        $dbDriver  = $GLOBALS['db']->getDriver();
        $dbVersion = $GLOBALS['db']->getDBVersion();
        foreach ($reg_keys as $idx => $reg_key) {
            if ($this->KeyExists($reg_key[0])) {
                unset($reg_keys[$idx]);
            } else {
                $params["name_$idx"]  = $reg_key[0];
                $params["value_$idx"] = $reg_key[1];
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
                $qsql = " INSERT INTO [[registry]]([key_name], [key_value], [updatetime])" . $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = " INSERT INTO [[registry]]([key_name], [key_value], [updatetime])" . $sqls;
            $result = $GLOBALS['db']->query($qsql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        foreach ($reg_keys as $idx => $reg_key) {
            if (empty($reg_keys[$idx])) continue;
            $this->_Registry[$reg_key[0]] = $reg_key[1];
        }

        return $this->UpdateLastUpdate();
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

        $params = array();
        $params['name'] = $name;

        $sql = "DELETE FROM [[registry]] WHERE [key_name] = {name}";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->UpdateLastUpdate();
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

    /* Functions below are for the FS cache */

    /**
     * Creates the JAWS_CACHE . 'registry|acl' . directory to store keys
     *
     * @access  public
     */
    function CreateCacheDirectory()
    {
        $new_dirs = array();
        $new_dirs[] = JAWS_CACHE;
        $new_dirs[] = JAWS_CACHE. 'registry';
        $new_dirs[] = JAWS_CACHE. 'registry'. DIRECTORY_SEPARATOR. 'gadgets';
        $new_dirs[] = JAWS_CACHE. 'registry'. DIRECTORY_SEPARATOR. 'plugins';
        foreach ($new_dirs as $new_dir) {
            if (!Jaws_Utils::mkdir($new_dir)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_REGISTRY_CACHEDIR_NOT_WRITABLE', $new_dir),
                                      __FUNCTION__);
            }
        }

        return true;
    }

    /**
     * Saves the key array file in JAWS_CACHE . $table.'/(gadgets|plugins)' . $component
     *
     * @access  public
     * @param   string  $comp    Component's name
     * @param   string  $type    The type of the component, (plugin or a gadget) only
     *                           if the component name is not empty
     * @param   string  $regexp  Regexp used when we check 'core'
     */
    function Commit($comp, $type = 'gadgets', $regexp = '')
    {
        //We don't accept full commits
        if (empty($comp)) {
            return false;
        }

        $res = $this->CreateCacheDirectory();
        if (Jaws_Error::isError($res)) {
            return false;
        }

        /* Since there's no component named core in jaws nor
         * is there a single namespace for core configs then
         * we will have to trick the system a bit.
         * make the component be nothing and when we are looping
         * over all the entries and $core = true then it will only
         * include core elements.
         */
        if ($comp === 'core') {
            if (empty($regexp)) {
                $regexp = '#^/(gadgets|(plugins/parse_text))/(.*?)/(.*?)#i';
            }

            //Do a deep search..
            $search = '/';
            $type   = 'core';
        } else {
            $search = '/' . $comp . '/';

            $type = strtolower($type);
            if (!in_array($type, array('gadgets', 'plugins'))) {
                return false;
            }
        }

        $result = "\$registry = array();\n";
        foreach ($this->_Registry as $key => $value) {
            if (strpos($key, $search) === false) {
                continue;
            }
            // Ok, check if we are doing a commit to core
            if ($comp == 'core') {
                // We have some registry keys which should never go to core.php
                $ret = preg_match($regexp, $key, $matches);
                if ($ret == 0) {
                    // Ok, item 0 exists and is empty, then add it to core, now add it
                    $result .= "\$registry['".$key."'] = '".addslashes($value)."';\n";
                }
            } else {
                /**
                 * Since we don't add /enabled and /version keys to cache files we should
                 * check it for them
                 */
                $exclude = substr($key, -8);
                if ($exclude == '/enabled' || $exclude == '/version') {
                    continue;
                }

                $result .= "\$registry['".$key."'] = '".addslashes($value)."';\n";
            }
        }

        $this->CommitWriteFile($result, $comp, $type);
        return true;
    }

    /**
     * Commits an string of registry-keys to a cache file
     *
     * @access  private
     * @param   string An string of registry-keys
     * @param   string Component that is being added
     * @param   string What kind of registry-keys does $data has?
     *                   Possible values are:
     *                    - gadgets
     *                    - plugins
     *                    - core
     */
    function CommitWriteFile($data, $comp, $type)
    {
        $type = strtolower($type);
        if (!in_array($type, array('gadgets', 'plugins'))) {
            //Core
            $add = '';
        } else {
            //gadgets/plugins
            $add = $type . '/';
        }

        $file  = JAWS_CACHE . 'registry/'. $add . $comp . '.php';
        $content = "<?php\n" . $data;
        Jaws_Utils::file_put_contents($file, $content);
    }

    /**
     * Loads the keys of a component and optionally it returns the keys found in the file
     *
     * @access  public
     * @param   string  $component Component's name
     */
    function LoadFile($component, $type = 'gadgets', $return = false)
    {
        $type = strtolower($type);
        unset($this->_LoadedFiles['core']);
        if ($component === '' || !in_array($type, array('gadgets', 'plugins')) || in_array($component, $this->_LoadedFiles)) {
            return;
        }
        $add = $component !== 'core' ? $type . '/' : '';
        $file = JAWS_CACHE . 'registry/' . $add . $component . '.php';
        $exists = file_exists($file);
        if ($exists) {
            require $file;
            $this->_LoadedFiles[$component] = $component;
            // $registry comes from the file loaded
            if (isset($registry) && is_array($registry)) {
                foreach ($registry as $key => $value) {
                    $this->_Registry[$key] = stripslashes($value);
                }

                if ($return) {
                    return $registry;
                }
            }

            return true;
        } else {
            // Cache file doesn't exist, lets generate it
            $res = $this->_regenerateInternalRegistry($component, $type);
            if (!$res) {
                return;
            }
            $this->commit($component, $type);
            return $this->loadFile($component, $type, $return);
        }
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

        if ($component == 'core') {
            // Big ass query since the core isn't under one namespace
            $sql = "
                SELECT [key_name], [key_value]
                FROM [[registry]]
                WHERE
                    [key_name] LIKE '/config/%'
                   OR
                    [key_name] LIKE '/map/%'
                   OR
                    [key_name] LIKE '/network/%'
                   OR
                    [key_name] LIKE '/policy/%'
                   OR
                    [key_name] LIKE '/crypt/%'
                   OR
                    [key_name] IN('/version', '/last_update',
                                  '/plugins/parse_text/enabled_items',
                                  '/plugins/parse_text/admin_enabled_items',
                                  '/gadgets/enabled_items',
                                  '/gadgets/autoload_items', '/gadgets/core_items')
                ORDER BY [key_name]";
        } else {
            if ($type == 'gadgets') {
                $sql = "
                    SELECT [key_name], [key_value]
                    FROM [[registry]]
                    WHERE [key_name] LIKE '/gadgets/".$component."/%'
                    ORDER BY [key_name]";
            } else {
                $sql = "
                    SELECT [key_name], [key_value]
                    FROM [[registry]]
                    WHERE [key_name] LIKE '/plugins/".$component."/%' OR [key_name] LIKE '/plugins/parse_text/".$component."/%'
                    ORDER BY [key_name]";
            }
        }

        $result = $GLOBALS['db']->queryAll($sql, array(), null, null, true);
        if (Jaws_Error::isError($result)) {
            return false;
        }

        $this->_Registry = $result + $this->_Registry;
        return true;
    }

    /**
     * Loads all the component files
     *
     * @access  public
     */
    function LoadAllFiles()
    {
        ///FIXME check for errors
        $gs = explode(',', $this->get('/gadgets/enabled_items'));
        $ci = explode(',', $this->get('/gadgets/core_items'));
        $ps = explode(',', $this->get('/plugins/parse_text/admin_enabled_items'));

        $ci = str_replace(' ', '', $ci);
        $ps = str_replace(' ', '', $ps);

        // load the core
        $this->LoadFile('core');

        foreach ($gs as $gadget) {
            $this->LoadFile($gadget);
        }

        foreach ($ci as $gadget) {
            $this->LoadFile($gadget);
        }

        foreach ($ps as $plugin) {
            $this->LoadFile($plugin, 'plugins');
        }
    }

    /**
     * Deletes the cache file of a certain component
     *
     * @acess   public
     * @access  protected
     * @param   string     $name       Component name
     * @param   string     $type       Type of component (gadget or plugin)
     * @return  bool       Success/Failure
     */
    function deleteCacheFile($name, $type = 'gadgets')
    {
        $type = strtolower($type);
        if (empty($name) || !in_array($type, array('gadgets', 'plugins'))) {
            return false;
        }

        $add = $name !== 'core' ? $type . '/' : '';
        $file = JAWS_CACHE . 'registry/' . $add . $name . '.php';
        if (file_exists($file)) {
            unlink($file);
            return true;
        }
        return false;
    }

    /**
     * Resets/Cleans the registry
     *
     * @access  public
     */
    function ResetData()
    {
        $this->_Registry    = array();
        $this->_LoadedFiles = array();
    }

}
