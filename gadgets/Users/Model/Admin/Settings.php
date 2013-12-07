<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the User gadget settings
     *
     * @access  public
     * @param   string  $method     Authentication method
     * @param   string  $anon       Anonymous users can auto-register
     * @param   string  $repetitive Anonymous can register by repetitive email
     * @param   string  $act        Activation type
     * @param   int     $group      Default group of anonymous registered user
     * @param   string  $recover    Users can recover their passwords
     * @param   string  $dashboard  Users can build their dashboard
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($method, $anon, $repetitive, $act, $group, $recover, $dashboard)
    {
        $res = true;
        if ($this->gadget->GetPermission('ManageAuthenticationMethod')) {
            $methods = $GLOBALS['app']->getAuthTypes();
            if ($methods !== false && in_array($method, $methods)) {
                $res = $this->gadget->registry->update('authtype', $method);
            }
        }
        $res = $res && $this->gadget->registry->update('anon_register', $anon);
        $res = $res && $this->gadget->registry->update('anon_repetitive_email', $repetitive);
        $res = $res && $this->gadget->registry->update('anon_activation', $act);
        $res = $res && $this->gadget->registry->update('anon_group', (int)$group);
        $res = $res && $this->gadget->registry->update('password_recovery', $recover);
        $res = $res && $this->gadget->registry->update('dashboard_building', $dashboard);
        if ($res) {
            return true;
        }

        return new Jaws_Error(_t('USERS_PROPERTIES_CANT_UPDATE'));
    }

}