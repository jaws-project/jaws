<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Default extends Jaws_Gadget_Action
{
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
        $tpl = $this->gadget->template->load('NoPermission.html');
        $tpl->SetBlock('NoPermission');
        $tpl->SetVariable('nopermission', _t('USERS_NO_PERMISSION_TITLE'));
        $tpl->SetVariable('description', _t('USERS_NO_PERMISSION_DESC', $gadget, $action));
        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('site-name', $this->gadget->registry->fetch('site_name', 'Settings'));
        $tpl->SetVariable('site-slogan', $this->gadget->registry->fetch('site_slogan', 'Settings'));
        $tpl->SetVariable('BASE_URL', $GLOBALS['app']->GetSiteURL('/'));
        $tpl->SetVariable('.dir', _t('GLOBAL_LANG_DIRECTION') == 'rtl' ? '.rtl' : '');
        if ($GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('NoPermission/known');
            $logoutLink = $this->gadget->urlMap('Logout');
            $referLink  = empty($_SERVER['HTTP_REFERER'])?
                $GLOBALS['app']->getSiteURL('/') : Jaws_XSS::filter($_SERVER['HTTP_REFERER']);
            $tpl->SetVariable(
                'known_description',
                _t('USERS_NO_PERMISSION_KNOWN_DESC', $logoutLink, $referLink));
            $tpl->ParseBlock('NoPermission/known');
        } else {
            $tpl->SetBlock('NoPermission/anon');
            $loginLink = $this->gadget->urlMap(
                'LoginBox',
                array('referrer' => bin2hex(Jaws_Utils::getRequestURL(false)))
            );
            $referLink = empty($_SERVER['HTTP_REFERER'])?
                $GLOBALS['app']->getSiteURL('/') : Jaws_XSS::filter($_SERVER['HTTP_REFERER']);
            $tpl->SetVariable(
                'anon_description',
                _t('USERS_NO_PERMISSION_ANON_DESC', $loginLink, $referLink));
            $tpl->ParseBlock('NoPermission/anon');
        }
        $tpl->ParseBlock('NoPermission');
        return $tpl->Get();
    }

    /**
     * Displays menu bar according to selected action
     *
     * @access  public
     * @param   string  $selected   selected action
     * @return  string  XHTML template content
     */
    function MenuBar($selected)
    {
        $actions = array('Groups', 'Account', 'Personal', 'Preferences', 'Contacts');
        if (!in_array($selected, $actions)) {
            $selected = 'Account';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'Groups',
            _t('USERS_MANAGE_GROUPS'),
            $this->gadget->urlMap('Groups'),
            'gadgets/Users/Resources/images/groups_mini.png'
        );

        if ($this->gadget->GetPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', '', false)) {
            $menubar->AddOption('Account',
                _t('USERS_EDIT_ACCOUNT'),
                $this->gadget->urlMap('Account'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserPersonal')) {
            $menubar->AddOption('Personal',
                _t('USERS_EDIT_PERSONAL'),
                $this->gadget->urlMap('Personal'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserPreferences')) {
            $menubar->AddOption('Preferences',
                _t('USERS_EDIT_PREFERENCES'),
                $this->gadget->urlMap('Preferences'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserContacts')) {
            $menubar->AddOption('Contacts',
                _t('USERS_EDIT_CONTACTS'),
                $this->gadget->urlMap('Contacts'),
                STOCK_EDIT);
        }

        $menubar->Activate($selected);
        return $menubar->Get();
    }
}