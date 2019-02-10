<?php
/**
 * Users URL maps
 *
 * @category   GadgetMaps
 * @package    Users
 */
$maps[] = array(
    'Login',
    'users/login[/authtype/{authtype}][/referrer/{referrer}]',
    array('referrer' => '.*')
);
$maps[] = array('Authenticate', 'users/authenticate');
$maps[] = array(
    'Registration',
    'users/registration[/authtype/{authtype}]'
);
$maps[] = array('Logout', 'users/logout');
$maps[] = array('Account', 'users/account');
$maps[] = array('Personal', 'users/personal');
$maps[] = array('Preferences', 'users/preferences');
$maps[] = array('Bookmarks', 'users/bookmarks');
$maps[] = array('Contact', 'users/contact');
$maps[] = array('Contacts', 'users/contacts');
$maps[] = array('ExportVCard', 'users/contacts/export');
$maps[] = array('Friends', 'users/{uid}/friends');
$maps[] = array('FriendsGroups', 'users/{uid}/friends/groups');
$maps[] = array('UserGroupUI', 'users/groups/new');
$maps[] = array('EditUserGroup', 'users/groups/{gid}/edit');
$maps[] = array('ManageGroup', 'users/groups/{gid}/manage');
$maps[] = array('LoginForgot', 'users/forget');
$maps[] = array('ReplaceUserEmail', 'users/replace_email[/{key}]');
$maps[] = array(
    'Profile',
    'users/{user}',
    array('user' => '[[:alnum:]\-_.@]+')
);
$maps[] = array('Users', 'users');
$maps[] = array('Groups', 'groups');
