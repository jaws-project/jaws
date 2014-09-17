<?php
/**
 * AddressBook URL maps
 *
 * @category   GadgetMaps
 * @package    AddressBook
 */
$maps[] = array('AddressBook',  'addressbook');
$maps[] = array('AddAddress',   'addressbook/new');
$maps[] = array('View',         'addressbook/view/{id}', array('id'   => '[[:digit:]]+'));
$maps[] = array('EditAddress',  'addressbook/edit/{id}', array('id'   => '[[:digit:]]+'));
$maps[] = array('VCardImport',  'addressbook/import');
$maps[] = array('ManageGroups', 'addressbook/groups');
$maps[] = array('AddGroup',     'addressbook/groups/new');
$maps[] = array('EditGroup',     'addressbook/groups/edit/{id}', array('id'   => '[[:digit:]]+'));
$maps[] = array('GroupMembers', 'addressbook/groups/members/{id}', array('id'   => '[[:digit:]]+'));
$maps[] = array(
    'UserAddress',
    'addressbook[/{uid}][/page/{page}]',
    array(
        'uid'  => '[\p{L}[:digit:]\-_\.]+',
        'page' => '[[:digit:]]+',
    )
);
