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
$admin_actions = array();
$admin_actions['Admin']                 = array('StandaloneAdminAction');
$admin_actions['ChangeTheme']           = array('StandaloneAdminAction');
$admin_actions['LayoutManager']         = array('AdminAction');
$admin_actions['LayoutBuilder']         = array('AdminAction');
$admin_actions['SetLayoutMode']         = array('AdminAction');
$admin_actions['DeleteLayoutElement']   = array('AdminAction');
$admin_actions['EditElementAction']     = array('StandaloneAdminAction');
$admin_actions['ChangeDisplayWhen']     = array('StandaloneAdminAction');
$admin_actions['AddLayoutElement']      = array('StandaloneAdminAction');
$admin_actions['SaveAddLayoutElement']  = array('AdminAction');
