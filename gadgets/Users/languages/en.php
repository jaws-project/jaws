<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Users"
 * "Last-Translator: Helgi Þormar Þorbjörnsson <dufuz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_EN_USERS_NAME', "Users");
define('_EN_USERS_DESCRIPTION', "User administration.");

/* ACLs */
define('_EN_USERS_ACL_DEFAULT', "Use users");
define('_EN_USERS_ACL_MANAGEUSERS', "User management");
define('_EN_USERS_ACL_MANAGEGROUPS', "Group management");
define('_EN_USERS_ACL_MANAGEONLINEUSERS', "Manage online users");
define('_EN_USERS_ACL_MANAGEPROPERTIES', "Properties management");
define('_EN_USERS_ACL_MANAGEUSERACLS', "User's ACLs management");
define('_EN_USERS_ACL_MANAGEGROUPACLS', "Group's ACLs management");
define('_EN_USERS_ACL_EDITUSERNAME', "Edit user's username by user");
define('_EN_USERS_ACL_EDITUSERNICKNAME', "Edit user's nickname by user");
define('_EN_USERS_ACL_EDITUSEREMAIL', "Edit user's email by user");
define('_EN_USERS_ACL_EDITUSERPASSWORD', "Edit user's password by user");
define('_EN_USERS_ACL_EDITUSERPERSONAL', "Edit user's personal data by user");
define('_EN_USERS_ACL_EDITUSERCONTACT', "Edit user's contact data by user");
define('_EN_USERS_ACL_EDITUSERPREFERENCES', "Edit user's preferences by user");
define('_EN_USERS_ACL_MANAGEAUTHENTICATIONMETHOD', "Manage authentication method");

/* Layout */
define('_EN_USERS_LAYOUT_LOGINBOX', "Login Box");
define('_EN_USERS_LAYOUT_LOGINBOX_DESC', "Display Login Box.");
define('_EN_USERS_LAYOUT_LOGINLINKS', "Login links");
define('_EN_USERS_LAYOUT_LOGINLINKS_DESC', "Display some links related to login stuff.");
define('_EN_USERS_LAYOUT_ONLINE_USERS', "Online users");
define('_EN_USERS_LAYOUT_ONLINE_USERS_DESC', "Display online registered users");
define('_EN_USERS_LAYOUT_ONLINE_STATISTICS', "Online users statistics");
define('_EN_USERS_LAYOUT_ONLINE_STATISTICS_DESC', "Display statistics of online users/guests");
define('_EN_USERS_LAYOUT_LATEST_REGISTERED', "Latest registered users");
define('_EN_USERS_LAYOUT_LATEST_REGISTERED_DESC', "Display Latest registered users");
define('_EN_USERS_LAYOUT_PROFILE', "About a user");
define('_EN_USERS_LAYOUT_PROFILE_DESC', "Display user's profile information");

/* Group Management */
define('_EN_USERS_GROUPS_GROUPNAME', "Group name");
define('_EN_USERS_GROUPS_GROUPID', "Group ID");
define('_EN_USERS_GROUPS_GROUP', "Group");
define('_EN_USERS_GROUPS_GROUPS', "Groups");
define('_EN_USERS_GROUPS_ALL_GROUPS', "All Groups");
define('_EN_USERS_GROUPS_ADD', "Add a group");
define('_EN_USERS_GROUPS_NOGROUP', "No Group");
define('_EN_USERS_GROUPS_NO_SELECTION', "Please select a group from your left");
define('_EN_USERS_GROUPS_GROUP_INFO', "Group Information");
define('_EN_USERS_GROUPS_ALREADY_EXISTS', "Group {0} already exists");
define('_EN_USERS_GROUPS_INCOMPLETE_FIELDS', "Please fill the group name field");
define('_EN_USERS_GROUPS_UPDATE', "Update Group");
define('_EN_USERS_GROUPS_EDIT', "Edit Group");
define('_EN_USERS_GROUPS_DELETE', "Delete Group");
define('_EN_USERS_GROUPS_PERMISSIONS', "Group permissions");
define('_EN_USERS_GROUPS_ACL_UPDATED', "Group privileges have been updated");
define('_EN_USERS_GROUPS_GROUP_NOT_EXIST', "The requested group does not exist.");
define('_EN_USERS_GROUPS_MEMBERS', "Members");
define('_EN_USERS_GROUPS_ADD_USER', "Add user to group");
define('_EN_USERS_GROUPS_CONFIRM_DELETE', "Are you sure you want to delete this group?");
define('_EN_USERS_GROUPS_CURRENTLY_EDITING_GROUP', "You are currently editing group {0}");
define('_EN_USERS_GROUPS_MARK_USERS', "Select the users you want to add to the group");
define('_EN_USERS_GROUPS_ACL_RESETED', "Group privileges have been reseted");

