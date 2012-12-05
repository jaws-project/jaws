<?php
/**
 * Blog Actions file
 *
 * @category   GadgetActions
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['LastPost']       = array('NormalAction:Post');
$actions['SingleView']     = array('NormalAction:Post');
$actions['ViewPage']       = array('NormalAction:Posts');
$actions['ViewDatePage']   = array('NormalAction:DatePosts');
$actions['ViewAuthorPage'] = array('NormalAction:AuthorPosts');
$actions['Comment']        = array('NormalAction:Comments');
$actions['Reply']          = array('NormalAction:Comments');
$actions['Preview']        = array('NormalAction:Comments');
$actions['SaveComment']    = array('NormalAction:Comments');
$actions['Archive']        = array('NormalAction:Archive');
$actions['ShowCategory']   = array('NormalAction:Categories');

$actions['EntriesByCategory'] = array(
    'LayoutAction',
    _t('BLOG_LAYOUT_ENTRIES_BY_CATEGORY'),
    _t('BLOG_LAYOUT_ENTRIES_BY_CATEGORY_DESC'),
    true
);
$actions['CategoriesList']  = array('NormalAction:Categories, LayoutAction',
                                    _t('BLOG_LAYOUT_CATEGORIES'),
                                    _t('BLOG_LAYOUT_CATEGORIES_DESC'));
$actions['PopularPosts']    = array('NormalAction, LayoutAction',
                                    _t('BLOG_LAYOUT_POPULAR_POSTS'),
                                    _t('BLOG_LAYOUT_POPULAR_POSTS_DESC'));
$actions['PostsAuthors']    = array('NormalAction, LayoutAction',
                                    _t('BLOG_LAYOUT_POSTS_AUTHORS'),
                                    _t('BLOG_LAYOUT_POSTS_AUTHORS_DESC'));

$actions['MonthlyHistory']         = array('LayoutAction', _t('BLOG_LAYOUT_MONTHLY'), 
                                                           _t('BLOG_LAYOUT_MONTHLY_DESC'));
$actions['RecentPosts']            = array('LayoutAction', _t('BLOG_LAYOUT_RECENT'),
                                                           _t('BLOG_LAYOUT_RECENT_DESC'));
$actions['RecentComments']         = array('LayoutAction', _t('BLOG_LAYOUT_RECENTCOMMENTS'),
                                                           _t('BLOG_LAYOUT_RECENTCOMMENTS_DESC'));
$actions['Calendar']               = array('LayoutAction', _t('BLOG_LAYOUT_CALENDAR'),
                                                           _t('BLOG_LAYOUT_CALENDAR_DESC'));
$actions['RSSLink']                = array('LayoutAction', _t('BLOG_LAYOUT_RSS'),
                                                           _t('BLOG_LAYOUT_RSS_DESC'));
$actions['AtomLink']               = array('LayoutAction', _t('BLOG_LAYOUT_ATOM'),
                                                           _t('BLOG_LAYOUT_ATOM_DESC'));
$actions['RecentCommentsRSSLink']  = array('LayoutAction', _t('BLOG_LAYOUT_COMMENTS_RSS'),
                                                           _t('BLOG_LAYOUT_COMMENTS_RSS_DESC'));
$actions['RecentCommentsAtomLink'] = array('LayoutAction', _t('BLOG_LAYOUT_COMMENTS_ATOM'),
                                                           _t('BLOG_LAYOUT_COMMENTS_ATOM_DESC'));
$actions['ShowTagCloud']           = array('LayoutAction', _t('BLOG_LAYOUT_TAGCLOUD'),
                                                           _t('BLOG_LAYOUT_TAGCLOUD_DESC'));

$actions['Summary']          = array('AdminAction:Summary');
$actions['NewEntry']         = array('AdminAction:Entries');
$actions['SaveNewEntry']     = array('AdminAction:Entries');
$actions['PreviewNewEntry']  = array('AdminAction:Entries');
$actions['ListEntries']      = array('AdminAction:Entries');
$actions['EditEntry']        = array('AdminAction:Entries');
$actions['PreviewEditEntry'] = array('AdminAction:Entries');
$actions['SaveEditEntry']    = array('AdminAction:Entries');
$actions['DeleteEntry']      = array('AdminAction:Entries');

$actions['UpdateCategory']   = array('AdminAction:Categories');
$actions['AddCategory']      = array('AdminAction:Categories');
$actions['EditCategory']     = array('AdminAction:Categories');
$actions['DeleteCategory']   = array('AdminAction:Categories');
$actions['ManageCategories'] = array('AdminAction:Categories');

$actions['ManageComments']   = array('AdminAction:Comments');
$actions['EditComment']      = array('AdminAction:Comments');
$actions['SaveEditComment']  = array('AdminAction:Comments');
$actions['DeleteComment']    = array('AdminAction:Comments');

$actions['ManageTrackbacks'] = array('AdminAction:Trackbacks');
$actions['ViewTrackback']    = array('AdminAction:Trackbacks');

$actions['AdditionalSettings']     = array('AdminAction:Settings');
$actions['SaveAdditionalSettings'] = array('AdminAction:Settings');

$actions['Trackback']          = array('StandaloneAction:Trackbacks');
$actions['Pingback']           = array('StandaloneAction:Pingback');
$actions['RSS']                = array('StandaloneAction:Feeds');
$actions['Atom']               = array('StandaloneAction:Feeds');
$actions['ShowRSSCategory']    = array('StandaloneAction:Feeds');
$actions['ShowAtomCategory']   = array('StandaloneAction:Feeds');
$actions['RecentCommentsRSS']  = array('StandaloneAction:Feeds');
$actions['RecentCommentsAtom'] = array('StandaloneAction:Feeds');
$actions['CommentsRSS']        = array('StandaloneAction:Feeds');
$actions['CommentsAtom']       = array('StandaloneAction:Feeds');
