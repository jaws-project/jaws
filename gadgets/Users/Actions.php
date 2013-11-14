<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['LoginBox'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Login',
);
$actions['LoginLinks'] = array(
    'layout' => true,
    'file' => 'Login',
);
$actions['OnlineUsers'] = array(
    'layout' => true,
    'file' => 'Statistics',
);
$actions['OnlineStatistics'] = array(
    'layout' => true,
    'file' => 'Statistics',
);
$actions['LatestRegistered'] = array(
    'layout' => true,
    'file' => 'Statistics',
);
$actions['Profile'] = array(
    'normal' => true,
    'file' => 'Profile',
    'parametric' => true,
);
$actions['AboutUser'] = array(
    'layout' => true,
    'file' => 'Profile',
    'parametric' => true,
);
$actions['Login'] = array(
    'normal' => true,
    'file' => 'Login',
);
$actions['Logout'] = array(
    'normal' => true,
    'file' => 'Login',
);
$actions['ForgotLogin'] = array(
    'normal' => true,
    'file' => 'Login',
);
$actions['SendRecoverKey'] = array(
    'normal' => true,
    'file' => 'Login',
);
$actions['Registration'] = array(
    'normal' => true,
    'file' => 'Registration',
);
$actions['DoRegister'] = array(
    'normal' => true,
    'file' => 'Registration',
);
$actions['Registered'] = array(
    'normal' => true,
    'file' => 'Registration',
);
$actions['ActivateUser'] = array(
    'normal' => true,
    'file' => 'Registration',
);
$actions['Account'] = array(
    'normal' => true,
    'file' => 'Account',
);
$actions['ChangePassword'] = array(
    'normal' => true,
    'file' => 'Account',
);
$actions['UpdateAccount'] = array(
    'standalone' => true,
    'file' => 'Account',
);
$actions['Personal'] = array(
    'normal' => true,
    'file' => 'Personal',
);
$actions['UpdatePersonal'] = array(
    'standalone' => true,
    'file' => 'Personal',
);
$actions['Preferences'] = array(
    'normal' => true,
    'file' => 'Preferences',
);
$actions['UpdatePreferences'] = array(
    'standalone' => true,
    'file' => 'Preferences',
);
$actions['Contacts'] = array(
    'normal' => true,
    'file' => 'Contacts',
);
$actions['UpdateContacts'] = array(
    'standalone' => true,
    'file' => 'Contacts',
);
$actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$actions['DeleteGroups'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['AddUserToGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['RemoveUserFromGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['UserGroupUI'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$actions['AddGroup'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$actions['ManageGroup'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$actions['UpdateGroup'] = array(
    'normal' => true,
    'file' => 'Groups',
);

/**
 * Admin actions
 */
$admin_actions['Users'] = array(
    'normal' => true,
    'file' => 'Users',
);
$admin_actions['MyAccount'] = array(
    'normal' => true,
    'file' => 'MyAccount',
);
$admin_actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$admin_actions['OnlineUsers'] = array(
    'normal' => true,
    'file' => 'OnlineUsers',
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
$admin_actions['LoadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
$admin_actions['UploadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
