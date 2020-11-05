<?php
/**
 * Users - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Users
 */
class Users_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Login'),
            'title' => $this::t('LOGIN_TITLE'),
            'status' => Menu_Info::STATUS_ANONYMOUS
        );

        $urls[] = array(
            'url'   => $this->gadget->urlMap('Registration'),
            'title' => $this::t('REGISTER'),
            'status' => Menu_Info::STATUS_ANONYMOUS
        );

        $urls[] = array(
            'url' => array(
                'action' => 'Profile',
                'params' => array('user' => '{user}')
            ),
            'title' => $this::t('PROFILE'),
            'variables' => array(
                'user'  => array(
                    'scope'  => SESSION_SCOPE_USER,
                    'name'   => 'username'
                )
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Account'),
            'title' => $this::t('ACTIONS_ACCOUNT_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserName,EditUserNickname,EditUserEmail,EditUserMobile',
                'together' => false,
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Password'),
            'title' => $this::t('ACTIONS_PASSWORD_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserPassword',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Personal'),
            'title' => $this::t('ACTIONS_PERSONAL_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserPersonal',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Preferences'),
            'title' => $this::t('ACTIONS_PREFERENCES_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserPreferences',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Bookmarks'),
            'title' => $this::t('ACTIONS_BOOKMARKS_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserBookmarks',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Contact'),
            'title' => $this::t('ACTIONS_CONTACT_TITLE'),
            'permission'  => array(
                'key'     => 'EditUserContact',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url'        => $this->gadget->urlMap('FriendsGroups'),
            'title'      => $this::t('FRIENDS'),
            'permission' => array(
                'key'    => 'ManageFriends',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Users'),
            'title' => $this::t('ACTIONS_USERS_TITLE'),
            'permission'  => array(
                'key'     => 'ManageUsers',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url' => $this->gadget->urlMap('Groups'),
            'title' => $this::t('ACTIONS_GROUPS_TITLE'),
            'permission'  => array(
                'key'     => 'ManageGroups',
            ),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 2)),
            'title'      => $this::t('DASHBOARD_USER'),
            'permission' => array(
                'key'    => 'AccessUserLayout',
            )
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 1)),
            'title'      => $this::t('DASHBOARD_USERS'),
            'permission' => array(
                'key'    => 'AccessUsersLayout',
            )
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 0)),
            'title'      => $this::t('DASHBOARD_GLOBAL'),
        );

        $urls[] = array(
            'url'    => $this->gadget->urlMap('Logout'),
            'title'  => Jaws::t('LOGOUT'),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );
        return $urls;
    }

}