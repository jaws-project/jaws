<?php
/**
 * LinkDump Actions file
 *
 * @category   GadgetActions
 * @package    LinkDump
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();

$index_actions['Link']    = array('NormalAction');
$index_actions['Archive'] = array('NormalAction');
$index_actions['Group']   = array('NormalAction');
$index_actions['Tag']     = array('NormalAction');

$index_actions['Display'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_DISPLAY'),
   _t('LINKDUMP_LAYOUT_DISPLAY_DESCRIPTION'),
   true
);
$index_actions['ShowCategories'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_CATEGORIES'),
    _t('LINKDUMP_LAYOUT_CATEGORIES_DESCRIPTION')
);
$index_actions['ShowTagCloud'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_TAG_CLOUD'),
    _t('LINKDUMP_LAYOUT_TAG_CLOUD_DESCRIPTION')
);
