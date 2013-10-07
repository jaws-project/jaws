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
            ->where('user', 0)
            ->and()
            ->openWhere('key_name', 'version')
            ->or()
            ->closeWhere('component', '')
            ->fetchAll('', JAWS_ERROR_NOTICE);
        if (Jaws_Error::IsError($result)) {
            if ($result->getCode() == MDB2_ERROR_NOSUCHFIELD) {
                // get 0.8.x jaws version
                $result = $tblReg->select('key_value')->where('key_name', '/version')->fetchOne();
                if (!Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            Jaws_Error::Fatal($result->getMessage());
        }

        foreach ($result as $regrec) {
            $this->_Registry[$regrec['component']][$regrec['key_name']] = $regrec['key_value'];
        }

        return isset($this->_Registry['']['version'])? $this->_Registry['']['version'] : null;
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
                ->where('user', 0)
                ->and()
                ->where('component', $component)
                ->and()
                ->where('key_name', $key_name)
                ->fetchOne();
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
        $tblReg->select('key_name', 'key_value')
            ->where('component', $component)
            ->and()
            ->where('user', 0);
        if (!empty($pattern)) {
            $tblReg->and()->where('key_name', $pattern, 'like');
        }

        $tblReg->orderBy('key_name');
        $result = $tblReg->fetchAll();
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
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->insert(array(
            'user'       => 0,
            'component'  => $component,
            'key_name'   => $key_name,
            'key_value'  => $key_value,
            'updatetime' => $GLOBALS['db']->Date(),
        ));
        $result = $tblReg->exec();
        if (!Jaws_Error::IsError($result)) {
            $this->_Registry[$component][$key_name] = $key_value;
        }

        return !Jaws_Error::IsError($result);
    }

    /**
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Pairs of keys/values
     * @param   string  $component  Component name
     * @param   int     $user       User ID
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $component = '', $user = 0)
    {
        if (empty($keys)) {
            return true;
        }

        $params = array();
        $params['user']      = (int)$user;
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
                             "\n SELECT {user}, {component}, {name_$ndx}, {value_$ndx}, {now} FROM DUAL";
                    break;
                case 'ibase':
                    $sqls[] = " VALUES ({user}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    break;
                case 'pgsql':
                    if (version_compare($dbVersion, '8.2.0', '>=')) {
                        $sqls .= (empty($sqls)? "\n VALUES" : ",").
                                 "\n ({user}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    } else {
                        $sqls[] = " VALUES ({user}, {component}, {name_$ndx}, {value_$ndx}, {now})";
                    }
                    break;
                default:
                    $sqls .= (empty($sqls)? '' : "\n UNION ALL").
                             "\n SELECT {user}, {component}, {name_$ndx}, {value_$ndx}, {now}";
                    break;
            }

            unset($this->_Registry[$component][$key_name]);
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                $qsql = '
                    INSERT INTO [[registry]]
                        ([user], [component], [key_name], [key_value], [updatetime])
                    '. $sql;
                $result = $GLOBALS['db']->query($qsql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        } else {
            $qsql = '
                INSERT INTO [[registry]]
                    ([user], [component], [key_name], [key_value], [updatetime])
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
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->update(array('key_value' => $key_value))
            ->where('user', 0)
            ->and()
            ->where('component', $component)
            ->and()
            ->where('key_name', $key_name);
        $result = $tblReg->exec();
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
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->delete()->where('component', $component);
        if (!empty($key_name)) {
            $tblReg->and()->where('key_name', $key_name);
        }
        $result = $tblReg->exec();
        if (!Jaws_Error::IsError($result)) {
            if (empty($key_name)) {
                unset($this->_Registry[$component]);
            } else {
                unset($this->_Registry[$component][$key_name]);
            }
        }

        return !Jaws_Error::IsError($result);
    }

    /**
     * Delete all registry keys related to the user
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $component  Component name
     * @return  bool    True if success otherwise False
     */
    function deleteByUser($user, $component = '')
    {
        $tblACL = Jaws_ORM::getInstance()->table('registry');
        $tblACL->delete()->where('user', (int)$user);
        if (!empty($component)) {
            $tblACL->and()->where('component', $component);
        }
        $result = $tblACL->exec();
        return !Jaws_Error::IsError($result);
    }

}