<?php
/**
 * Forums Actions file
 *
 * @category    GadgetActions
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['RecentPosts'] = array(
    'layout' => true,
    'file'   => 'RecentPosts',
    'parametric' => true,
);
$actions['Forums'] = array(
    'normal' => true,
    'file'   => 'Forums',
);
$actions['Topics'] = array(
    'normal' => true,
    'file'   => 'Topics',
);
$actions['NewTopic'] = array(
    'normal' => true,
    'file'   => 'Topics',
);
$actions['EditTopic'] = array(
    'normal' => true,
    'file'   => 'Topics',
);
$actions['DeleteTopic'] = array(
    'normal' => true,
    'file'   => 'Topics',
);
$actions['UpdateTopic'] = array(
    'standalone' => true,
    'file'   => 'Topics',
);
$actions['LockTopic'] = array(
    'standalone' => true,
    'file'   => 'Topics',
);
$actions['Posts'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['Post'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['NewPost'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['EditPost'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['ReplyPost'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['DeletePost'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['UpdatePost'] = array(
    'standalone' => true,
    'file'   => 'Posts',
);
$actions['Attachment'] = array(
    'standalone' => true,
    'file'   => 'Attachment',
);
