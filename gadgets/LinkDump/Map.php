<?php
/**
 * LinkDump URL maps
 *
 * @category   GadgetMaps
 * @package    LinkDump
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('Categories', 'links');
$maps[] = array('Categories', 'links/groups');
$maps[] = array(
    'Category',
    'links/groups/{id}',
    array('id' =>  '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'Link', 
    'links/{id}',
    array('id' => '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'RSS',
    'links/{id}/rss',
    array('id' => '[\p{L}[:digit:]\-_\.]+',),
    'xml'
);