<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 */

/**
 * Index actions
 */
$actions['LoginBox'] = array(
    'normal' => true,
    'layout' => true,
    'global' => true,
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
    'navigation' => array(
        'params' => array(
            'user' => $GLOBALS['app']->Session->GetAttribute('username')
        ),
        'order' => 0
    ),
);
$actions['AboutUser'] = array(
    'layout' => true,
    'file' => 'Profile',
    'parametric' => true,
);
$actions['Login'] = array(
    'normal' => true,
    'global' => true,
    'file' => 'Login',
);
$actions['Logout'] = array(
    'normal' => true,
    'internal' => true,
    'file' => 'Login',
);
$actions['LoginForgot'] = array(
    'normal' => true,
    'global' => true,
    'file' => 'Login',
);
$actions['LoginRecovery'] = array(
    'standalone' => true,
    'global' => true,
    'file' => 'Login',
);
$actions['Registration'] = array(
    'normal' => true,
    'global' => true,
    'file' => 'Registration',
);
$actions['DoRegister'] = array(
    'normal' => true,
    'global' => true,
    'file' => 'Registration',
);
$actions['ReplaceUserEmail'] = array(
    'normal' => true,
    'file' => 'Registration',
    'temporary' => true,
);
$actions['Account'] = array(
    'normal' => true,
    'file' => 'Account',
    'acls' => array(
        'EditUserName,EditUserNickname,EditUserEmail,EditUserPassword',
        '',
        false
    ),
    'navigation' => array(
        'order' => 1
    ),
);
$actions['UpdateAccount'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Account',
    'loglevel' => JAWS_NOTICE,
);
$actions['Personal'] = array(
    'normal' => true,
    'file' => 'Personal',
    'acls' => array('EditUserPersonal'),
    'navigation' => array(
        'order' => 2
    ),
);
$actions['UpdatePersonal'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Personal',
);
$actions['Preferences'] = array(
    'normal' => true,
    'file' => 'Preferences',
    'acls' => array('EditUserPreferences'),
    'navigation' => array(
        'order' => 3
    ),
);
$actions['UpdatePreferences'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Preferences',
);
$actions['Contact'] = array(
    'normal' => true,
    'file' => 'Contacts',
    'acls' => array('EditUserContact'),
    'navigation' => array(
        'order' => 4
    ),
);
$actions['UpdateContact'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Contacts',
);
$actions['GetContact'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Contacts',
);
$actions['SaveContact'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Contacts',
);
$actions['DeleteContacts'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Contacts',
);
$actions['Bookmarks'] = array(
    'normal' => true,
    'file'   => 'Bookmarks',
    'acls' => array('EditUserBookmarks'),
    'navigation' => array(
        'separator' => true,
        'order' => 5
    ),
);
$actions['GetBookmarks'] = array(
    'standalone' => true,
    'file'   => 'Bookmarks'
);
$actions['GetBookmark'] = array(
    'standalone' => true,
    'file'   => 'Bookmarks'
);
$actions['BookmarkUI'] = array(
    'standalone' => true,
    'file'   => 'Bookmarks'
);
$actions['UpdateBookmark'] = array(
    'standalone' => true,
    'file'   => 'Bookmarks'
);
$actions['DeleteBookmark'] = array(
    'standalone' => true,
    'file'   => 'Bookmarks'
);
$actions['Contacts'] = array(
    'normal' => true,
    'file' => 'Contacts',
    'acls' => array('EditUserContacts'),
    'navigation' => array(
        'order' => 6
    ),
);
$actions['GetContacts'] = array(
    'standalone' => true,
    'file' => 'Contacts',
);

$actions['FriendsGroups'] = array(
    'normal' => true,
    'file' => 'Friends',
    'acls' => array('ManageFriends'),
    'navigation' => array(
        'separator' => true,
        'order' => 7
    ),
);
$actions['GetFriendGroups'] = array(
    'standalone' => true,
    'file' => 'Friends',
);
$actions['SaveFriendGroup'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Friends',
);
$actions['AddUsersToFriendGroup'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Friends',
);
$actions['GetFriendGroup'] = array(
    'standalone' => true,
    'file' => 'Friends',
);

$actions['DeleteFriendGroups'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Friends',
);
$actions['DeleteFriend'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Friends',
);
$actions['FriendsGroupUI'] = array(
    'normal' => true,
    'file' => 'Friends',
);
$actions['EditFriendsGroup'] = array(
    'normal' => true,
    'file' => 'Friends',
);
$actions['AddFriendsGroup'] = array(
    'normal' => true,
    'internal' => true,
    'file' => 'Friends',
);

$actions['UpdateFriendsGroup'] = array(
    'normal' => true,
    'internal' => true,
    'file' => 'Friends',
);

$actions['Users'] = array(
    'normal' => true,
    'file' => 'Users',
    'acls' => array('ManageUsers'),
    'navigation' => array(
        'separator' => true,
        'order' => 8
    ),
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Users',
);
$actions['GetUser'] = array(
    'standalone' => true,
    'file' => 'Users',
);
$actions['AddUser'] = array(
    'standalone' => true,
    'file' => 'Users',
    'loglevel' => JAWS_NOTICE,
);
$actions['UpdateUser'] = array(
    'standalone' => true,
    'file' => 'Users',
    'loglevel' => JAWS_NOTICE,
);
$actions['DeleteUser'] = array(
    'standalone' => true,
    'file' => 'Users',
    'loglevel' => JAWS_WARNING,
);
$actions['GetUserGroups'] = array(
    'standalone' => true,
    'file' => 'Users',
);
$actions['AddUserToGroups'] = array(
    'standalone' => true,
    'file' => 'Users',
    'loglevel' => JAWS_NOTICE,
);

$actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
    'acls' => array('ManageGroups'),
    'navigation' => array(
        'order' => 9
    ),
);
$actions['GetGroups'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['GetGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['AddGlobalGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
    'loglevel' => JAWS_NOTICE,
);
$actions['UpdateGlobalGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
    'loglevel' => JAWS_NOTICE,
);
$actions['DeleteGlobalGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
    'loglevel' => JAWS_NOTICE,
);
$actions['GetGroupUsers'] = array(
    'standalone' => true,
    'file' => 'Groups',
);
$actions['AddUsersToGroup'] = array(
    'standalone' => true,
    'file' => 'Groups',
    'loglevel' => JAWS_NOTICE,
);
$actions['ExportVCard'] = array(
    'standalone' => true,
    'file' => 'VCard',
);
$actions['ImportVCard'] = array(
    'standalone' => true,
    'file' => 'VCard',
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
$admin_actions['Logout'] = array(
    'normal' => true,
    'file' => 'Users',
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
$admin_actions['GetUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetUserContact'] = array(
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
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['DeleteUser'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_WARNING,
);
$admin_actions['DeleteSession'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['IPBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['AgentBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateUserACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateGroupACL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['AddUserToGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['AddUsersToGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
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
);
$admin_actions['UpdatePreferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateContacts'] = array(
    'standalone' => true,
    'file' => 'Ajax',
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
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
    'loglevel' => JAWS_NOTICE,
);
$admin_actions['GroupUsersUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupUsers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ACLs'] = array(
    'normal' => true,
    'file' => 'ACLs',
);
$admin_actions['GetACLs'] = array(
    'standalone' => true,
    'file' => 'ACLs',
);
$admin_actions['GetACLGroupsUsers'] = array(
    'standalone' => true,
    'file' => 'ACLs',
);