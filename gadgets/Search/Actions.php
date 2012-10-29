<?php
/**
 * Search Actions file
 *
 * @category    GadgetActions
 * @package     Search
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

/* Actions */
$actions['Box'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_BOX'),
    _t('SEARCH_LAYOUT_BOX_DESCRIPTION')
);

$actions['SimpleBox'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_SIMPLEBOX'),
    _t('SEARCH_LAYOUT_SIMPLEBOX_DESCRIPTION')
);

$actions['AdvancedBox'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_ADVANCEDBOX'),
    _t('SEARCH_LAYOUT_ADVANCEDBOX_DESCRIPTION')
);

$actions['Results'] = array('NormalAction:Results');

/* Admin actions */
$actions['SaveChanges'] = array('AdminAction');
