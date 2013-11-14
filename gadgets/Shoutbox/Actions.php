<?php
/**
 * Shoutbox Actions file
 *
 * @category    GadgetActions
 * @package     Shoutbox
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Comments'] = array(
    'layout' => true,
    'file'   => 'Comments',
);
$actions['Preview'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['GetComments'] = array(
    'standalone' => true,
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
