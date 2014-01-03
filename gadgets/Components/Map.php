<?php
/**
 * Components URL maps
 *
 * @category    GadgetMaps
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
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