/* Group Management Responses*/
define('_EN_USERS_GROUPS_CREATED', "Group {0} has been created.");
define('_EN_USERS_GROUPS_UPDATED', "Group {0} has been updated");
define('_EN_USERS_GROUPS_DELETED', "Group {0} has been deleted.");
define('_EN_USERS_GROUPS_UPDATED_USERS', "The relations between users and groups have been updated");

/* Group Management Errors*/
define('_EN_USERS_GROUPS_NOT_CREATED', "There was a problem creating group {0}.");
define('_EN_USERS_GROUPS_NOT_UPDATED', "There was a problem updating group {0}.");
define('_EN_USERS_GROUPS_CANT_DELETE', "There was a problem deleting group {0}.");

/* User Management */
define('_EN_USERS_USERS', "Users");
define('_EN_USERS_ACCOUNT_INFO', "Account Information");
define('_EN_USERS_PERSONAL_INFO', "Personal Information");
define('_EN_USERS_PROFILE_INFO', "Profile Information");
define('_EN_USERS_USERS_ADD', "Add User");
define('_EN_USERS_ACCOUNT_EDIT', "Edit User");
define('_EN_USERS_ACCOUNT_DELETE', "Delete User");
define('_EN_USERS_USERS_GROUPS', "User Groups");
define('_EN_USERS_ACLRULES', "ACL Rules");
define('_EN_USERS_USERS_USERNAME', "Username");
define('_EN_USERS_USERS_PASSWORD', "Password");
define('_EN_USERS_USERS_NICKNAME', "Nickname");
define('_EN_USERS_USERS_FIRSTNAME', "First name");
define('_EN_USERS_USERS_LASTNAME', "Last name");
define('_EN_USERS_USERS_USERID', "User ID");
define('_EN_USERS_USERS_TYPE', "Type");
define('_EN_USERS_USERS_TYPE_SUPERADMIN', "Super administrator");
define('_EN_USERS_USERS_TYPE_NORMAL', "Normal user");
define('_EN_USERS_USERS_STATUS_0', "Disabled");
define('_EN_USERS_USERS_STATUS_1', "Enabled");
define('_EN_USERS_USERS_STATUS_2', "Not Verified");
define('_EN_USERS_USERS_PRIVACY', "Privacy");
define('_EN_USERS_USERS_GENDER', "Gender");
define('_EN_USERS_USERS_GENDER_0', "Unknown");
define('_EN_USERS_USERS_GENDER_1', "Male");
define('_EN_USERS_USERS_GENDER_2', "Female");
define('_EN_USERS_USERS_BIRTHDAY', "Birthday");
define('_EN_USERS_USERS_BIRTHDAY_SAMPLE', "e.g., 2009/08/31");
define('_EN_USERS_USERS_ABOUT', "About");
define('_EN_USERS_USERS_EXPERIENCES', "Experiences and Skills");
define('_EN_USERS_USERS_OCCUPATIONS', "Occupations");
define('_EN_USERS_USERS_INTERESTS', "Interests");
define('_EN_USERS_USERS_SHOW_ALL', "Show all");
define('_EN_USERS_USERS_CONCURRENT_LOGINS', "Concurrent logins");
define('_EN_USERS_USERS_EXPIRY_DATE', "Expiry date");
define('_EN_USERS_USERS_REGISTRATION_DATE', "Registration date");
define('_EN_USERS_USERS_SEARCH_TERM', "Term");
define('_EN_USERS_USERS_ORDER_TYPE', "Order");
define('_EN_USERS_USERS_SEND_AUTO_PASSWORD', "Leave it empty for send random password to your email");
define('_EN_USERS_USERS_PASSWORD_VERIFY', "Verify Password");
define('_EN_USERS_USERS_PASSWORD_OLD', "Old Password");
define('_EN_USERS_USERS_NO_SELECTION', "Please select an user");
define('_EN_USERS_USERS_PASSWORDS_DONT_MATCH', "The password entries do not match.");
define('_EN_USERS_USERS_INCOMPLETE_FIELDS', "Some fields haven't been filled in.");
define('_EN_USERS_USERS_ALREADY_EXISTS', "There is another user using the same username ({0}).");
define('_EN_USERS_EMAIL_ALREADY_EXISTS', "There is another user using the same email ({0}).");
define('_EN_USERS_USERS_CONFIRM_NO_CHANGES', "Are you sure you don't want to save the data?");
define('_EN_USERS_USERS_SELECT_A_USER', "Select an user from the left.");
define('_EN_USERS_USER_NOT_EXIST', "The requested user does not exist.");
define('_EN_USERS_USERS_EDIT', "Edit User");
define('_EN_USERS_USERS_ACCOUNT_INFO', "Account Information");
define('_EN_USERS_USERS_ACCOUNT_PREF', "Account Preferences");
define('_EN_USERS_USERS_ACCOUNT_UPDATE', "Update Account");
define('_EN_USERS_USERS_PERMISSIONS', "Permissions");
define('_EN_USERS_USER_CONFIRM_DELETE', "Delete this user and all information this user has submitted?");
define('_EN_USERS_USER_MEMBER_OF_GROUPS', "{0} is a member of the groups below");
define('_EN_USERS_USER_MEMBER_OF_NO_GROUPS', "Currently {0} is not in any group");
define('_EN_USERS_THIS_USER', 'This user');
define('_EN_USERS_USER_CANT_AUTO_TURN_OFF_CP', "You can't turn off your privileges for all ControlPanel");
define('_EN_USERS_GROUPS', "Users groups");
define('_EN_USERS_USER_CURRENTLY_EDITING', "You are currently editing user {0}");
define('_EN_USERS_LOGIN_TITLE', "Login");
define('_EN_USERS_NOCHANGE_PASSWORD', "Just leave it empty if you are not willing to change it");
define('_EN_USERS_USERS_MARK_GROUPS', "Select the groups you want this user member of");
define('_EN_USERS_RESET_ACL', "Reset ACL");
define('_EN_USERS_RESET_ACL_CONFIRM', "Are you sure you want to reset (delete) the permissions?");
define('_EN_USERS_PERSONAL', "Personal Information");
define('_EN_USERS_PREFERENCES', "Preferences");
define('_EN_USERS_ADVANCED_OPTS_EDITOR', "User editor");
define('_EN_USERS_ADVANCED_OPTS_LANGUAGE', "Preferred language");
define('_EN_USERS_ADVANCED_OPTS_THEME', "Preferred theme");
define('_EN_USERS_ADVANCED_OPTS_NOT_YET', "No value defined yet");
define('_EN_USERS_USERS_PERMISSION_ALLOW', "Allow");
define('_EN_USERS_USERS_PERMISSION_DENY', "Deny");
define('_EN_USERS_USERS_PERMISSION_NONE', "Default");

