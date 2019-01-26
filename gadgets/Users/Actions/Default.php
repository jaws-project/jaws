<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
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
                'Login',
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
        $actions = array('Profile', 'FriendsGroups');
        if (!in_array($selected, $actions)) {
            $selected = 'Profile';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Profile',
            _t('USERS_PROFILE'),
            $this->gadget->urlMap('Profile', array('user' => $GLOBALS['app']->Session->GetAttribute('username'))),
            STOCK_ABOUT
        );

        if ($this->gadget->GetPermission('ManageFriends')) {
            $menubar->AddOption(
                'FriendsGroups',
                _t('USERS_FRIENDS'),
                $this->gadget->urlMap('FriendsGroups'),
                'gadgets/Users/Resources/images/groups_mini.png'
            );
        }

        $menubar->Activate($selected);
        return $menubar->Get();
    }

    /**
     * Displays sub-menu bar according to selected action
     *
     * @access  public
     * @param   string  $selected   selected action
     * @param   array   $actions    visible actions
     * @param   array   $params     action params
     * @return  string  XHTML template content
     */
    function SubMenuBar($selected, $actions, $params = null)
    {
        $default_actions = array(
            'Profile', 'Friends', 'EditGroup', 'AddGroup', 'Account',
            'Personal', 'Preferences', 'Bookmarks', 'Contact', 'Contacts'
        );
        if (!in_array($selected, $default_actions)) {
            $action_selected = 'Profile';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->setClass('gadget_submenubar');
        if (in_array('Profile', $actions)) {
            $menubar->AddOption('Profile',
                _t('USERS_PROFILE'),
                $this->gadget->urlMap(
                    'Profile',
                    array('user' => $GLOBALS['app']->Session->GetAttribute('username'))
                )
            );
        }

        if (in_array('FriendsGroups', $actions)) {
            $menubar->AddOption(
                'FriendsGroups',
                _t('USERS_USER_GROUPS'),
                $this->gadget->urlMap('FriendsGroups')
            );
        }

        if (in_array('AddFriendsGroup', $actions)) {
            $menubar->AddOption('AddFriendsGroup', _t('USERS_ADD_GROUP'),
                $this->gadget->urlMap('FriendsGroupUI'), STOCK_ADD);
        }

        if (in_array('Friends', $actions)) {
            $menubar->AddOption(
                'Friends',
                _t('USERS_GROUPS_MEMBERS'),
                $this->gadget->urlMap('ManageFriendsGroup', $params)
            );
        }

        if (in_array('EditGroup', $actions)) {
            $menubar->AddOption(
                'EditFriendsGroup',
                _t('USERS_EDIT_GROUP'),
                $this->gadget->urlMap('EditFriendsGroup', $params)
            );
        }

        if (in_array('Account', $actions)) {
            if ($this->gadget->GetPermission(
                'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword',
                '',
                false
            )) {
                $menubar->AddOption(
                    'Account',
                    _t('USERS_EDIT_ACCOUNT'),
                    $this->gadget->urlMap('Account')
                );
            }
        }

        if (in_array('Personal', $actions)) {
            if ($this->gadget->GetPermission('EditUserPersonal')) {
                $menubar->AddOption(
                    'Personal',
                    _t('USERS_EDIT_PERSONAL'),
                    $this->gadget->urlMap('Personal')
                );
            }
        }

        if (in_array('Preferences', $actions)) {
            if ($this->gadget->GetPermission('EditUserPreferences')) {
                $menubar->AddOption(
                    'Preferences',
                    _t('USERS_EDIT_PREFERENCES'),
                    $this->gadget->urlMap('Preferences')
                );
            }
        }

        if (in_array('Bookmarks', $actions)) {
            if ($this->gadget->GetPermission('EditUserBookmarks')) {
                $menubar->AddOption(
                    'Bookmarks',
                    _t('USERS_EDIT_BOOKMARKS'),
                    $this->gadget->urlMap('Bookmarks')
                );
            }
        }

        if (in_array('Contact', $actions)) {
            if ($this->gadget->GetPermission('EditUserContacts')) {
                $menubar->AddOption(
                    'Contact',
                    _t('USERS_EDIT_CONTACT'),
                    $this->gadget->urlMap('Contact')
                );
            }
        }

        if (in_array('Contacts', $actions)) {
            if ($this->gadget->GetPermission('EditUserContacts')) {
                $menubar->AddOption(
                    'Contacts',
                    _t('USERS_EDIT_CONTACTS'),
                    $this->gadget->urlMap('Contacts')
                );
            }
        }

        $menubar->Activate($selected);
        return $menubar->Get();
    }

}