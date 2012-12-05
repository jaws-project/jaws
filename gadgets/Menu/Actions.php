<?php
/**
 * Menu Actions file
 *
 * @category    GadgetActions
 * @package     Menu
 * @author      Mohsen Khahani <mohsen@khahani.com>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

$actions = array();

$actions['Menu'] = array(
    'LayoutAction:Menu',
    _t('MENU_LAYOUT_MENU'),
    _t('MENU_LAYOUT_MENU_DESCRIPTION'),
    true
);

/* Standalone actions */
$actions['UploadImage'] = array('StandaloneAdminAction');
$actions['LoadImage']   = array('StandaloneAdminAction,StandaloneAction');
