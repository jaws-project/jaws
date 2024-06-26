<?php
/**
 * Shoutbox Actions file
 *
 * @category    GadgetActions
 * @package     Shoutbox
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Comments'] = array(
    'layout' => true,
    'file'   => 'Comments',
);
$actions['GetComments'] = array(
    'standalone' => true,
    'internal' => true,
    'file'   => 'Comments',
);

/**
 * Admin actions
 */
$admin_actions['Comments'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$admin_actions['Settings'] = array(
    'normal' => true,
    'file'   => 'Settings',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
