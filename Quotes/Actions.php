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
$actions = array();
/* Layout actions*/
$actions['Display'] = array(
    'LayoutAction',
    _t('QUOTES_ACTION_DISPLAY'),
    _t('QUOTES_ACTION_DISPLAY_DESCRIPTION'),
    true
);
$actions['RecentQuotes'] = array(
    'NormalAction,LayoutAction',
    _t('QUOTES_LAYOUT_RECENT'),
    _t('QUOTES_LAYOUT_RECENT_DESCRIPTION')
);

/* Normal actions*/
$actions['ViewQuote']       = array('NormalAction');
$actions['ViewGroupQuotes'] = array('NormalAction');

/* Admin actions */
$actions['Admin']       = array('AdminAction');
$actions['QuoteGroups'] = array('AdminAction');

/* Standalone actions */
$actions['QuotesByGroup'] = array('StandaloneAction');
