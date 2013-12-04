<?php
/**
 * FeedReader Actions file
 *
 * @category    GadgetActions
 * @package     FeedReader
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh  <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
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
$actions['GetFeed'] = array(
    'normal' => true,
    'file'   => 'Feed'
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
