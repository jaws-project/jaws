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
                array('referrer' => Jaws_Utils::getRequestURL(false))
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
     * @param   string    $action_selected    selected action
     * @param   array     $visible_actions    visible actions
     * @param   array     $action_params      action params
     * @return  string    XHTML template content
     */
    function MenuBar($action_selected, $visible_actions, $action_params = null)
    {
        $actions = array('EditGroup', 'AddGroup', 'Groups');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'Groups';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if (in_array('AddGroup', $visible_actions)) {
            $menubar->AddOption('AddGroup', _t('USERS_ADD_GROUP'),
                $this->gadget->urlMap('UserGroupUI'), STOCK_ADD);
        }

        if (in_array('EditGroup', $visible_actions)) {
            $menubar->AddOption('EditGroup', _t('USERS_EDIT_GROUP'),
                $this->gadget->urlMap('EditUserGroup', $action_params), STOCK_EDIT);
        }

        if (in_array('Groups', $visible_actions)) {
            $menubar->AddOption('Groups', _t('USERS_MANAGE_GROUPS'),
                $this->gadget->urlMap('Groups'), 'gadgets/Users/Resources/images/groups_mini.png');
        }

        $menubar->Activate($action_selected);

        return $menubar->Get();
    }
}