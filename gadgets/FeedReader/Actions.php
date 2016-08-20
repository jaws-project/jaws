<?php
/**
 * FeedReader Actions file
 *
 * @category    GadgetActions
 * @package     FeedReader
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh  <afz@php.net>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['DisplayFeeds'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feed'
);
$actions['DisplayFeed'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feed'
);
$actions['DisplayUserFeed'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feed'
);
$actions['GetFeed'] = array(
    'normal' => true,
    'file'   => 'Feed'
);

$actions['UserFeedsList'] = array(
    'normal' => true,
    'file' => 'Feed',
);
$actions['GetUserFeeds'] = array(
    'standalone' => true,
    'file' => 'Feed',
);
$actions['DeleteUserFeeds'] = array(
    'standalone' => true,
    'file' => 'Feed',
);
$actions['GetUserFeed'] = array(
    'standalone' => true,
    'file' => 'Feed',
);
$actions['InsertFeed'] = array(
    'standalone' => true,
    'file' => 'Feed',
);
$actions['UpdateFeed'] = array(
    'standalone' => true,
    'file' => 'Feed',
);

/**
 * Admin actions
 */
$admin_actions['ManageFeeds'] = array(
    'normal' => true,
    'file' => 'Feed',
);
$admin_actions['GetFeed'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertFeed'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateFeed'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteFeed'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
