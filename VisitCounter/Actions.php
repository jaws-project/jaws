<?php
/**
 * VisitCounter Actions file
 *
 * @category    GadgetActions
 * @package     VisitCounter
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Layout actions */
$actions['DisplayOnline'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_ONLINE_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_ONLINE')
);
$actions['DisplayToday'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_TODAY_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_TODAY')
);
$actions['DisplayTotal'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_TOTAL_VISITORS'),
    _t('VISITCOUNTER_ACTION_DESC_TOTAL')
);
$actions['Display'] = array(
    'LayoutAction',
    _t('VISITCOUNTER_ACTION_CUSTOM'),
    _t('VISITCOUNTER_ACTION_DESC_CUSTOM')
);
