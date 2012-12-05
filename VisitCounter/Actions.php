<?php
/**
 * VisitCounter Actions file
 *
 * @category   GadgetActions
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();

/* Layout actions */
$index_actions['DisplayOnline'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_ONLINE_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_ONLINE')
);
$index_actions['DisplayToday'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_TODAY_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_TODAY')
);
$index_actions['DisplayTotal'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_TOTAL_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_TOTAL')
);
$index_actions['Display'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_ACTION_CUSTOM'),
    _t('VISITCOUNTER_ACTION_DESC_CUSTOM')
);
