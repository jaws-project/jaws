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
$maps[] = array('Archive',
                'links/archive/{id}',
                '',
                array('id' =>  '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('Group',
                'links/group/{id}',
                '',
                array('id' =>  '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('Tag',
                'links/tag/{tag}',
                '',
                array('tag' =>  '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('Link', 
                'links/{id}',
                '',
                array('id' => '[[:alnum:][:space:][:punct:]]+$',)
                );
