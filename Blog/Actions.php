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

$actions['LastPost']       = array('NormalAction');
$actions['ViewDatePage']   = array('NormalAction');
$actions['ViewPage']       = array('NormalAction');
$actions['ViewAuthorPage'] = array('NormalAction');
$actions['SingleView']     = array('NormalAction');
$actions['Comment']        = array('NormalAction');
$actions['Reply']          = array('NormalAction');
$actions['Preview']        = array('NormalAction');
$actions['SaveComment']    = array('NormalAction');
$actions['Archive']        = array('NormalAction');
$actions['ShowCategory']   = array('NormalAction');

$actions['CategoriesList']  = array('NormalAction, LayoutAction',
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

$actions['Summary']          = array('AdminAction');
$actions['NewEntry']         = array('AdminAction');
$actions['SaveNewEntry']     = array('AdminAction');
$actions['PreviewNewEntry']  = array('AdminAction');
$actions['ListEntries']      = array('AdminAction');
$actions['EditEntry']        = array('AdminAction');
$actions['PreviewEditEntry'] = array('AdminAction');
$actions['SaveEditEntry']    = array('AdminAction');
$actions['DeleteEntry']      = array('AdminAction');

$actions['UpdateCategory']   = array('AdminAction');
$actions['AddCategory']      = array('AdminAction');
$actions['EditCategory']     = array('AdminAction');
$actions['DeleteCategory']   = array('AdminAction');
$actions['ManageCategories'] = array('AdminAction');

$actions['ManageComments']   = array('AdminAction');
$actions['EditComment']      = array('AdminAction');
$actions['SaveEditComment']  = array('AdminAction');
$actions['DeleteComment']    = array('AdminAction');

$actions['ManageTrackbacks'] = array('AdminAction');
$actions['ViewTrackback']    = array('AdminAction');

$actions['AdditionalSettings']     = array('AdminAction');
$actions['SaveAdditionalSettings'] = array('AdminAction');

$actions['Trackback']          = array('StandaloneAction');
$actions['Pingback']           = array('StandaloneAction');
$actions['RSS']                = array('StandaloneAction');
$actions['Atom']               = array('StandaloneAction');
$actions['ShowRSSCategory']    = array('StandaloneAction');
$actions['ShowAtomCategory']   = array('StandaloneAction');
$actions['RecentCommentsRSS']  = array('StandaloneAction');
$actions['RecentCommentsAtom'] = array('StandaloneAction');
$actions['CommentsRSS']        = array('StandaloneAction');
$actions['CommentsAtom']       = array('StandaloneAction');
