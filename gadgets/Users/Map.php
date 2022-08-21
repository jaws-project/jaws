<?php
/**
 * Users URL maps
 *
 * @category   GadgetMaps
 * @package    Users
 */
$maps[] = array(
    'Login',
    'users/login[/authtype/{authtype}][/defaults/{defaults}][/referrer/{referrer}]',
    array(
        'defaults' => '[[:alnum:]\-_.@]+',
        'referrer' => '[[:alnum:]\-_.@]+',
    )
);
$maps[] = array('Authenticate', 'users/authenticate');
$maps[] = array(
    'Registration',
    'users/registration[/authtype/{authtype}][/defaults/{defaults}][/referrer/{referrer}]',
    array(
        'defaults' => '[[:alnum:]\-_.@]+',
        'referrer' => '[[:alnum:]\-_.@]+',
    )
);
$maps[] = array(
    'LoginForgot',
    'users/forget[/authtype/{authtype}][/defaults/{defaults}][/referrer/{referrer}]',
    array(
        'defaults' => '[[:alnum:]\-_.@]+',
        'referrer' => '[[:alnum:]\-_.@]+',
    )
);
$maps[] = array('Logout', 'users/logout');
$maps[] = array('Account', 'users/account');
$maps[] = array('Password', 'users/password');
$maps[] = array('Personal', 'users/personal');
$maps[] = array('UserAttributes', 'users/attributes/{gadget}');
$maps[] = array('Preferences', 'users/preferences');
$maps[] = array('Bookmarks', 'users/bookmarks');
$maps[] = array('Contact', 'users/contact');
$maps[] = array('Contacts', 'users/contacts');
$maps[] = array('ExportVCard', 'users/contacts/export');
$maps[] = array('ImportVCard', 'users/contacts/import[/restype/{restype}]');
$maps[] = array('Friends', 'users/friends');
$maps[] = array('FriendsGroups', 'users/friends/groups');
$maps[] = array('UserGroupUI', 'users/groups/new');
$maps[] = array('GroupAttributes', 'users/groups/attributes/{gadget}[/{group}]');
$maps[] = array('EditUserGroup', 'users/groups/{gid}/edit');
$maps[] = array('ManageGroup', 'users/groups/{gid}/manage');
$maps[] = array('ReplaceUserEmail', 'users/replace_email[/{key}]');
$maps[] = array(
    'Profile',
    'users/profile[/{user}]',
    array('user' => '[[:alnum:]\-_.@]+')
);
$maps[] = array(
    'Avatar',
    'users/avatar[/{user}]',
    array('user' => '[[:alnum:]\-_.@]+'),
    ''
);
$maps[] = array('Users', 'users');
$maps[] = array('Groups', 'groups');
