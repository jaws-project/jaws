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
    function Jaws_Gadget_ACL($gadget)
    {
        $this->gadget = $gadget;
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
            $gadget = empty($value)? $this->gadget->name : $value;
            return $GLOBALS['app']->ACL->insertAll($name, $gadget);
        } else {
            $gadget = empty($gadget)? $this->gadget->name : $gadget;
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
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
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
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->ACL->update($name, $subkey, $value, $gadget);
    }

    /**
     * Delete ACL key
     *
     * @access  public
     * @param   string  $key_name   Key name
     * @param   string  $subkey     Subkey name
     * @param   string  $gadget     (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function delete($key_name, $subkey = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        return $GLOBALS['app']->ACL->delete($gadget, $key_name, $subkey);
    }

    /**
     * Gets the short description of a given ACL key
     *
     * @access  public
     * @param   string $key     ACL key name
     * @param   int    $subkey  ACL sub-key
     * @return  string The ACL description
     */
    function description($key, $subkey = 0)
    {
        if (in_array($key, array('default', 'default_admin', 'default_registry'))) {
            return _t(strtoupper('GLOBAL_ACL_'. $key));
        } elseif (empty($subkey)) {
            return _t(strtoupper($this->gadget->name. '_ACL_'. $key));
        } else {
            static $alreadyLoaded;
            $acl_key_name = strtoupper($this->gadget->name. '_ACL_'. $key. '_'. $subkey);
            if (!isset($alreadyLoaded)) {
                $alreadyLoaded = true;
                // load ACL hook for get dynamic ACL names
                $objHook = $this->gadget->hook->load('ACL');
                if (!Jaws_Error::IsError($objHook)) {
                    $objHook->Execute();
                }
            }

            return _t($acl_key_name);
        }
    }

}