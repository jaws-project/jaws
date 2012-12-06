<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['Users'] = array(
    'normal' => true,
    'file' => 'Users',
);
$actions['MyAccount'] = array(
    'normal' => true,
    'file' => 'MyAccount',
);
$actions['Groups'] = array(
    'normal' => true,
    'file' => 'Groups',
);
$actions['OnlineUsers'] = array(
    'normal' => true,
    'file' => 'OnlineUsers',
);
$actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
$actions['LoadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
$actions['UploadAvatar'] = array(
    'standalone' => true,
    'file' => 'Avatar',
);
