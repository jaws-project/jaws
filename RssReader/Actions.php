<?php
/**
 * RssReader Actions file
 *
 * @category   GadgetActions
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();

/* Layout actions */
$index_actions['Display'] = array(
    'LayoutAction',
    _t('RSSREADER_LAYOUT_SHOW_TITLES'),
    _t('RSSREADER_LAYOUT_SHOW_TITLES_DESCRIPTION'),
    true
);

/* Normal actions*/
$index_actions['GetFeed'] = array('NormalAction');
