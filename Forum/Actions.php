<?php
/**
 * Forum Actions file
 *
 * @category   GadgetActions
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();
$actions['Forums']      = array('AdminAction,NormalAction:Forums');
$actions['Topics']      = array('NormalAction:Topics');
$actions['Topic']       = array('NormalAction:Topics');
$actions['NewTopic']    = array('NormalAction:Topics');
$actions['UpdateTopic'] = array('StandaloneAction:Topics');
$actions['Post']        = array('NormalAction:Posts');
$actions['UpdatePost']  = array('StandaloneAction:Posts');
