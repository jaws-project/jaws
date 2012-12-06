<?php
/**
 * FileBrowser Actions file
 *
 * @category    GadgetActions
 * @package     FileBrowser
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Display']       = array('NormalAction');
$actions['FileInfo']      = array('NormalAction');
$actions['Download']      = array('StandaloneAction');
$actions['InitialFolder'] = array(
    'LayoutAction',
    _t('FILEBROWSER_INITIAL_FOLDER'),
    _t('FILEBROWSER_INITIAL_FOLDER_DESC')
);