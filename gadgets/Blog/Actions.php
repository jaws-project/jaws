<?php
/**
 * Blog Actions file
 *
 * @category    GadgetActions
 * @package     Blog
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['DefaultAction'] = array(
    'normal' => true,
);
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
$actions['Preview'] = array(
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

$actions['CategoryEntries'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Posts',
);
$actions['CategoriesList'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Categories',
);
$actions['PopularPosts'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Posts',
);
$actions['PostsAuthors'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Posts',
);
$actions['MonthlyHistory'] = array(
    'layout' => true,
    'file'   => 'DatePosts',
);
$actions['RecentPosts'] = array(
    'layout' => true,
    'file'   => 'Posts',
);
$actions['Calendar'] = array(
    'layout' => true,
    'file'   => 'DatePosts',
);
$actions['FeedsLink'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feeds',
);
$actions['ShowTagCloud'] = array(
    'layout' => true,
    'file'   => 'Tags',
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