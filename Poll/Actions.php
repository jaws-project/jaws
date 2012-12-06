<?php
/**
 * Poll Actions file
 *
 * @category    GadgetActions
 * @package     Poll
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Poll'] = array(
    'NormalAction:Poll,LayoutAction:Poll',
    _t('POLL_LAYOUT_POLL'),
    _t('POLL_LAYOUT_POLL_DESC'),
   true
);

$actions['Polls'] = array(
    'NormalAction:Polls,LayoutAction:Polls',
    _t('POLL_LAYOUT_POLLS'),
    _t('POLL_LAYOUT_POLLS_DESC'),
   true
);

/* Normal actions*/
$actions['ViewResult'] = array('NormalAction');
$actions['Vote'] = array('NormalAction');
