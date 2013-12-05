<?php
/**
 * Blocks Actions file
 *
 * @category    GadgetActions
 * @package     Blocks
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Block'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Block',
    'parametric' => true,
);

/**
 * Admin actions
 */
$admin_actions['Blocks'] = array(
    'normal' => true,
    'file'   => 'Blocks',
);
$admin_actions['GetBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['NewBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteBlock'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ParseText'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
