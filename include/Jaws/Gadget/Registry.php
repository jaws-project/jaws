<?php
/**
 * Jaws Gadget Registry
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Registry extends Jaws_Gadget_Class
{
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
        return $this->app->registry->insert($key, $value, $custom, $gadget);
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
        return $this->app->registry->insertAll($keys, $gadget, $user);
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
        return $this->insertAll($keys, $gadget, $this->app->session->user->id);
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
        return $this->app->registry->fetch($name, $gadget);
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
        return $this->app->registry->fetchAll($gadget, $onlyCustom);
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
        return $this->app->registry->fetchByUser(
            is_null($user)? $this->app->session->user->id : $user,
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
        return $this->app->registry->fetchAllByUser(
            $this->app->session->user->id,
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
        return $this->app->registry->update($name, $value, $custom, $gadget);
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
        return $this->app->registry->update(
            $name,
            $value,
            null,
            $gadget,
            is_null($user)? $this->app->session->user->id : $user
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
        return $this->app->registry->rename($old_name, $new_name, $custom, $gadget);
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
        return $this->app->registry->delete($gadget, $name);
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
        return $this->app->registry->deleteByUser(
            $this->app->session->user->id,
            $gadget
        );
    }

}