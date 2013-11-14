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
$actions = array();

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