/* MyAccount */
define('_EN_USERS_MYACCOUNT_UPDATED', "Your profile has been updated.");
define('_EN_USERS_MYACCOUNT_PASSWORDS_DONT_MATCH', "Your password and password verification do not match.");
define('_EN_USERS_MYACCOUNT_INCOMPLETE_FIELDS', "Please fill all the fields if you want to update your account.");
define('_EN_USERS_MYACCOUNT', "My Account");
define('_EN_USERS_EDIT_ACCOUNT', "Edit Account");
define('_EN_USERS_EDIT_PERSONAL', "Edit Personal");
define('_EN_USERS_CONTROLPANEL', "Control Panel");
define('_EN_USERS_EDIT_PREFERENCES', "Edit Preferences");
define('_EN_USERS_PREFERENCES_UPDATED', "Your preferences have been updated.");
define('_EN_USERS_LOGINLINKS', "Login links");
define('_EN_USERS_WELCOME', 'Welcome');

/* User Management Responses */
define('_EN_USERS_USERS_CREATED', "User {0} has been created.");
define('_EN_USERS_USERS_UPDATED', "User {0} has been updated.");
define('_EN_USERS_USERS_ACL_UPDATED', "User privileges have been updated.");
define('_EN_USERS_USER_DELETED', "User {0} has been deleted.");
define('_EN_USERS_USERS_ACL_RESETED', "User privileges have been reseted.");
define('_EN_USERS_USERS_PERSONALINFO_UPDATED', "Personal information have been updated");
define('_EN_USERS_USERS_ADVANCED_UPDATED', "User advanced options have been updated");

