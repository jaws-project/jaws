<?php
/**
 * Quotes Actions file
 *
 * @category   GadgetActions
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']       = array('AdminAction');
$admin_actions['QuoteGroups'] = array('AdminAction');

/* Layout actions*/
$index_actions['Display'] = array(
    'LayoutAction',
    _t('QUOTES_ACTION_DISPLAY'),
    _t('QUOTES_ACTION_DISPLAY_DESCRIPTION'),
    true
);
$index_actions['RecentQuotes'] = array(
    'NormalAction,LayoutAction',
    _t('QUOTES_LAYOUT_RECENT'),
    _t('QUOTES_LAYOUT_RECENT_DESCRIPTION')
);

/* Normal actions*/
$index_actions['ViewQuote']       = array('NormalAction');
$index_actions['ViewGroupQuotes'] = array('NormalAction');

/* Standalone actions */
$index_actions['QuotesByGroup'] = array('StandaloneAction');
