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
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $result = $tblReg->select('component', 'key_name', 'key_value')
            ->where('key_name', 'version')->or()->where('component', '')
            ->getAll();
        if (!Jaws_Error::IsError($result)) {
            foreach ($result as $regrec) {
                $this->_Registry[$regrec['component']][$regrec['key_name']] = $regrec['key_value'];
            }
        }
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
        if (!@array_key_exists($key_name, $this->_Registry[$component])) {
            $tblReg = Jaws_ORM::getInstance()->table('registry');
            $value  = $tblReg->select('key_value')
                ->where('component', $component)->and()
                ->where('key_name', $key_name)
                ->getOne();
            if (Jaws_Error::IsError($value)) {
                return null;
            }

            $this->_Registry[$component][$key_name] = $value;
        }

        return $this->_Registry[$component][$key_name];
    }

    /**
     * Fetch all registry keys of the gadget
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   string  $pattern    Key pattern
     * @return  mixed   Array of keys if successful or Jaws_Error on failure
     */
    function fetchAll($component = '', $pattern = '')
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->select('key_name', 'key_value')->where('component', $component);
        if (!empty($pattern)) {
            $tblReg->and()->where('key_name', $pattern, 'like');
        }

        $result = $tblReg->getAll();
        if (Jaws_Error::IsError($result)) {
            return null;
        }

        return $result;
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
        $params['component'] = $component;
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
        $params['now']       = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[registry]]
                ([component], [key_name], [key_value], [updatetime])
            VALUES
                ({component}, {key_name}, {key_value}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$component][$key_name] = $key_value;
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
                             "\n SELECT {component}, {name_$ndx}, {value_$ndx}, {now} FROM DUAL";
                    break;
                case 'ibase':
                    $sqls[] = " VALUES ({component}, {name_$ndx}, {value_$ndx}, {now})";
                    break;
                case 'pgsql':
                    if (version_compare($dbVersion, '8.2.0', '>=')) {
                        $sqls .= (empty($sqls)? "\n VALUES" : ",").
                                 "\n ({component}, {name_$ndx}, {value_$ndx}, {now})";
                    } else {
                        $sqls[] = " VALUES ({component}, {name_$ndx}, {value_$ndx}, {now})";
                    }
                    break;
                default:
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {component}, {name_$ndx}, {value_$ndx}, {now}";
                    break;
            }

            $this->_Registry[$component][$key_name] = $key_value;
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = '
                    INSERT INTO [[registry]]
                        ([component], [key_name], [key_value], [updatetime])
                    '. $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = '
                INSERT INTO [[registry]]
                    ([component], [key_name], [key_value], [updatetime])
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
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $key_value, $component = '')
    {
        $params = array();
        $params['key_name']  = $key_name;
        $params['key_value'] = $key_value;
        $params['component'] = $component;

        $sql = '
            UPDATE [[registry]] SET
                [key_value] = {key_value}
            WHERE
                [component] = {component}
              AND
                [key_name] = {key_name}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $this->_Registry[$component][$key_name] = $key_value;
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
                FROM [[registry]]
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

}