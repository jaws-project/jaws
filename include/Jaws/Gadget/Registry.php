<?php
/**
 * Jaws Gadget Registry
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2015 Jaws Development Group
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
     * @param   string  $key    Key name
     * @param   string  $value  Key value
     * @param   bool    $custom Customizable by user?
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function insert($key, $value, $custom = false, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->insert($key, $value, $custom, $gadget);
    }

    /**
     * Insert a registry key value
     *
     * @access  public
     * @param   array   $keys   Array of registry keys, values
     * @param   string  $gadget (Optional) Gadget name
     * @param   int     $user   (Optional) User ID
     * @return  mixed   Returns number of records inserted otherwise Jaws_Error
     */
    function insertAll($keys, $gadget = '', $user = 0)
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->insertAll($keys, $gadget, $user);
    }

    /**
     * Insert a registry key value
     *
     * @access  public
     * @param   array   $keys   Array of registry keys, values
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns number of records inserted otherwise Jaws_Error
     */
    function insertAllByUser($keys, $gadget = '')
    {
        return $this->insertAll($keys, $gadget, $GLOBALS['app']->Session->GetAttribute('user'));
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
     * @param   bool    $onlyCustom Only custom
     * @param   string  $gadget     (Optional) Gadget name
     * @return  mixed   Returns Array of keys/values if success otherwise null
     */
    function fetchAll($onlyCustom = false, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetchAll($gadget, $onlyCustom);
    }

    /**
     * Fetch user's registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @param   int     $user   User ID
     * @return  mixed   Returns key value if exists otherwise null
     */
    function fetchByUser($name, $gadget = '', $user = null)
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetchByUser(
            is_null($user)? $GLOBALS['app']->Session->GetAttribute('user') : $user,
            $name,
            $gadget
        );
    }

    /**
     * Fetch user's all registry keys/values of given gadget
     *
     * @access  public
     * @param   string  $gadget     (Optional) Gadget name
     * @return  mixed   Returns Array of keys/values if success otherwise null
     */
    function fetchAllByUser($gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->fetchAllByUser(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $gadget
        );
    }

    /**
     * Update registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   bool    $custom Customizable by user?
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function update($name, $value, $custom = null, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->update($name, $value, $custom, $gadget);
    }

    /**
     * Update registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @param   int     $user   User ID
     * @return  bool    Returns True or False
     */
    function updateByUser($name, $value, $gadget = '', $user = null)
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->update(
            $name,
            $value,
            null,
            $gadget,
            is_null($user)? $GLOBALS['app']->Session->GetAttribute('user') : $user
        );
    }

    /**
     * Renames a registry key
     *
     * @access  public
     * @param   string  $old_name   Old key name
     * @param   string  $new_name   New key name
     * @param   bool    $custom     Customizable by user?
     * @param   string  $gadget     (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function rename($old_name, $new_name, $custom = null, $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->rename($old_name, $new_name, $custom, $gadget);
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

    /**
     * Delete all registry keys related to the user
     *
     * @access  public
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    True if success otherwise False
     */
    function deleteByUser($gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->Registry->deleteByUser(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $gadget
        );
    }

}