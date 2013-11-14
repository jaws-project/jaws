<?php
/**
 * Poll Actions file
 *
 * @category    GadgetActions
 * @package     Poll
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Poll'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Poll',
    'parametric' => true,
);
$actions['Polls'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Poll',
    'parametric' => true,
);
$actions['ViewResult'] = array(
    'normal' => true,
    'file' => 'Poll',
);
$actions['Vote'] = array(
    'normal' => true,
    'file' => 'Poll',
);

/**
 * Admin actions
 */
$admin_actions['Polls'] = array(
    'normal' => true,
    'file' => 'Poll',
);
$admin_actions['PollGroups'] = array(
    'normal' => true,
    'file' => 'Group',
);
$admin_actions['Reports'] = array(
    'normal' => true,
    'file' => 'Report',
);
