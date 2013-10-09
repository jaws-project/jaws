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
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object  $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Registry($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Insert a registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function insert($name, $value = '', $gadget = '')
    {
        if (is_array($name)) {
            $gadget = empty($value)? $this->gadget->name : $value;
            return $GLOBALS['app']->Registry->insertAll($name, $gadget);
        } else {
            $gadget = empty($gadget)? $this->gadget->name : $gadget;
            return $GLOBALS['app']->Registry->insert($name, $value, $gadget);
        }
    }

    /**
     * Fetch registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns key value if exists otherwise null
     */
    function fetch($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetch($name, $gadget);
    }

    /**
     * Fetch all registry keys/values of given gadget
     *
     * @access  public
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns keys/values if success otherwise null
     */
    function fetchAll($gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetchAll($gadget);
    }

    /**
     * Fetch user's registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns key value if exists otherwise null
     */
    function fetchByUser($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetchByUser(
            $GLOBALS['app']->Session->GetAttribute('layout'),
            $name,
            $gadget
        );
    }

    /**
     * Update registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function update($name, $value, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->update($name, $value, $gadget);
    }

    /**
     * Delete registry key
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function delete($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->delete($gadget, $name);
    }

}