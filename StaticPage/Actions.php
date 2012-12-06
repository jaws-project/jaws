<?php
/**
 * StaticPage Actions file
 *
 * @category    GadgetActions
 * @package     StaticPage
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Layout actions */
$actions['GroupPages'] = array(
    'NormalAction,LayoutAction',
    _t('STATICPAGE_LAYOUT_GROUP_PAGES'),
    _t('STATICPAGE_LAYOUT_GROUP_PAGES_DESC'),
    true
);
$actions['PagesList']  = array(
    'LayoutAction', 
    _t('STATICPAGE_LAYOUT_PAGES_LIST'),
    _t('STATICPAGE_LAYOUT_PAGES_LIST_DESCRIPTION')
);
$actions['GroupsList'] = array(
    'LayoutAction', 
    _t('STATICPAGE_LAYOUT_GROUPS_LIST'),
    _t('STATICPAGE_LAYOUT_GROUPS_LIST_DESCRIPTION')
);

/* Normal actions */
$actions['Page']      = array('NormalAction');
$actions['Pages']     = array('NormalAction');
$actions['PagesTree'] = array('NormalAction');
