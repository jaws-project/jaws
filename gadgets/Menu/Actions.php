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
$index_actions = array();
$admin_actions = array();

/* Amin actions */
$admin_actions['UploadImage'] = array('StandaloneAdminAction');
$admin_actions['LoadImage']   = array('StandaloneAdminAction');

/* Index actions */
$index_actions['Menu'] = array(
    'LayoutAction:Menu',
    _t('MENU_LAYOUT_MENU'),
    _t('MENU_LAYOUT_MENU_DESCRIPTION'),
    true
);
$index_actions['LoadImage'] = array('StandaloneAction');
