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
$actions = array();

$actions['Link']    = array('NormalAction');
$actions['Archive'] = array('NormalAction');
$actions['Group']   = array('NormalAction');
$actions['Tag']     = array('NormalAction');

$actions['Display'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_DISPLAY'),
   _t('LINKDUMP_LAYOUT_DISPLAY_DESCRIPTION'),
   true
);
$actions['ShowCategories'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_CATEGORIES'),
    _t('LINKDUMP_LAYOUT_CATEGORIES_DESCRIPTION')
);
$actions['ShowTagCloud'] = array(
    'LayoutAction',
    _t('LINKDUMP_LAYOUT_TAG_CLOUD'),
    _t('LINKDUMP_LAYOUT_TAG_CLOUD_DESCRIPTION')
);
