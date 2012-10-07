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

/* Standalone actions */
$actions['UploadImage'] = array('StandaloneAdminAction');
$actions['LoadImage']   = array('StandaloneAdminAction,StandaloneAction');
