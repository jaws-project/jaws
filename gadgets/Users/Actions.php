<?php
/**
 * Users Actions
 *
 * @category   GadgetActions
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

/* Layout actions */
$actions['LoginBox'] = array(
    'NormalAction:Login,LayoutAction:Login',
    _t('USERS_LAYOUT_LOGINBOX'),
    _t('USERS_LAYOUT_LOGINBOX_DESC')
);
$actions['LoginLinks'] = array(
    'LayoutAction:Login',
    _t('USERS_LAYOUT_LOGINLINKS'),
    _t('USERS_LAYOUT_LOGINLINKS_DESC')
);
$actions['OnlineUsers'] = array(
    'LayoutAction:Statistics,AdminAction:OnlineUsers',
    _t('USERS_LAYOUT_ONLINE_USERS'),
    _t('USERS_LAYOUT_ONLINE_USERS_DESC'),
);
$actions['OnlineStatistics'] = array(
    'LayoutAction:Statistics',
    _t('USERS_LAYOUT_ONLINE_STATISTICS'),
    _t('USERS_LAYOUT_ONLINE_STATISTICS_DESC'),
);
$actions['LatestRegistered'] = array(
    'LayoutAction:Statistics',
    _t('USERS_LAYOUT_LATEST_REGISTERED'),
    _t('USERS_LAYOUT_LATEST_REGISTERED_DESC'),
);
$actions['Profile'] = array(
    'NormalAction:Profile,LayoutAction:Profile',
    _t('USERS_LAYOUT_PROFILE'),
    _t('USERS_LAYOUT_PROFILE_DESC'),
    true
);

/* Admin actions */
$actions['Users']        = array('AdminAction:Users');
$actions['MyAccount']    = array('AdminAction:MyAccount');
$actions['Groups']       = array('AdminAction:Groups');
$actions['Properties']   = array('AdminAction:Properties');

$actions['LoadAvatar']   = array('StandaloneAdminAction:Avatar');
$actions['UploadAvatar'] = array('StandaloneAdminAction:Avatar');

/* Normal actions */
$actions['Login']          = array('NormalAction:Login');
$actions['Logout']         = array('NormalAction:Login');
$actions['ForgotLogin']    = array('NormalAction:Login');
$actions['SendRecoverKey'] = array('NormalAction:Login');

$actions['Registration'] = array('NormalAction:Registration');
$actions['DoRegister']   = array('NormalAction:Registration');
$actions['Registered']   = array('NormalAction:Registration');
$actions['ActivateUser'] = array('NormalAction:Registration');

$actions['Account']        = array('NormalAction:Account');
$actions['ChangePassword'] = array('NormalAction:Account');
$actions['UpdateAccount']  = array('StandaloneAction:Account');

$actions['Personal']       = array('NormalAction:Personal');
$actions['UpdatePersonal'] = array('StandaloneAction:Personal');

$actions['Preferences']       = array('NormalAction:Preferences');
$actions['UpdatePreferences'] = array('StandaloneAction:Preferences');
