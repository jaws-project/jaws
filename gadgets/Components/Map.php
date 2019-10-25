<?php
/**
 * Components URL maps
 *
 * @category    GadgetMaps
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'Version',
    'version/{type}[/{component}]',
    array(
        'type'=>'[[:digit:]]',
        'component' => '[[:alnum:]_]+'
    )
);
