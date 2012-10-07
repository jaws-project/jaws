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
/* Normal actions*/
$actions = array();
$actions['LastPoll']    = array('NormalAction');
$actions['ListOfPolls'] = array('NormalAction');
$actions['ViewPoll']    = array('NormalAction');
$actions['ViewResult']  = array('NormalAction');
$actions['Vote']        = array('NormalAction');

/* Admin actions */
$actions['Polls']       = array('AdminAction');
$actions['PollGroups']  = array('AdminAction');
$actions['Reports']     = array('AdminAction');
