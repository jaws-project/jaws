<?php
/**
 * AddressBook Actions file
 *
 * @category   GadgetActions
 * @package    AddressBook
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['AddressBook'] = array(
    'normal' => true,
    'file'   => 'AddressBook',
);
$actions['AddAddress'] = array(
    'normal' => true,
    'file'   => 'AddressBook',
);
$actions['EditAddress'] = array(
    'normal' => true,
    'file'   => 'AddressBook',
);
$actions['View'] = array(
    'normal' => true,
    'file'   => 'AddressBook',
);
$actions['InsertAddress'] = array(
    'stanalone' => true,
    'file'   => 'AddressBook',
);
$actions['UpdateAddress'] = array(
    'stanalone' => true,
    'file'   => 'AddressBook',
);
$actions['DeleteAddress'] = array(
    'stanalone' => true,
    'file'   => 'AddressBook',
);
$actions['ManageGroups'] = array(
    'normal' => true,
    'file'   => 'Groups',
);
$actions['AddGroup'] = array(
    'normal' => true,
    'file'   => 'Groups',
);
$actions['EditGroup'] = array(
    'normal' => true,
    'file'   => 'Groups',
);
$actions['Groups'] = array(
    'layout' => true,
    'file'   => 'Groups',
);
$actions['InsertGroup'] = array(
    'stanalone' => true,
    'file'   => 'Groups',
);
$actions['UpdateGroup'] = array(
    'stanalone' => true,
    'file'   => 'Groups',
);
$actions['DeleteGroup'] = array(
    'stanalone' => true,
    'file'   => 'Groups',
);
$actions['GroupMembers'] = array(
    'normal' => true,
    'file'   => 'AddressBookGroup',
);