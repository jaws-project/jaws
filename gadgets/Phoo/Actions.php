<?php
/**
 * Phoo Actions file
 *
 * @category    GadgetActions
 * @package     Phoo
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['PhotoblogPortrait'] = array(
    'normal' => true,
    'file' => 'Photoblog',
);
$actions['AlbumList'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Albums',
    'parametric' => true,
);
$actions['ViewAlbum'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['ViewAlbumPage'] = array(
    'normal' => true,
    'file' => 'Photos',
);
$actions['ViewImage'] = array(
    'normal' => true,
    'file' => 'Photos',
);
//$actions['Preview'] = array(
//    'normal' => true,
//    'file' => 'Comments',
//);

$actions['Random'] = array(
    'layout' => true,
    'file' => 'Random',
    'parametric' => true,
);
$actions['Moblog'] = array(
    'layout' => true,
    'file' => 'Moblog',
    'parametric' => true,
);