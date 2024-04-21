<?php
/**
 * Forums - Files hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lgpl.html
 */
class Forums_Hooks_Files extends Jaws_Gadget_Hook
{
    /**
     * Check access permission
     *
     * @access  public
     * @param   string  $interface  Gadget interface(gadget, action, reference, ...)
     * @return  bool    True if allowed otherwise False
     */
    function Execute($interface = array())
    {
        return true;
    }

}