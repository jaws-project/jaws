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
    'NormalAction:Post'
);
$actions['SingleView'] = array(
    'NormalAction:Post'
);
$actions['ViewPage'] = array(
    'NormalAction:Posts'
);
$actions['ViewDatePage'] = array(
    'NormalAction:DatePosts'
);
$actions['ViewAuthorPage'] = array(
    'NormalAction:AuthorPosts'
);
$actions['Comment'] = array(
    'NormalAction:Comments'
);
$actions['Reply'] = array(
    'NormalAction:Comments'
);
$actions['Preview'] = array(
    'NormalAction:Comments'
);
$actions['SaveComment'] = array(
    'NormalAction:Comments'
);
$actions['Archive'] = array(
    'NormalAction:Archive'
);
$actions['ShowCategory'] = array(
    'NormalAction:Categories'
);

$actions['EntriesByCategory'] = array(
    'layout' => true,
    true
);
$actions['CategoriesList'] = array(
    'NormalAction:Categories,LayoutAction',
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
    'StandaloneAction:Trackbacks'
);
$actions['Pingback'] = array(
    'StandaloneAction:Pingback'
);
$actions['RSS'] = array(
    'StandaloneAction:Feeds'
);
$actions['Atom'] = array(
    'StandaloneAction:Feeds'
);
$actions['ShowRSSCategory'] = array(
    'StandaloneAction:Feeds'
);
$actions['ShowAtomCategory'] = array(
    'StandaloneAction:Feeds'
);
$actions['RecentCommentsRSS'] = array(
    'StandaloneAction:Feeds'
);
$actions['RecentCommentsAtom'] = array(
    'StandaloneAction:Feeds'
);
$actions['CommentsRSS'] = array(
    'StandaloneAction:Feeds'
);
$actions['CommentsAtom'] = array(
    'StandaloneAction:Feeds'
);
