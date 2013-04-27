<?php
/**
 * Jaws Gadget Registry
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Registry
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
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Registry($gadget)
    {
        $this->name = $gadget;
    }

    /**
     * Add registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function add($name, $value = '', $gadget = '')
    {
        if (is_array($name)) {
            $gadget = empty($value)? $this->name : $value;
            return $GLOBALS['app']->Registry->NewKeyEx($name, $gadget, 1);
        } else {
            $gadget = empty($gadget)? $this->name : $gadget;
            return $GLOBALS['app']->Registry->NewKey($name, $value, $gadget, 1);
        }
    }

    /**
     * Get registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns key value if exists otherwise null
     */
    function get($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Get($name, $gadget);
    }

    /**
     * Set registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function set($name, $value, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Set($name, $value, $gadget);
    }

    /**
     * Delete registry key
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function del($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Delete($gadget, $name);
    }

}