/* User Management Errors */
define('_EN_USERS_USERS_NOT_CREATED', "There was a problem creating user {0}.");
define('_EN_USERS_USERS_NOT_UPDATED', "There was a problem updating user {0}.");
define('_EN_USERS_USERS_CANT_DELETE', "There was a problem deleting user {0}.");
define('_EN_USERS_USERS_CANT_DELETE_SELF', "Can't delete your same user");
define('_EN_USERS_USERS_PERSONALINFO_NOT_UPDATED', "There was a problem updating personal information");
define('_EN_USERS_USERS_NOT_ADVANCED_UPDATED', "There was a problem updating advanced user options");

/* Online Users */
define('_EN_USERS_ONLINE_USERS', "Online users");
define('_EN_USERS_ONLINE_ADMIN', "Admin");
define('_EN_USERS_ONLINE_ANONY', "Anonymous");
define('_EN_USERS_ONLINE_ACTIVE', "Active");
define('_EN_USERS_ONLINE_INACTIVE', "Inactive");
define('_EN_USERS_ONLINE_LAST_ACTIVETIME', "Last Active Time");
define('_EN_USERS_ONLINE_BLOCKING_IP', "Block IP");
define('_EN_USERS_ONLINE_BLOCKING_AGENT', "Block agent");
define('_EN_USERS_ONLINE_CONFIRM_THROWOUT', "Are you sure you want to throwout this user?");
define('_EN_USERS_ONLINE_CONFIRM_BLOCKIP', "Are you sure you want to block this IP address?");
define('_EN_USERS_ONLINE_CONFIRM_BLOCKAGENT', "Are you sure you want to block this browser/robot agent?");
define('_EN_USERS_ONLINE_SESSION_DELETED', "Session has been deleted");
define('_EN_USERS_ONLINE_SESSION_NOT_DELETED', "There was a problem deleting  session");
define('_EN_USERS_ONLINE_NO_ONLINE', "There is no online registered user");
define('_EN_USERS_ONLINE_REGISTERED_COUNT', "Online registered");
define('_EN_USERS_ONLINE_GUESTS_COUNT', "Online guests");

/* Properties */
define('_EN_USERS_PROPERTIES_ANON_REGISTER', "Anonymous users can register");
define('_EN_USERS_PROPERTIES_ANON_REPETITIVE_EMAIL', "Anonymous can register by repetitive email");
define('_EN_USERS_PROPERTIES_ANON_ACTIVATION', "Anonymous activation type");
define('_EN_USERS_PROPERTIES_ACTIVATION_AUTO', "Auto");
define('_EN_USERS_PROPERTIES_ACTIVATION_BY_USER', "By User");
define('_EN_USERS_PROPERTIES_ACTIVATION_BY_ADMIN', "By Admin");
define('_EN_USERS_PROPERTIES_ANON_GROUP', "Default group of registered user");
define('_EN_USERS_PROPERTIES_PASS_RECOVERY', "Users can recover their passwords");
define('_EN_USERS_PROPERTIES_UPDATED', "Properties have been updated");
define('_EN_USERS_PROPERTIES_CANT_UPDATE', "There was a problem when updating the properties");

/* Permission message */
define('_EN_USERS_NO_PERMISSION_TITLE', "No permission");
define('_EN_USERS_NO_PERMISSION_DESC', "I'm sorry but you don't have permission to execute this action ({0}::{1}).");
define('_EN_USERS_NO_PERMISSION_ANON_DESC', "The reason is that you are logged as anonymous. A possible fix is to <a href=\"{0}\">login again</a> with a valid username.");

