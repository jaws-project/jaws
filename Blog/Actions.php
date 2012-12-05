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
$index_actions = array();
$admin_actions = array();

/* Admin actions*/
$admin_actions['Summary']          = array('AdminAction:Summary');
$admin_actions['NewEntry']         = array('AdminAction:Entries');
$admin_actions['SaveNewEntry']     = array('AdminAction:Entries');
$admin_actions['PreviewNewEntry']  = array('AdminAction:Entries');
$admin_actions['ListEntries']      = array('AdminAction:Entries');
$admin_actions['EditEntry']        = array('AdminAction:Entries');
$admin_actions['PreviewEditEntry'] = array('AdminAction:Entries');
$admin_actions['SaveEditEntry']    = array('AdminAction:Entries');
$admin_actions['DeleteEntry']      = array('AdminAction:Entries');
$admin_actions['UpdateCategory']   = array('AdminAction:Categories');
$admin_actions['AddCategory']      = array('AdminAction:Categories');
$admin_actions['EditCategory']     = array('AdminAction:Categories');
$admin_actions['DeleteCategory']   = array('AdminAction:Categories');
$admin_actions['ManageCategories'] = array('AdminAction:Categories');
$admin_actions['ManageComments']   = array('AdminAction:Comments');
$admin_actions['EditComment']      = array('AdminAction:Comments');
$admin_actions['SaveEditComment']  = array('AdminAction:Comments');
$admin_actions['DeleteComment']    = array('AdminAction:Comments');
$admin_actions['ManageTrackbacks'] = array('AdminAction:Trackbacks');
$admin_actions['ViewTrackback']    = array('AdminAction:Trackbacks');
$admin_actions['AdditionalSettings']     = array('AdminAction:Settings');
$admin_actions['SaveAdditionalSettings'] = array('AdminAction:Settings');

/* Index actions*/
$index_actions['LastPost']       = array('NormalAction:Post');
$index_actions['SingleView']     = array('NormalAction:Post');
$index_actions['ViewPage']       = array('NormalAction:Posts');
$index_actions['ViewDatePage']   = array('NormalAction:DatePosts');
$index_actions['ViewAuthorPage'] = array('NormalAction:AuthorPosts');
$index_actions['Comment']        = array('NormalAction:Comments');
$index_actions['Reply']          = array('NormalAction:Comments');
$index_actions['Preview']        = array('NormalAction:Comments');
$index_actions['SaveComment']    = array('NormalAction:Comments');
$index_actions['Archive']        = array('NormalAction:Archive');
$index_actions['ShowCategory']   = array('NormalAction:Categories');

$index_actions['EntriesByCategory'] = array(
    'LayoutAction',
    _t('BLOG_LAYOUT_ENTRIES_BY_CATEGORY'),
    _t('BLOG_LAYOUT_ENTRIES_BY_CATEGORY_DESC'),
    true
);
$index_actions['CategoriesList']  = array('NormalAction:Categories, LayoutAction',
                                    _t('BLOG_LAYOUT_CATEGORIES'),
                                    _t('BLOG_LAYOUT_CATEGORIES_DESC'));
$index_actions['PopularPosts']    = array('NormalAction, LayoutAction',
                                    _t('BLOG_LAYOUT_POPULAR_POSTS'),
                                    _t('BLOG_LAYOUT_POPULAR_POSTS_DESC'));
$index_actions['PostsAuthors']    = array('NormalAction, LayoutAction',
                                    _t('BLOG_LAYOUT_POSTS_AUTHORS'),
                                    _t('BLOG_LAYOUT_POSTS_AUTHORS_DESC'));

$index_actions['MonthlyHistory']         = array('LayoutAction', _t('BLOG_LAYOUT_MONTHLY'), 
                                                           _t('BLOG_LAYOUT_MONTHLY_DESC'));
$index_actions['RecentPosts']            = array('LayoutAction', _t('BLOG_LAYOUT_RECENT'),
                                                           _t('BLOG_LAYOUT_RECENT_DESC'));
$index_actions['RecentComments']         = array('LayoutAction', _t('BLOG_LAYOUT_RECENTCOMMENTS'),
                                                           _t('BLOG_LAYOUT_RECENTCOMMENTS_DESC'));
$index_actions['Calendar']               = array('LayoutAction', _t('BLOG_LAYOUT_CALENDAR'),
                                                           _t('BLOG_LAYOUT_CALENDAR_DESC'));
$index_actions['RSSLink']                = array('LayoutAction', _t('BLOG_LAYOUT_RSS'),
                                                           _t('BLOG_LAYOUT_RSS_DESC'));
$index_actions['AtomLink']               = array('LayoutAction', _t('BLOG_LAYOUT_ATOM'),
                                                           _t('BLOG_LAYOUT_ATOM_DESC'));
$index_actions['RecentCommentsRSSLink']  = array('LayoutAction', _t('BLOG_LAYOUT_COMMENTS_RSS'),
                                                           _t('BLOG_LAYOUT_COMMENTS_RSS_DESC'));
$index_actions['RecentCommentsAtomLink'] = array('LayoutAction', _t('BLOG_LAYOUT_COMMENTS_ATOM'),
                                                           _t('BLOG_LAYOUT_COMMENTS_ATOM_DESC'));
$index_actions['ShowTagCloud']           = array('LayoutAction', _t('BLOG_LAYOUT_TAGCLOUD'),
                                                           _t('BLOG_LAYOUT_TAGCLOUD_DESC'));

$index_actions['Trackback']          = array('StandaloneAction:Trackbacks');
$index_actions['Pingback']           = array('StandaloneAction:Pingback');
$index_actions['RSS']                = array('StandaloneAction:Feeds');
$index_actions['Atom']               = array('StandaloneAction:Feeds');
$index_actions['ShowRSSCategory']    = array('StandaloneAction:Feeds');
$index_actions['ShowAtomCategory']   = array('StandaloneAction:Feeds');
$index_actions['RecentCommentsRSS']  = array('StandaloneAction:Feeds');
$index_actions['RecentCommentsAtom'] = array('StandaloneAction:Feeds');
$index_actions['CommentsRSS']        = array('StandaloneAction:Feeds');
$index_actions['CommentsAtom']       = array('StandaloneAction:Feeds');
