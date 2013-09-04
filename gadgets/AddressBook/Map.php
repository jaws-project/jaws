<?php
/**
 * AddressBook URL maps
 *
 * @category   GadgetMaps
 * @package    AddressBook
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
$maps[] = array('AddressList', 'addressbook[/{uid}][/page/{page}]',
                                array('uid'   => '[\p{L}[:digit:]-_\.]+',
                                'page' => '[[:digit:]]+',));