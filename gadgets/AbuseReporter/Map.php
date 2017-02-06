<?php
/**
 * AbuseReporter URL maps
 *
 * @category    GadgetMaps
 * @package     AbuseReporter
 */

$maps[] = array(
    'Categories',
    'forms/categories',
);

$maps[] = array(
    'Forms',
    'forms[/categories/{cid}][/page/{page}][/order/{order}]',
    array(
        'cid' => '[\p{L}[:digit:]\-_\.]+',
        'page' => '[[:digit:]]+',
        'order' => '[[:digit:]]+',
    )
);