<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
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
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($method, $anon, $repetitive, $act, $group, $recover)
    {
        $method     = $method;
        $anon       = $anon;
        $repetitive = $repetitive;
        $recover    = $recover;

        $res = true;
        if ($GLOBALS['app']->Session->GetPermission('Users', 'ManageAuthenticationMethod')) {
            $methods = Jaws::getAuthMethods();
            if ($methods !== false && in_array($method, $methods)) {
                $res = $this->gadget->SetRegistry('auth_method', $method);
            }
        }
        $res = $res && $this->gadget->SetRegistry('anon_register', $anon);
        $res = $res && $this->gadget->SetRegistry('anon_repetitive_email', $repetitive);
        $res = $res && $this->gadget->SetRegistry('anon_activation', $act);
        $res = $res && $this->gadget->SetRegistry('anon_group', (int)$group);
        $res = $res && $this->gadget->SetRegistry('password_recovery', $recover);
        if ($res) {
            return true;
        }

        return new Jaws_Error(_t('USERS_PROPERTIES_CANT_UPDATE'), _t('USERS_NAME'));
    }

}