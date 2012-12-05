<?php
/**
 * Forums Actions file
 *
 * @category    GadgetActions
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Forums'] = array('AdminAction:Forums');

/* Layout actions */
$index_actions['RecentPosts'] = array(
    'LayoutAction:RecentPosts',
    _t('FORUMS_LAYOUT_RECENT_POSTS'),
    _t('FORUMS_LAYOUT_RECENT_POSTS_DESC'),
    true
);
$index_actions['Forums']      = array('NormalAction:Forums');
$index_actions['Topics']      = array('NormalAction:Topics');
$index_actions['NewTopic']    = array('NormalAction:Topics');
$index_actions['EditTopic']   = array('NormalAction:Topics');
$index_actions['DeleteTopic'] = array('NormalAction:Topics');
$index_actions['UpdateTopic'] = array('StandaloneAction:Topics');
$index_actions['LockTopic']   = array('StandaloneAction:Topics');
$index_actions['Posts']       = array('NormalAction:Posts');
$index_actions['Post']        = array('NormalAction:Posts');
$index_actions['NewPost']     = array('NormalAction:Posts');
$index_actions['EditPost']    = array('NormalAction:Posts');
$index_actions['ReplyPost']   = array('NormalAction:Posts');
$index_actions['DeletePost']  = array('NormalAction:Posts');
$index_actions['UpdatePost']  = array('StandaloneAction:Posts');
$index_actions['Attachment']  = array('StandaloneAction:Attachment');
