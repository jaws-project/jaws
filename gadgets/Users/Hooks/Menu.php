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
            'url'   => $this->gadget->urlMap('LoginBox'),
            'title' => _t('USERS_LOGIN_TITLE'),
            'status' => Menu_Info::STATUS_ANONYMOUS
        );

/*
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Profile', array('user' => $uInfo['username'])),
            'title' => _t('USERS_PROFILE')
        );
*/

        $urls[] = array(
            'url'        => $this->gadget->urlMap('FriendsGroups'),
            'title'      => _t('USERS_FRIENDS'),
            'permission' => array(
                'key'    => 'ManageFriends',
                'subkey' => ''
            )
        );

/*
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Dashboard', array('user' => $logged_user), false, 'Layout'),
            'title' => _t('USERS_DASHBOARD_USER')
        );
*/
        $urls[] = array(
            'url'        => $this->gadget->urlMap('Dashboard', array('user' => 0), false, 'Layout'),
            'title'      => _t('USERS_DASHBOARD_GLOBAL'),
            'permission' => array(
                'key'    => 'AccessDashboard',
                'subkey' => ''
            )
        );

        $urls[] = array(
            'url'    => $this->gadget->urlMap('Logout'),
            'title'  => _t('GLOBAL_LOGOUT'),
            'status' => Menu_Info::STATUS_LOGGED_IN,
        );
        return $urls;
    }

}