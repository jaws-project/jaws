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
            'title' => _t('USERS_LOGIN_TITLE'),
            'status' => Menu_Info::STATUS_ANONYMOUS
        );

        $urls[] = array(
            'url'   => $this->gadget->urlMap('Registration'),
            'title' => _t('USERS_REGISTER'),
            'status' => Menu_Info::STATUS_ANONYMOUS
        );

        $urls[] = array(
            'url' => array(
                'action' => 'Profile',
                'params' => array('user' => '{user}')
            ),
            'title' => _t('USERS_PROFILE'),
            'variables' => array(
                'user'  => array(
                    'scope'  => SESSION_SCOPE_USER,
                    'name'   => 'username'
                )
            )
        );

        $urls[] = array(
            'url'        => $this->gadget->urlMap('FriendsGroups'),
            'title'      => _t('USERS_FRIENDS'),
            'permission' => array(
                'key'    => 'ManageFriends',
                'subkey' => ''
            )
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 2)),
            'title'      => _t('USERS_DASHBOARD_USER'),
            'permission' => array(
                'key'    => 'AccessUserLayout',
                'subkey' => ''
            )
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 1)),
            'title'      => _t('USERS_DASHBOARD_USERS'),
            'permission' => array(
                'key'    => 'AccessUsersLayout',
                'subkey' => ''
            )
        );

        $urls[] = array(
            'url'        => Jaws_Gadget::getInstance('Layout')->urlMap('LayoutType', array('type' => 0)),
            'title'      => _t('USERS_DASHBOARD_GLOBAL'),
        );

        $urls[] = array(
            'url'    => $this->gadget->urlMap('Logout'),
            'title'  => _t('GLOBAL_LOGOUT'),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );
        return $urls;
    }

}