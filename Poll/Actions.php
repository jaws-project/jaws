<?php
/**
 * Poll Actions file
 *
 * @category   GadgetActions
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Polls']      = array('AdminAction');
$admin_actions['PollGroups'] = array('AdminAction');
$admin_actions['Reports']    = array('AdminAction');

$index_actions['Poll'] = array(
    'NormalAction:Poll,LayoutAction:Poll',
    _t('POLL_LAYOUT_POLL'),
    _t('POLL_LAYOUT_POLL_DESC'),
   true
);

$index_actions['Polls'] = array(
    'NormalAction:Polls,LayoutAction:Polls',
    _t('POLL_LAYOUT_POLLS'),
    _t('POLL_LAYOUT_POLLS_DESC'),
   true
);

/* Normal actions*/
$index_actions['ViewResult'] = array('NormalAction');
$index_actions['Vote']       = array('NormalAction');
