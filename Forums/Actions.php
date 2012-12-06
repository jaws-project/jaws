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
$actions = array();

$actions['RecentPosts'] = array(
    'LayoutAction:RecentPosts',
    _t('FORUMS_LAYOUT_RECENT_POSTS'),
    _t('FORUMS_LAYOUT_RECENT_POSTS_DESC'),
    true
);
$actions['Forums'] = array('NormalAction:Forums');
$actions['Topics'] = array('NormalAction:Topics');
$actions['NewTopic'] = array('NormalAction:Topics');
$actions['EditTopic'] = array('NormalAction:Topics');
$actions['DeleteTopic'] = array('NormalAction:Topics');
$actions['UpdateTopic'] = array('StandaloneAction:Topics');
$actions['LockTopic'] = array('StandaloneAction:Topics');
$actions['Posts'] = array('NormalAction:Posts');
$actions['Post'] = array('NormalAction:Posts');
$actions['NewPost'] = array('NormalAction:Posts');
$actions['EditPost'] = array('NormalAction:Posts');
$actions['ReplyPost'] = array('NormalAction:Posts');
$actions['DeletePost'] = array('NormalAction:Posts');
$actions['UpdatePost'] = array('StandaloneAction:Posts');
$actions['Attachment'] = array('StandaloneAction:Attachment');
