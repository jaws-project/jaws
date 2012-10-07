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
$actions = array();

$actions['Page']       = array('NormalAction');
$actions['Pages']      = array('NormalAction');
$actions['GroupPages'] = array('NormalAction');
$actions['PagesTree']  = array('NormalAction');

$actions['PagesList']  = array('LayoutAction', 
                               _t('STATICPAGE_LAYOUT_PAGES_LIST'),
                               _t('STATICPAGE_LAYOUT_PAGES_LIST_DESCRIPTION'));
$actions['GroupsList'] = array('LayoutAction', 
                               _t('STATICPAGE_LAYOUT_GROUPS_LIST'),
                               _t('STATICPAGE_LAYOUT_GROUPS_LIST_DESCRIPTION'));

$actions['AddPage']             = array('AdminAction');
$actions['AddNewPage']          = array('AdminAction');
$actions['EditPage']            = array('AdminAction');
$actions['SaveEditPage']        = array('AdminAction');
$actions['Groups']              = array('AdminAction');
$actions['Properties']          = array('AdminAction');
$actions['AddNewTranslation']   = array('AdminAction');
$actions['AddTranslation']      = array('AdminAction');
$actions['EditTranslation']     = array('AdminAction');
$actions['SaveEditTranslation'] = array('AdminAction');
