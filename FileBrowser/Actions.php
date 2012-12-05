<?php
/**
 * FileBrowser Actions file
 *
 * @category   GadgetActions
 * @package    FileBrowser
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']         = array('AdminAction');
$admin_actions['UploadFile']    = array('StandaloneAdminAction');
$admin_actions['BrowseFile']    = array('StandaloneAdminAction');
$admin_actions['DeleteFile']    = array('StandaloneAdminAction');
$admin_actions['DeleteDir']     = array('StandaloneAdminAction');

$index_actions['Display']       = array('NormalAction');
$index_actions['FileInfo']      = array('NormalAction');
$index_actions['Download']      = array('StandaloneAction');
$index_actions['InitialFolder'] = array(
    'LayoutAction',
    _t('FILEBROWSER_INITIAL_FOLDER'),
    _t('FILEBROWSER_INITIAL_FOLDER_DESC')
);