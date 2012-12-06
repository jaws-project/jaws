<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

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
