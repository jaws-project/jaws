<?php
/**
 * Users URL maps
 *
 * @category   GadgetMaps
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array('LoginBox',
                'user/login/referrer/{referrer}',
                '',
                array('referrer' => '.*'));
$maps[] = array(
    'Profile',
    'users/{user}',
    '',
    array('user' => '[[:alnum:]-_\.\@]+')
);
$maps[] = array('LoginBox', 'user/login');
$maps[] = array('Registration', 'user/registration');
$maps[] = array('Registered', 'user/registered');
$maps[] = array('Logout', 'user/logout');
$maps[] = array('Account', 'user/account');
$maps[] = array('Personal', 'user/personal');
$maps[] = array('Preferences', 'user/preferences');
$maps[] = array('ForgotLogin', 'user/forget');
$maps[] = array('ChangePassword', 'user/recover/key/{key}');
$maps[] = array('ActivateUser', 'user/activate/key/{key}');
