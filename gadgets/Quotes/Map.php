<?php
/**
 * Quotes URL maps
 *
 * @category   GadgetMaps
 * @package    Quotes
 */
$maps[] = array('quotes', 'quotes[/categories/{category}]');
$maps[] = array('groups', 'quotes/categories');
$maps[] = array('quote', 'quotes/{id}[/{metaurl}]');
