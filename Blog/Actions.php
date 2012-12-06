<?php
/**
 * Blog Actions file
 *
 * @category    GadgetActions
 * @package     Blog
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['LastPost'] = array(
    'normal' => true,
    'file'   => 'Post',
);
$actions['SingleView'] = array(
    'normal' => true,
    'file'   => 'Post',
);
$actions['ViewPage'] = array(
    'normal' => true,
    'file'   => 'Posts',
);
$actions['ViewDatePage'] = array(
    'normal' => true,
    'file'   => 'DatePosts',
);
$actions['ViewAuthorPage'] = array(
    'normal' => true,
    'file'   => 'AuthorPosts',
);
$actions['Comment'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['Reply'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['Preview'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['SaveComment'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['Archive'] = array(
    'normal' => true,
    'file'   => 'Archive',
);
$actions['ShowCategory'] = array(
    'normal' => true,
    'file'   => 'Categories',
);

$actions['EntriesByCategory'] = array(
    'layout' => true,
    'parametric' => true,
);
$actions['CategoriesList'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Categories',
);
$actions['PopularPosts'] = array(
    'normal' => true,
    'layout' => true,
);
$actions['PostsAuthors'] = array(
    'normal' => true,
    'layout' => true,
);
$actions['MonthlyHistory'] = array(
    'layout' => true,
);
$actions['RecentPosts'] = array(
    'layout' => true,
);
$actions['RecentComments'] = array(
    'layout' => true,
);
$actions['Calendar'] = array(
    'layout' => true,
);
$actions['RSSLink'] = array(
    'layout' => true,
);
$actions['AtomLink'] = array(
    'layout' => true,
);
$actions['RecentCommentsRSSLink'] = array(
    'layout' => true,
);
$actions['RecentCommentsAtomLink'] = array(
    'layout' => true,
);
$actions['ShowTagCloud'] = array(
    'layout' => true,
);
$actions['Trackback'] = array(
    'standalone' => true,
    'file' => 'Trackbacks',
);
$actions['Pingback'] = array(
    'standalone' => true,
    'file' => 'Pingback',
);
$actions['RSS'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['Atom'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['ShowRSSCategory'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['ShowAtomCategory'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['RecentCommentsRSS'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['RecentCommentsAtom'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['CommentsRSS'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['CommentsAtom'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
