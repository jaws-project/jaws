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
$actions = array();

/* Admin actions */
$actions['Themes']        = array('AdminAction');
$actions['UploadTheme']   = array('AdminAction');
$actions['DownloadTheme'] = array('StandaloneAdminAction');
