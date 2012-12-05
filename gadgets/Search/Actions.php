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
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['SaveChanges'] = array('AdminAction');

/* Actions */
$index_actions['Box'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_BOX'),
    _t('SEARCH_LAYOUT_BOX_DESCRIPTION')
);

$index_actions['SimpleBox'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_SIMPLEBOX'),
    _t('SEARCH_LAYOUT_SIMPLEBOX_DESCRIPTION')
);

$index_actions['AdvancedBox'] = array(
    'NormalAction:Search,LayoutAction:Search',
    _t('SEARCH_LAYOUT_ADVANCEDBOX'),
    _t('SEARCH_LAYOUT_ADVANCEDBOX_DESCRIPTION')
);

$index_actions['Results'] = array('NormalAction:Results');
