<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls LoginBox function if no action is specified
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        $userHTML = $GLOBALS['app']->LoadGadget('Users', 'HTML', 'Login');
        return $userHTML->LoginBox();
    }

    /**
     * Builds the NoPermission UI
     *
     * @access  public
     * @param   string  $user    Username
     * @param   string  $gadget  The Gadget user is requesting
     * @param   string  $action  The 'denied' action
     * @return  string  XHTML content
     */
    function ShowNoPermission($user, $gadget, $action)
    {
        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('NoPermission.html');
        $tpl->SetBlock('NoPermission');
        $tpl->SetVariable('nopermission', _t('USERS_NO_PERMISSION_TITLE'));
        $tpl->SetVariable('description', _t('USERS_NO_PERMISSION_DESC', $gadget, $action));
        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('site-name', $this->gadget->GetRegistry('site_name', 'Settings'));
        $tpl->SetVariable('site-slogan', $this->gadget->GetRegistry('site_slogan', 'Settings'));
        $tpl->SetVariable('BASE_URL', $GLOBALS['app']->GetSiteURL('/'));
        $tpl->SetVariable('.dir', _t('GLOBAL_LANG_DIRECTION') == 'rtl' ? '.' . _t('GLOBAL_LANG_DIRECTION') : '');
        if (!$GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('NoPermission/anonymous');
            $loginLink = $this->gadget->GetURLFor('LoginBox', array('referrer' => Jaws_Utils::getRequestURL(false)));
            $tpl->SetVariable('anon_description', _t('USERS_NO_PERMISSION_ANON_DESC', $loginLink));
            $tpl->ParseBlock('NoPermission/anonymous');
        }
        $tpl->ParseBlock('NoPermission');
        return $tpl->Get();
    }

}