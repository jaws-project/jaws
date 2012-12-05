<?php
/**
 * StaticPage Actions file
 *
 * @category   GadgetActions
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['AddPage']             = array('AdminAction');
$admin_actions['AddNewPage']          = array('AdminAction');
$admin_actions['EditPage']            = array('AdminAction');
$admin_actions['SaveEditPage']        = array('AdminAction');
$admin_actions['Groups']              = array('AdminAction');
$admin_actions['Properties']          = array('AdminAction');
$admin_actions['AddNewTranslation']   = array('AdminAction');
$admin_actions['AddTranslation']      = array('AdminAction');
$admin_actions['EditTranslation']     = array('AdminAction');
$admin_actions['SaveEditTranslation'] = array('AdminAction');

/* Layout actions */
$index_actions['GroupPages'] = array(
    'NormalAction,LayoutAction',
    _t('STATICPAGE_LAYOUT_GROUP_PAGES'),
    _t('STATICPAGE_LAYOUT_GROUP_PAGES_DESC'),
    true
);
$index_actions['PagesList']  = array(
    'LayoutAction', 
    _t('STATICPAGE_LAYOUT_PAGES_LIST'),
    _t('STATICPAGE_LAYOUT_PAGES_LIST_DESCRIPTION')
);
$index_actions['GroupsList'] = array(
    'LayoutAction', 
    _t('STATICPAGE_LAYOUT_GROUPS_LIST'),
    _t('STATICPAGE_LAYOUT_GROUPS_LIST_DESCRIPTION')
);

/* Normal actions */
$index_actions['Page']      = array('NormalAction');
$index_actions['Pages']     = array('NormalAction');
$index_actions['PagesTree'] = array('NormalAction');
