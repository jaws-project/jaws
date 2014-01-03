<?php
/**
 * Emblems Gadget
 *
 * @category    Gadget
 * @package     Emblems
 * @author      Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Display'] = array(
    'layout' => true,
    'file'   => 'Emblems'
);

/**
 * Admin actions
 */
$admin_actions['Emblems'] = array(
    'normal' => true,
    'file'   => 'Emblems'
);
$admin_actions['AddEmblem'] = array(
    'normal' => true,
    'file'   => 'Emblems'
);
$admin_actions['EditEmblem'] = array(
    'normal' => true,
    'file'   => 'Emblems'
);
$admin_actions['UpdateEmblem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteEmblem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
