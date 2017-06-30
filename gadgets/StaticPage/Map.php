<?php
/**
 * StaticPage URL maps
 *
 * @category   GadgetMaps
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('PagesTree', 'pages');
$maps[] = array('GroupsList', 'pages/groups');
$maps[] = array(
    'Page', 
    'pages/page[/{pid}][/{language}]',
     array(
        'pid'      => '[\p{L}[:digit:]\-_\.]+',
        'language' => '[[:lower:]\-]+',
    )
);
$maps[] = array(
    'GroupPages',
    'pages/groups/{gid}[/order/{order}]',
    array(
        'gid' => '[\p{L}[:digit:]\-_\.]+',
        'order' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Pages', 
    'pages/groups/{gid}/{pid}[/{language}]',
    array(
        'gid'      => '[\p{L}[:digit:]\-_\.]+',
        'pid'      => '[\p{L}[:digit:]\-_\.]+',
        'language' => '[[:lower:]\-]+',
    )
);
