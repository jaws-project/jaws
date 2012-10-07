<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UsersHTML extends Jaws_GadgetHTML
{
    /**
     * Default Action
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
     */
    function DefaultAction()
    {
        $userHTML = $GLOBALS['app']->LoadGadget('Users', 'HTML', 'Login');
        return $userHTML->LoginBox();
    }

    /**
     * Prepares the NoPermission HTML template
     *
     * @access  public
     * @param   string  $user    Username
     * @param   string  $gadget  Gadget user is requesting
     * @param   string  $action  The 'denied' action
     * @return  string  HTML template
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
        $tpl->SetVariable('site-name', $GLOBALS['app']->Registry->Get('/config/site_name'));
        $tpl->SetVariable('site-slogan', $GLOBALS['app']->Registry->Get('/config/site_slogan'));
        $tpl->SetVariable('BASE_URL', $GLOBALS['app']->GetSiteURL('/'));
        $tpl->SetVariable('.dir', _t('GLOBAL_LANG_DIRECTION') == 'rtl' ? '.' . _t('GLOBAL_LANG_DIRECTION') : '');
        if (!$GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('NoPermission/anonymous');
            $loginLink = $GLOBALS['app']->Map->GetURLFor(
                                    'Users',
                                    'LoginBox',
                                    array('referrer'  => Jaws_Utils::getRequestURL(false)));
            $tpl->SetVariable('anon_description', _t('USERS_NO_PERMISSION_ANON_DESC', $loginLink));
            $tpl->ParseBlock('NoPermission/anonymous');
        }
        $tpl->ParseBlock('NoPermission');
        return $tpl->Get();
    }

}