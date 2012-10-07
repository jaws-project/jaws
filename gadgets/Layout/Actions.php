<?php
/**
 * Layout Actions
 *
 * @category   GadgetActions
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['Admin']                 = array('StandaloneAdminAction');
$actions['ChangeTheme']           = array('StandaloneAdminAction');
$actions['LayoutManager']         = array('AdminAction');
$actions['LayoutBuilder']         = array('AdminAction');
$actions['SetLayoutMode']         = array('AdminAction');
$actions['DeleteLayoutElement']   = array('AdminAction');
$actions['EditElementAction']     = array('StandaloneAdminAction');
$actions['ChangeDisplayWhen']     = array('StandaloneAdminAction');
$actions['AddLayoutElement']      = array('StandaloneAdminAction');
$actions['SaveAddLayoutElement']  = array('AdminAction');
