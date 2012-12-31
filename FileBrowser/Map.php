<?php
/**
 * FileBrowser URL maps
 *
 * @category   GadgetMaps
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DefaultAction', 'files');
$maps[] = array('Display',
                'files/{path}/page/{page}',
                '',
                array('path' => '.*',
                      'page' => '[[:digit:]]+$')
                );
$maps[] = array('Display',
                'files/{path}',
                '',
                array('path' => '.*')
                );
$maps[] = array('FileInfo', 
                'file/info/{id}',
                '',
                array('id' =>  '[[:alnum:][:space:][:punct:]]+$',)
                );
$maps[] = array('Download', 
                'download/{id}',
                '',
                array('id' =>  '[[:alnum:][:space:][:punct:]]+$',)
                );
