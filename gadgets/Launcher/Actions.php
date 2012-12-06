<?php
/**
 * Launcher Actions file
 *
 * @category    GadgetActions
 * @package     Launcher
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2006-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/* Actions*/
$actions = array();

/* Layout actions */
$actions['Execute'] = array(
    'NormalAction:Execute,LayoutAction:Execute',
    _t('LAUNCHER_SCRIPT'),
    _t('LAUNCHER_SCRIPT_DESC'),
    true
);