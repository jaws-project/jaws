<?php
/**
 * Forums Actions file
 *
 * @category    GadgetActions
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Forums'] = array(
    'normal' => true,
    'file'   => 'Forums',
);
$actions['Group'] = array(
    'normal' => true,
    'file'   => 'Forums',
);
$actions['Topics'] = array(
    'normal' => true,
    'file'   => 'Topics',
);
$actions['RecentTopics'] = array(
    'layout' => true,
    'file'   => 'RecentTopics',
    'parametric' => true,
);
$actions['UserTopics'] = array(
    'normal' => true,
    'file'   => 'UserTopics',
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
$actions['UserPosts'] = array(
    'normal' => true,
    'file'   => 'UserPosts',
);
$actions['Post'] = array(
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
$actions['GetPost'] = array(
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
$admin_actions['GetGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetForum'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetForumUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertForum'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateForum'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteForum'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
