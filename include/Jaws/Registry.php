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
    }

    /**
     * Search for a key in the setted registry table
     *
     * @access  public
     * @param   string  Key to find
     * @return  string  The value of the key
     */
    function Get($name)
    {
        if (!array_key_exists($name, $this->_Registry)) {
            $params = array();
            $params['name'] = $name;

            $sql = "
                SELECT
                    [key_name], [key_value]
                FROM [[registry]]
                WHERE [key_name] = {name}";

            $row = $GLOBALS['db']->queryRow($sql, $params);
            if (Jaws_Error::IsError($row) || empty($row)) {
                return null;
            }

            $this->_Registry[$name] = $row['key_value'];
        }

        return $this->_Registry[$name];
    }

    /**
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $name  The key
     * @param   string  $value The value
     * @return  bool    True is set otherwise False
     */
    function Set($name, $value)
    {
        $params = array();
        $params['name']  = $name;
        $params['value'] = $value;

        $sql = '
        UPDATE [[registry]] SET
            [key_value] = {value}
        WHERE [key_name] = {name}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$name] = $value;
        return true;
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

        $dbDriver  = $GLOBALS['db']->getDriver();
        $dbVersion = $GLOBALS['db']->getDBVersion();
        foreach ($reg_keys as $idx => $reg_key) {
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
        $params = array();
        $params['name'] = $name;

        $sql = '
            DELETE
                FROM [[registry]]
            WHERE
                [key_name] = {name}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

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
    function LoadFile($component, $type = 'gadgets', $return = false)
    {
    }

    /**
     * Loads all the component files
     *
     * @access  public
     */
    function LoadAllFiles()
    {
    }

}