/* Anon registration */
define('_EN_USERS_REGISTER', "Create account");
define('_EN_USERS_REGISTER_NOT_ENABLED', "Sorry but anonymous users can't register, ask the admininistrator for an account.");
define('_EN_USERS_REGISTER_ALREADY_LOGGED', "You are already logged with a non-anonymous account, click <a href=\"{0}\">here</a> if you want to logout");
define('_EN_USERS_REGISTER_VALID_USERNAMES', "No periods, slash, special symbols are valid...");
define('_EN_USERS_REGISTER_EMAIL_NOT_VALID', "The email is not totally valid, please check it");
define('_EN_USERS_REGISTER_SUBJECT', "User registration - {0}");
define('_EN_USERS_REGISTER_HELLO', "Hello {0}");
define('_EN_USERS_REGISTER_ADMIN_MAIL_MSG', "A new account has been created"); 
define('_EN_USERS_REGISTER_MAIL_MSG', "We got an account registration that points to your email.\nIf you think this is an error please reply back telling us the error.");
define('_EN_USERS_REGISTER_ACTIVATION_MAIL_MSG', "Your account has been created.\nHowever, you need activate your account before use this website.");
define('_EN_USERS_REGISTER_ACTIVATION_SENDMAIL_FAILED', "There was a problem while sending the activation link to {0}, however, for security reasons we deleted your user from the database");
define('_EN_USERS_REGISTER_RANDOM_MAIL_MSG', "We got an account registration that points to your email.\nYou also decided to have a strong-random password so we decided to send you this password.");
define('_EN_USERS_REGISTER_BY_ADMIN_RANDOM_MAIL_MSG', "We got an account registration that points to your email.\nYou also decided to have a strong-random password so we decided to send you this password.\nHowever, you need activate your account by administrator group, before use this website.");
define('_EN_USERS_REGISTER_BY_USER_RANDOM_MAIL_MSG', "We got an account registration that points to your email.\nYou also decided to have a strong-random password so we decided to send you this password.\nHowever, you need activate your account before use this website.");
define('_EN_USERS_REGISTER_RANDOM_SENDMAIL_FAILED', "There was a problem while sending the password to {0}, however, for security reasons we deleted your user from the database");
define('_EN_USERS_REGISTER_REGISTERED', "Account created");
define('_EN_USERS_REGISTER_REGISTERED_MSG', "The account has been created, you can <a href=\"{0}\">login</a> whenever you want. If you asked for a random password you should check your email in order to know it");

/* Anon activation */
define('_EN_USERS_ACTIVATE_ACTIVATION_LINK', "Activation link");
define('_EN_USERS_ACTIVATE_ACTIVATION_BY_ADMIN_MSG', "Your account has been created.\nHowever, this website requires account activation by the administrator group.\nAn e-mail has been sent to them and you will be informed when your account has been activated.");
define('_EN_USERS_ACTIVATE_ACTIVATION_BY_USER_MSG', "Your account has been created.\nHowever, this website requires account activation, an activation key has been sent to the e-mail address you provided.\nPlease check your e-mail for further information.");
define('_EN_USERS_ACTIVATE_ACTIVATED_BY_USER_MSG', "The account has been activated, you can <a href=\"{0}\">login</a> whenever you want.");
define('_EN_USERS_ACTIVATE_ACTIVATED_BY_ADMIN_MSG', "The account has been activated.");
define('_EN_USERS_ACTIVATE_ACTIVATED_MAIL_MSG', "Your account has been activated, you can login whenever you want.");
define('_EN_USERS_ACTIVATE_NOT_ACTIVATED_SENDMAIL', "There was a problem while sending the activation link to {0}, however, for security reasons we deleted your user from the database");
define('_EN_USERS_ACTIVATION_KEY_NOT_VALID', "Sorry, the activation key is not valid");
define('_EN_USERS_ACTIVATION_ERROR_ACTIVATION', "There was an error while activating the account");

/* Password recovery */
define('_EN_USERS_FORGOT_LOGIN', "Forgot your login information?");
define('_EN_USERS_FORGOT_REMEMBER', "Remember user and password");
define('_EN_USERS_FORGOT_REMEMBER_INFO', "Enter your email address to retrieve your login information.");
define('_EN_USERS_FORGOT_MAIL_SENT', "A mail has been sent with information to change your password");
define('_EN_USERS_FORGOT_ERROR_SENDING_MAIL', "There was an error while sending you an email with more information about recovering your password");
define('_EN_USERS_FORGOT_MAIL_MESSAGE', "Someone has asked us to remember your password. To change your password open the following link, , otherwise just ignore this email (your password wont be changed).");
define('_EN_USERS_FORGOT_KEY_NOT_VALID', "Sorry, the key is not valid");
define('_EN_USERS_FORGOT_PASSWORD_CHANGED', "The new password (auto-generated) has been sent to your email");
define('_EN_USERS_FORGOT_PASSWORD_CHANGED_SUBJECT', "New password");
define('_EN_USERS_FORGOT_PASSWORD_CHANGED_MESSAGE', "A new password has been asigned to your account, you can find it below. In order to change it you need to login with your username ({0}) and this password, then you can update your profile.");
define('_EN_USERS_FORGOT_ERROR_CHANGING_PASSWORD', "There was an error while changing your password");
