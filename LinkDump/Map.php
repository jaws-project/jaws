<?php
/**
 * LinkDump URL maps
 *
 * @category   GadgetMaps
 * @package    LinkDump
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DefaultAction', 'links');
$maps[] = array('DefaultAction', 'links/groups');
$maps[] = array(
    'Archive',
    'links/archive/{id}',
    array('id' =>  '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Group',
    'links/group/{id}',
    array('id' =>  '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Tag',
    'links/tag/{tag}',
    array('tag' =>  '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Link', 
    'links/{id}',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'RSS',
    'links/{id}/rss',
    array('id' => '[\p{L}[:digit:]-_\.]+',),
    'xml'
);