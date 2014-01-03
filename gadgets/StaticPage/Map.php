<?php
/**
 * StaticPage URL maps
 *
 * @category   GadgetMaps
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('GroupsList', 'pages');
$maps[] = array('Page', 'page/default');
$maps[] = array('PagesTree', 'page/index');
$maps[] = array(
    'Page', 
    'page/{pid}[/{language}]',
     array(
        'pid'      => '[\p{L}[:digit:]-_\.]+',
        'language' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'GroupPages',
    'pages/{gid}[/order/{order}]',
    array(
        'gid' => '[\p{L}[:digit:]-_\.]+',
        'order' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Pages', 
    'pages/{gid}/{pid}[/{language}]',
    array(
        'gid'      => '[\p{L}[:digit:]-_\.]+',
        'pid'      => '[\p{L}[:digit:]-_\.]+',
        'language' => '[[:lower:]-]+',
    )
);
