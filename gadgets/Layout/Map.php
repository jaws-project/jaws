<?php
/**
 * Layout URL maps
 *
 * @category    GadgetMaps
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'LayoutType',
    'layout/switch/{type}'
);
$maps[] = array(
    'Layout',
    'layout[/{layout}][/theme/{theme}]',
    array(
        'layout' => '[[:alnum:]\_\.]+',
        'theme'  => '[[:alnum:]\-_\,]+',
    )
);
