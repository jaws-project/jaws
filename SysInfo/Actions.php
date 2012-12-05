<?php
/**
 * SysInfo Actions
 *
 * @category   GadgetActions
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['SysInfo']  = array('AdminAction');
$admin_actions['PHPInfo']  = array('AdminAction');
$admin_actions['JawsInfo'] = array('AdminAction');
$admin_actions['DirInfo']  = array('AdminAction');

/* Index actions */
$index_actions['SysInfo'] = array(
    'LayoutAction,NormalAction',
    _t('SYSINFO_SYSINFO'),
    _t('SYSINFO_SYSINFO_DESC')
);
$index_actions['PHPInfo'] = array(
    'LayoutAction,NormalAction',
    _t('SYSINFO_PHPINFO'),
    _t('SYSINFO_PHPINFO_DESC')
);
$index_actions['JawsInfo'] = array(
    'LayoutAction,NormalAction',
    _t('SYSINFO_JAWSINFO'),
    _t('SYSINFO_JAWSINFO_DESC')
);
$index_actions['DirInfo'] = array(
    'LayoutAction,NormalAction',
    _t('SYSINFO_DIRINFO'),
    _t('SYSINFO_DIRINFO_DESC')
);
