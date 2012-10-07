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
/* Normal actions*/
$actions = array();
$actions['ViewQuote']       = array('NormalAction');
$actions['RecentQuotes']    = array('NormalAction');
$actions['ViewGroupQuotes'] = array('NormalAction');

/* Admin actions */
$actions['Admin']       = array('AdminAction');
$actions['QuoteGroups'] = array('AdminAction');

/* Standalone actions */
$actions['QuotesByGroup'] = array('StandaloneAction');
