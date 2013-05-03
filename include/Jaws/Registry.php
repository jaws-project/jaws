<?php
/**
 * Class to manage jaws registry
 *
 * @category   Registry
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
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
     * Fetch the key value
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $cmp_name   Component name
     * @return  string  The value of the key
     */
    function fetch($key_name, $cmp_name = '')
    {
        if (!@array_key_exists($key_name, $this->_Registry[$cmp_name])) {
            $params = array();
            $params['cmp_name'] = $cmp_name;
            $params['key_name'] = $key_name;

            $sql = '
                SELECT
                    [cmp_name], [key_name], [key_value]
                FROM [[registry]]
                WHERE
                    [cmp_name] = {cmp_name}
                  AND
                    [key_name] = {key_name}';

            $row = $GLOBALS['db']->queryRow($sql, $params);
            if (Jaws_Error::IsError($row) || empty($row) ||
                $row['cmp_name'] !== $cmp_name || $row['key_name'] !== $key_name)
            {
                return null;
            }

            $this->_Registry[$cmp_name][$key_name] = $row['key_value'];
        }

        return $this->_Registry[$cmp_name][$key_name];
    }

    /**
     * Insert a new key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   string  $cmp_name   Component name
     * @param   string  $cmp_type   Component type(0: core, 1: gadget, 2: plugin)
     * @return  bool    True is set otherwise False
     */
    function insert($key_name, $key_value, $cmp_name = '', $cmp_type = 0)
    {
        $params = array();
        $params['cmp_name']  = $cmp_name;
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
        $params['cmp_type']  = $cmp_type;
        $params['now']       = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[registry]]
                ([cmp_name], [key_name], [key_value], [cmp_type], [updatetime])
            VALUES
                ({cmp_name}, {key_name}, {key_value}, {cmp_type}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$cmp_name][$key_name] = $key_value;
        return true;
    }

    /**
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $cmp_name   Component name
     * @param   string  $cmp_type   Component type(0: core, 1: gadget, 2: plugin)
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $cmp_name = '', $cmp_type = 0)
    {
        if (empty($keys)) {
            return true;
        }

        $params = array();
        $params['cmp_name'] = $cmp_name;
        $params['cmp_type'] = $cmp_type;
        $params['now']      = $GLOBALS['db']->Date();

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
                             "\n SELECT {cmp_name}, {name_$ndx}, {value_$ndx}, {cmp_type}, {now} FROM DUAL";
                    break;
                case 'ibase':
                    $sqls[] = " VALUES ({cmp_name}, {name_$ndx}, {value_$ndx}, {cmp_type}, {now})";
                    break;
                case 'pgsql':
                    if (version_compare($dbVersion, '8.2.0', '>=')) {
                        $sqls .= (empty($sqls)? "\n VALUES" : ",").
                                 "\n ({cmp_name}, {name_$ndx}, {value_$ndx}, {cmp_type}, {now})";
                    } else {
                        $sqls[] = " VALUES ({cmp_name}, {name_$ndx}, {value_$ndx}, {cmp_type}, {now})";
                    }
                    break;
                default:
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {cmp_name}, {name_$ndx}, {value_$ndx}, {cmp_type}, {now}";
                    break;
            }

            $this->_Registry[$cmp_name][$key_name] = $key_value;
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = '
                    INSERT INTO [[registry]]
                        ([cmp_name], [key_name], [key_value], [cmp_type], [updatetime])
                    '. $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = '
                INSERT INTO [[registry]]
                    ([cmp_name], [key_name], [key_value], [cmp_type], [updatetime])
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
     * @param   string  $key_value  Key value
     * @param   string  $cmp_name   Component name
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $key_value, $cmp_name = '')
    {
        $params = array();
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
        $params['cmp_name']  = $cmp_name;

        $sql = '
            UPDATE [[registry]] SET
                [key_value] = {key_value}
            WHERE
                [cmp_name] = {cmp_name}
              AND
                [key_name] = {key_name}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$cmp_name][$key_name] = $key_value;
        return true;
    }

    /**
     * Deletes a key
     *
     * @access  public
     * @param   string  $cmp_name   Component name
     * @param   string  $key_name   Key name
     * @return  bool    True is set otherwise False
     */
    function delete($cmp_name, $key_name = '')
    {
        $params = array();
        $params['cmp_name'] = $cmp_name;
        $params['key_name'] = $key_name;

        $sql = '
            DELETE
                FROM [[registry]]
            WHERE
                [cmp_name] = {cmp_name}
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