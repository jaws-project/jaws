<?php
/**
 * StaticPage URL maps
 *
 * @category   GadgetMaps
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DefaultAction', 'page/default');
$maps[] = array('PagesTree', 'page/index');
$maps[] = array('Page', 
                'page/{pid}/{language}',
                '',
                array('pid'      => '[\p{L}[:digit:]-_\.]+',
                      'language' => '[[:lower:]-]+',)
                );
$maps[] = array('Page', 
                'page/{pid}',
                '',
                array('pid' => '[\p{L}[:digit:]-_\.]+',)
                );
// new maps
$maps[] = array('Pages', 
                'pages/{gid}/{pid}/{language}',
                '',
                array('gid'      => '[\p{L}[:digit:]-_\.]+',
                      'pid'      => '[\p{L}[:digit:]-_\.]+',
                      'language' => '[[:lower:]-]+',)
                );
$maps[] = array('Pages', 
                'pages/{gid}/{pid}',
                '',
                array('gid' => '[\p{L}[:digit:]-_\.]+',
                      'pid' => '[\p{L}[:digit:]-_\.]+',)
                );
$maps[] = array('GroupPages',
                'pages/{gid}',
                '',
                array('gid' => '[\p{L}[:digit:]-_\.]+',)
                );
$maps[] = array('GroupsList', 'pages');
