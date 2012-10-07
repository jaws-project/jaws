<?php
/**
 * ControlPanel Actions
 *
 * @category   GadgetActions
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['DefaultAction']  = array('AdminAction');
$actions['Logout']         = array('AdminAction');

$actions['Backup']         = array('StandaloneAdminAction');
