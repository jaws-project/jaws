<?php
/**
 * TMS (Theme Management System) Gadget actions
 *
 * @category   GadgetActions
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$admin_actions = array();

/* Admin actions */
$admin_actions['Themes']        = array('AdminAction');
$admin_actions['UploadTheme']   = array('AdminAction');
$admin_actions['DownloadTheme'] = array('StandaloneAdminAction');
