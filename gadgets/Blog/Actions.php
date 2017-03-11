<?php
/**
 * Blog Actions file
 *
 * @category    GadgetActions
 * @package     Blog
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['DefaultAction'] = array(
    'normal' => true,
    'file'   => 'Default',
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
$actions['TypePosts'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'TypePosts',
);
$actions['CategoriesList'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Categories',
);
$actions['PopularPosts'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
    'file'   => 'PopularPosts',
);
$actions['FavoritePosts'] = array(
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

/**
 * Admin actions
 */
$admin_actions['Summary'] = array(
    'normal' => true,
    'file'   => 'Summary',
);
$admin_actions['NewEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['SaveNewEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['PreviewNewEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['ListEntries'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['EditEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['PreviewEditEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['SaveEditEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['DeleteEntry'] = array(
    'normal' => true,
    'file'   => 'Entries',
);
$admin_actions['UpdateCategory'] = array(
    'normal' => true,
    'file'   => 'Categories',
);
$admin_actions['AddCategory'] = array(
    'normal' => true,
    'file'   => 'Categories',
);
$admin_actions['EditCategory'] = array(
    'normal' => true,
    'file'   => 'Categories',
);
$admin_actions['DeleteCategory'] = array(
    'normal' => true,
    'file'   => 'Categories',
);
$admin_actions['ManageCategories'] = array(
    'normal' => true,
    'file'   => 'Categories',
);
$admin_actions['ManageComments'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$admin_actions['ManageTrackbacks'] = array(
    'normal' => true,
    'file'   => 'Trackbacks',
);
$admin_actions['ViewTrackback'] = array(
    'normal' => true,
    'file'   => 'Trackbacks',
);
$admin_actions['AdditionalSettings'] = array(
    'normal' => true,
    'file'   => 'Settings',
);
$admin_actions['SaveAdditionalSettings'] = array(
    'normal' => true,
    'file'   => 'Settings',
);
$admin_actions['ParseText'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SearchPosts'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SizeOfSearch'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetCategory'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetCategories'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddCategory2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateCategory2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteCategory2'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SearchTrackbacks'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SizeOfTrackbacksSearch'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteEntries'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ChangeEntryStatus'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteTrackbacks'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['TrackbackMarkAs'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AutoDraft'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['Types'] = array(
    'normal' => true,
    'file'   => 'Types',
);
$admin_actions['UploadImage'] = array(
    'standalone' => true,
    'file'   => 'Categories',
);
