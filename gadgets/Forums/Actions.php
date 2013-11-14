<?php
/**
 * Forums Actions file
 *
 * @category    GadgetActions
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['RecentTopics'] = array(
    'layout' => true,
    'file'   => 'RecentTopics',
    'parametric' => true,
);
$actions['UserPosts'] = array(
    'normal' => true,
    'file'   => 'UserPosts',
);
$actions['UserTopics'] = array(
    'normal' => true,
    'file'   => 'UserTopics',
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
$actions['PublishTopic'] = array(
    'normal' => true,
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

/**
 * Admin actions
 */
$admin_actions['Forums'] = array(
    'normal' => true,
    'file'   => 'Forums',
);
