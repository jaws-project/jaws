<?php
/**
 * Files URL maps
 *
 * @category    GadgetMaps
 * @package     Files
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2024 Jaws Development Group
 */
$maps[] = array(
    'file',
    'files/file/{id}-{key}',
    array(
        'id'  => '[[:digit:]]+',
        'key' => '[\p{L}[:digit:]\-_\.]+',
    ),
    '',
);
