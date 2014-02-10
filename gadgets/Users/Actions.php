<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
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
    'loglevel' => JAWS_NOTICE,
);
$actions['Registration'] = array(
    'normal' => true,
    'file' => 'Registration',
);
$actions['DoRegister'] = array(
    'normal' => true,
    'file' => 'Registration',
    'loglevel' => JAWS_NOTICE,
);
$actions['Registered'] = array(
    'normal' => true,
    'file' => 'Registration',
    'loglevel' => JAWS_NOTICE,
);
$actions['ActivateUser'] = array(
    'normal' => true,
    'file' => 'Registration',
    'temporary' => true,
    'loglevel' => JAWS_NOTICE,
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
    'loglevel' => JAWS_NOTICE,
);
$actions['Personal'] = array(
    'normal' => true,
    'file' => 'Personal',
);
$actions['UpdatePersonal'] = array(
    'standalone' => true,
    'file' => 'Personal',
    'loglevel' => JAWS_NOTICE,
);
$actions['Preferences'] = array(
    'normal' => true,
    'file' => 'Preferences',
);
$actions['UpdatePreferences'] = array(
    'standalone' => true,
    'file' => 'Preferences',
    'loglevel' => JAWS_NOTICE,
);
$actions['Contacts'] = array(
    'normal' => true,
    'file' => 'Contacts',
);
$actions['UpdateContacts'] = array(
    'standalone' => true,
    'file' => 'Contacts',
    'loglevel' => JAWS_NOTICE,
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
$actions['EditUserGroup'] = array(
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
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['MyAccount'] = array(
    'normal' => true,
    'file' => 'MyAccount',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['OnlineUsers'] = array(
    'normal' => true,
    'file' => 'OnlineUsers',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['LoadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
$admin_actions['UploadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
$admin_actions['GetUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetOnlineUsers'] = array(
    'standalone' => true,
    'file' => 'OnlineUsers',
);
$admin_actions['GetOnlineUsersCount'] = array(
    'standalone' => true,
    'file' => 'OnlineUsers',
);
$admin_actions['GetUsersCount'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['DeleteUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['DeleteSession'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['IPBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['AgentBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateUserACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateGroupACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['AddUserToGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['AddUsersToGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GetACLUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetACLKeys'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateMyAccount'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UserGroupsUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetUserGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PersonalUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PreferencesUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ContactsUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePersonal'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdatePreferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateContacts'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['GetGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['GroupUsersUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupUsers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
