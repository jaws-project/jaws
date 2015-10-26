<?php
/**
 * TMS (Theme Management System) Gadget actions
 *
 * @category    GadgetActions
 * @package     TMS
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Admin actions
 */
$admin_actions['Themes'] = array(
    'normal' => true,
    'file' => 'Themes'
);
$admin_actions['UploadTheme'] = array(
    'normal' => true,
    'file' => 'Themes'
);
$admin_actions['DownloadTheme'] = array(
    'standalone' => true,
    'file' => 'Themes'
);
$admin_actions['DeleteTheme'] = array(
    'standalone' => true,
    'file' => 'Ajax'
);
$admin_actions['GetThemeInfo'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
