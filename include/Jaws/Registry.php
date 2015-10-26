<?php
/**
 * Class to manage jaws registry
 *
 * @category   Registry
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Registry
{
    /**
     * All default registry keys
     *
     * @var     array
     * @access  private
     */
    private $regkeys = array();

    /**
     * All default registry keys custom attribute
     *
     * @var     array
     * @access  private
     */
    private $customs = array();

    /**
     * Loads the data from the DB
     *
     * @access  public
     * @return  string  Jaws version
     */
    function Init()
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->select('component', 'key_name', 'key_value', 'custom:boolean');
        $result = $tblReg->where('user', 0)->fetchAll('', JAWS_ERROR_NOTICE);
        if (Jaws_Error::IsError($result)) {
            if ($result->getCode() == MDB2_ERROR_NOSUCHFIELD) {
                // get 0.9.x jaws version
                $result = $tblReg->select('key_value')
                    ->where('key_name', 'version')
                    ->and()
                    ->where('component', '')
                    ->fetchOne();
                if (!Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            Jaws_Error::Fatal($result->getMessage());
        }

        foreach ($result as $regrec) {
            $this->regkeys[$regrec['component']][$regrec['key_name']] = $regrec['key_value'];
            if ($regrec['custom']) {
                $this->customs[$regrec['component']][$regrec['key_name']] = $regrec['key_value'];
            }
        }

        return @$this->regkeys['']['version'];
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
        return @$this->regkeys[$component][$key_name];
    }

    /**
     * Fetch all registry keys of the gadget
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   bool    $onlyCustom Only custom
     * @return  mixed   Array of keys if successful or Jaws_Error on failure
     */
    function fetchAll($component = '', $onlyCustom = false)
    {
        return $onlyCustom? @$this->customs[$component] : @$this->regkeys[$component];
    }

    /**
     * Fetch user's registry key value
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $key_name   Key name
     * @param   string  $component  Component name
     * @return  mixed   User's value of the key if success otherwise default key value
     */
    function fetchByUser($user, $key_name, $component = '')
    {
        $value = $this->fetch($key_name, $component);
        if (isset($this->customs[$component][$key_name])) {
            $tblReg = Jaws_ORM::getInstance()->table('registry');
            $uvalue = $tblReg->select('key_value')
                ->where('user', (int)$user)
                ->and()
                ->where('component', $component)
                ->and()
                ->where('key_name', $key_name)
                ->fetchOne();
            if (!Jaws_Error::IsError($uvalue) && !is_null($uvalue)) {
                return $uvalue;
            }
        }

        return $value;
    }

    /**
     * Fetch all user's registry keys of a gadget
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $component  Component name
     * @return  mixed   Array of keys if successful or Jaws_Error on failure
     */
    function fetchAllByUser($user, $component = '')
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $result = $tblReg->select('key_name', 'key_value')
            ->where('component', $component)
            ->and()
            ->where('user', (int)$user)
            ->orderBy('key_name')
            ->fetchAll();
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
     * @param   bool    $custom     Customizable by user?
     * @param   string  $component  Component name
     * @param   int     $user       User ID
     * @return  bool    True is set otherwise False
     */
    function insert($key_name, $key_value, $custom = false, $component = '', $user = 0)
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->insert(array(
            'user'       => (int)$user,
            'component'  => $component,
            'key_name'   => $key_name,
            'key_value'  => $key_value,
            'custom'     => (bool)$custom,
            'updatetime' => Jaws_DB::getInstance()->date(),
        ));
        $result = $tblReg->exec();
        if (!Jaws_Error::IsError($result)) {
            $this->regkeys[$component][$key_name] = $key_value;
            if ($custom) {
                $this->customs[$component][$key_name] = $key_value;
            }
        }

        return !Jaws_Error::IsError($result);
    }

    /**
     * Inserts array of keys
     *
     * @access  public
     * @param   array   $keys       Array of keys, values
     * @param   string  $component  Component name
     * @param   int     $user       User ID
     * @return  bool    True is set otherwise False
     */
    function insertAll($keys, $component = '', $user = 0)
    {
        if (empty($keys)) {
            return true;
        }

        $data = array();
        $user = (int)$user;
        $time = Jaws_DB::getInstance()->date();
        $tmp_regkeys = $this->regkeys;
        $tmp_customs = $this->customs;
        $columns = array('user', 'component', 'key_name', 'key_value', 'custom', 'updatetime');
        foreach ($keys  as $key) {
            @list($key_name, $key_value, $custom) = $key;
            $tmp_regkeys[$component][$key_name] = $key_value;
            if ($custom) {
                $tmp_customs[$component][$key_name] = $key_value;
            }
            $data[] = array($user, $component, $key_name, $key_value, (bool)$custom, $time);
        }

        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $result  = $tblReg->insertAll($columns, $data)->exec();
        if (!Jaws_Error::IsError($result)) {
            $this->regkeys = $tmp_regkeys;
            $this->customs = $tmp_customs;
        }

        return $result;
    }

    /**
     * Updates value of a key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   bool    $custom     Customizable by user?
     * @param   string  $component  Component name
     * @param   int     $user       User ID
     * @return  bool    True is set otherwise False
     */
    function update($key_name, $key_value, $custom = null, $component = '', $user = 0)
    {
        $data = array();
        if (!is_null($key_value)) {
            $data['key_value'] = $key_value;
        }
        if (!is_null($custom)) {
            $data['custom'] = (bool)$custom;
        }

        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->update($data)
            ->where('user', (int)$user)
            ->and()
            ->where('component', $component)
            ->and()
            ->where('key_name', $key_name);
        $result = $tblReg->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // if nothing updated then try to insert a new record
        if (!empty($user) && empty($result)) {
            return $this->insert($key_name, $key_value, (bool)$custom, $component, $user);
        }

        // update registry cache array
        if (empty($user)) {
            $this->regkeys[$component][$key_name] = $key_value;
            if ($custom) {
                $this->customs[$component][$key_name] = $key_value;
            } else {
                unset($this->customs[$component][$key_name]);
            }
        }

        return true;
    }

    /**
     * Renames a key
     *
     * @access  public
     * @param   string  $old_name   Old key name
     * @param   string  $new_name   New key name
     * @param   bool    $custom     Customizable by user?
     * @param   string  $component  Component name
     * @return  bool    True is set otherwise False
     */
    function rename($old_name, $new_name, $custom = null, $component = '')
    {
        $data['key_name'] =  $new_name;
        if (!is_null($custom)) {
            $data['custom'] = (bool)$custom;
        }
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->update($data);
        $tblReg->where('component', $component)->and()->where('key_name', $old_name);
        $result = $tblReg->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        // update internal cache
        $key_value = null;
        if (isset($this->regkeys[$component][$old_name])) {
            $key_value = $this->regkeys[$component][$old_name];
            $this->regkeys[$component][$new_name] = $this->regkeys[$component][$old_name];
            unset($this->regkeys[$component][$old_name]);
        }

        // update internal custom cache array
        if (isset($this->customs[$component][$old_name])) {
            if ($custom !== false) {
                $this->customs[$component][$new_name] = $this->customs[$component][$old_name];
            }
            unset($this->customs[$component][$old_name]);
        } elseif ($custom) {
            $this->customs[$component][$new_name] = $key_value;
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
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $tblReg->delete()->where('component', $component);
        if (!empty($key_name)) {
            $tblReg->and()->where('key_name', $key_name);
        }
        $result = $tblReg->exec();
        if (!Jaws_Error::IsError($result)) {
            if (empty($key_name)) {
                unset($this->regkeys[$component]);
                unset($this->customs[$component]);
            } else {
                unset($this->regkeys[$component][$key_name]);
                unset($this->customs[$component][$key_name]);
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