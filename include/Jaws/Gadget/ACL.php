<?php
/**
 * Jaws Gadget ACL
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_ACL
{
    /**
     * Name of the gadget
     *
     * @var     string
     * @access  private
     */
    var $name = '';

    /**
     * constructor
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  void
     */
    function Jaws_Gadget_ACL($gadget)
    {
        $this->name = $gadget;
    }

    /**
     * Insert a ACL key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $subkey Subkey name
     * @param   int     $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function insert($name, $subkey = '', $value = 0, $gadget = '')
    {
        if (is_array($name)) {
            $gadget = empty($value)? $this->name : $value;
            return $GLOBALS['app']->ACL->insertAll($name, $gadget);
        } else {
            $gadget = empty($gadget)? $this->name : $gadget;
            return $GLOBALS['app']->ACL->insert($name, $subkey, $value, $gadget);
        }
    }

    /**
     * Fetch ACL key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $subkey Subkey name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns key value if exists otherwise null
     */
    function fetch($name, $subkey = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->ACL->fetch($name, $subkey, $gadget);
    }

    /**
     * Update ACL key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $subkey Subkey name
     * @param   int     $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function update($name, $subkey = '', $value = 0, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->ACL->update($name, $subkey, $value, $gadget);
    }

    /**
     * Delete ACL key
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function delete($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->ACL->delete($gadget, $name);
    }

    /**
     * Gets the short description of a given ACL key
     *
     * @access  public
     * @param   string $key  ACL Key
     * @return  string The ACL description
     */
    function description($key)
    {
        if (in_array($key, array('default', 'default_admin', 'default_registry'))) {
            return _t(strtoupper('GLOBAL_ACL_'. $key));
        } else {
            return _t(strtoupper($this->name. '_ACL_'. $key));
        }
    }

}