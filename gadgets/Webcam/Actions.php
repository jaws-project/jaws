<?php
/**
 * Webcam Actions file
 *
 * @category    GadgetActions
 * @package     Webcam
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Display'] = array(
    'layout' => true,
    'file' => 'Webcam',
);
$actions['Random'] = array(
    'layout' => true,
    'file' => 'Webcam',
);

/**
 * Admin actions
 */
$admin_actions['ManageWebcams'] = array(
    'normal' => true,
    'file' => 'Webcam',
);
$admin_actions['GetWebcam'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['NewWebcam'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateWebcam'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteWebcam'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ShowShortURL'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
