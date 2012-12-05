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
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Users']        = array('AdminAction:Users');
$admin_actions['MyAccount']    = array('AdminAction:MyAccount');
$admin_actions['Groups']       = array('AdminAction:Groups');
$admin_actions['OnlineUsers']  = array('AdminAction:OnlineUsers');
$admin_actions['Properties']   = array('AdminAction:Properties');
$admin_actions['LoadAvatar']   = array('StandaloneAdminAction:Avatar');
$admin_actions['UploadAvatar'] = array('StandaloneAdminAction:Avatar');

/* Index actions */
$index_actions['LoginBox'] = array(
    'NormalAction:Login,LayoutAction:Login',
    _t('USERS_LAYOUT_LOGINBOX'),
    _t('USERS_LAYOUT_LOGINBOX_DESC')
);
$index_actions['LoginLinks'] = array(
    'LayoutAction:Login',
    _t('USERS_LAYOUT_LOGINLINKS'),
    _t('USERS_LAYOUT_LOGINLINKS_DESC')
);
$index_actions['OnlineUsers'] = array(
    'LayoutAction:Statistics',
    _t('USERS_LAYOUT_ONLINE_USERS'),
    _t('USERS_LAYOUT_ONLINE_USERS_DESC'),
);
$index_actions['OnlineStatistics'] = array(
    'LayoutAction:Statistics',
    _t('USERS_LAYOUT_ONLINE_STATISTICS'),
    _t('USERS_LAYOUT_ONLINE_STATISTICS_DESC'),
);
$index_actions['LatestRegistered'] = array(
    'LayoutAction:Statistics',
    _t('USERS_LAYOUT_LATEST_REGISTERED'),
    _t('USERS_LAYOUT_LATEST_REGISTERED_DESC'),
);
$index_actions['Profile'] = array(
    'NormalAction:Profile,LayoutAction:Profile',
    _t('USERS_LAYOUT_PROFILE'),
    _t('USERS_LAYOUT_PROFILE_DESC'),
    true
);

/* Normal actions */
$index_actions['Login']          = array('NormalAction:Login');
$index_actions['Logout']         = array('NormalAction:Login');
$index_actions['ForgotLogin']    = array('NormalAction:Login');
$index_actions['SendRecoverKey'] = array('NormalAction:Login');

$index_actions['Registration'] = array('NormalAction:Registration');
$index_actions['DoRegister']   = array('NormalAction:Registration');
$index_actions['Registered']   = array('NormalAction:Registration');
$index_actions['ActivateUser'] = array('NormalAction:Registration');

$index_actions['Account']        = array('NormalAction:Account');
$index_actions['ChangePassword'] = array('NormalAction:Account');
$index_actions['UpdateAccount']  = array('StandaloneAction:Account');

$index_actions['Personal']       = array('NormalAction:Personal');
$index_actions['UpdatePersonal'] = array('StandaloneAction:Personal');

$index_actions['Preferences']       = array('NormalAction:Preferences');
$index_actions['UpdatePreferences'] = array('StandaloneAction:Preferences');
