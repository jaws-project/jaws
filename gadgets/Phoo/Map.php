<?php
/**
 * Phoo URL maps
 *
 * @category   GadgetMaps
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'AlbumList',
    'photos[/group/{group}]',
);
$maps[] = array(
    'ViewAlbum',
    'photos/album/{id}',
);
$maps[] = array(
    'ViewAlbumPage',
    'photos/album/{id}/page/{page}',
);
$maps[] = array(
    'ViewImage',
    'photos/album/{albumid}/photo/{id}[/page/{page}][/order/{order}]',
);
$maps[] = array(
    'Reply',
    'photos/reply/{id}/photo/{photoid}/album/{albumid}',
);
$maps[] = array(
    'PhotoblogPortrait',
    'photoblog/{photoid}',
);
$maps[] = array(
    'PhotoblogPortrait',
    'photoblog',
);
