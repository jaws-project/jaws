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
        //
    }

    /**
     * Search for a key in the setted registry table
     *
     * @access  public
     * @param   string  $name       Key name
     * @param   string  $component  Component name
     * @param   int     $type       Component type
     * @return  string  The value of the key
     */
    function Get($name, $component = '', $type = JAWS_COMPONENT_OTHERS)
    {
        if (!@array_key_exists($name, $this->_Registry[$type][$component])) {
            $params = array();
            $params['type']      = $type;
            $params['component'] = $component;
            $params['name']      = $name;

            $sql = '
                SELECT
                    [key_value]
                FROM [[registry]]
                WHERE
                    [component_type] = {type}
                  AND
                    [component_name] = {component}
                  AND
                    [key_name] = {name}';

            $row = $GLOBALS['db']->queryRow($sql, $params);
            if (Jaws_Error::IsError($row) || empty($row)) {
                return null;
            }

            $this->_Registry[$type][$component][$name] = $row['key_value'];
        }

        return $this->_Registry[$type][$component][$name];
    }

    /**
     * Updates the value of a key
     *
     * @access  public
     * @param   string  $name       Key name
     * @param   string  $value      Key value
     * @param   string  $component  Component name
     * @param   int     $type       Component type
     * @return  bool    True is set otherwise False
     */
    function Set($name, $value, $component = '', $type = JAWS_COMPONENT_OTHERS)
    {
        $params = array();
        $params['type']      = $type;
        $params['component'] = $component;
        $params['name']      = $name;
        $params['value']     = $value;

        $sql = '
            UPDATE [[registry]] SET
                [key_value] = {value}
            WHERE
                [component_type] = {type}
              AND
                [component_name] = {component}
              AND
                [key_name] = {name}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$type][$component][$name] = $value;
        return true;
    }

    /**
     * Creates a new key
     *
     * @access  public
     * @param   string  $name       Key name
     * @param   string  $value      Key value
     * @param   string  $component  Component name
     * @param   int     $type       Component type
     * @return  bool    True is set otherwise False
     */
    function NewKey($name, $value, $component = '', $type = JAWS_COMPONENT_OTHERS)
    {
        $params = array();
        $params['type']      = $type;
        $params['component'] = $component;
        $params['name']      = $name;
        $params['value']     = $value;
        $params['now']       = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[registry]]
                ([component_type], [component_name], [key_name], [key_value], [updatetime])
            VALUES
                ({type}, {component}, {name}, {value}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$type][$component][$name] = $value;
        return true;
    }

    /**
     * Creates a array of new keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $component  Component name
     * @param   int     $type       Component type
     * @return  bool    True is set otherwise False
     */
    function NewKeyEx($keys, $component = '', $type = JAWS_COMPONENT_OTHERS)
    {
        if (empty($keys)) {
            return true;
        }

        $params = array();
        $params['type']      = $type;
        $params['component'] = $component;
        $params['now']       = $GLOBALS['db']->Date();

        $sqls = '';
        $dbDriver  = $GLOBALS['db']->getDriver();
        $dbVersion = $GLOBALS['db']->getDBVersion();
        for ($ndx = 0; $ndx < count($keys); $ndx++) {
            list($name, $value) = each($keys);
            $params["name_$ndx"]  = $name;
            $params["value_$ndx"] = $value;

            // Ugly hack to support all databases
            switch ($dbDriver) {
                case 'oci8':
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {type}, {component}, {name_$ndx}, {value_$ndx}, {now} FROM DUAL";
                    break;
                case 'ibase':
                    $sqls[] = " VALUES ({type}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    break;
                case 'pgsql':
                    if (version_compare($dbVersion, '8.2.0', '>=')) {
                        $sqls .= (empty($sqls)? "\n VALUES" : ",").
                                 "\n ({type}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    } else {
                        $sqls[] = " VALUES ({type}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    }
                    break;
                default:
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {type}, {component}, {name_$ndx}, {value_$ndx}, {now}";
                    break;
            }

            $this->_Registry[$type][$component][$name] = $value;
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = '
                    INSERT INTO [[registry]]
                        ([component_type], [component_name], [key_name], [key_value], [updatetime])
                    '. $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = '
                INSERT INTO [[registry]]
                    ([component_type], [component_name], [key_name], [key_value], [updatetime])
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
     * @param   string  $name  The key
     * @param   string  $component  Component name
     * @param   string  $type       Component type
     * @return  bool    True is set otherwise False
     */
    function DeleteKey($name, $component = '', $type = JAWS_COMPONENT_OTHER)
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

}