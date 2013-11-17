<?php
/**
 * Friends Actions file
 *
 * @category    GadgetActions
 * @package     Friends
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Display'] = array(
    'layout' => true,
    'file'   => 'Friends',
);

/**
 * Admin actions
 */
$admin_actions['Friends'] = array(
    'normal' => true,
    'file' => 'Friends',
);
$admin_actions['GetFriend'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['NewFriend'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateFriend'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteFriend'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
