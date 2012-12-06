<?php
/**
 * FileBrowser Actions file
 *
 * @category    GadgetActions
 * @package     FileBrowser
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Admin actions */
$actions['Admin']         = array('AdminAction');
$actions['UploadFile']    = array('StandaloneAdminAction');
$actions['BrowseFile']    = array('StandaloneAdminAction');
$actions['DeleteFile']    = array('StandaloneAdminAction');
$actions['DeleteDir']     = array('StandaloneAdminAction');
