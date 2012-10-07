<?php
/**
 * StaticPage URL maps
 *
 * @category   GadgetMaps
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DefaultAction', 'page/default');
$maps[] = array('PagesTree', 'page/index');
$maps[] = array('Page', 
                'page/{pid}/{language}',
                '',
                array('pid'      => '[[:alnum:][:space:][:punct:]]+',
                      'language' => '[[:lower:]-]+$',)
                );
$maps[] = array('Page', 
                'page/{pid}',
                '',
                array('pid' => '[[:alnum:][:space:][:punct:]]+$',)
                );
// new maps
$maps[] = array('Pages', 
                'pages/{gid}/{pid}/{language}',
                '',
                array('gid'      => '[[:alnum:][:space:][:punct:]]+',
                      'pid'      => '[[:alnum:][:space:][:punct:]]+',
                      'language' => '[[:lower:]-]+$',)
                );
$maps[] = array('Pages', 
                'pages/{gid}/{pid}',
                '',
                array('gid' => '[[:alnum:][:space:][:punct:]]+',
                      'pid' => '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('GroupPages',
                'pages/{gid}',
                '',
                array('gid' => '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('GroupsList', 'pages');
