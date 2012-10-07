<?php
/**
 * Search Actions file
 *
 * @category   GadgetActions
 * @package    Search
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Normal actions */
$actions['Results']     = array('NormalAction');
$actions['Search']      = array('NormalAction');
$actions['Box']         = array('NormalAction');
$actions['SimpleBox']   = array('NormalAction');
$actions['AdvancedBox'] = array('NormalAction');

/* Admin actions */
$actions['SaveChanges'] = array('AdminAction');
