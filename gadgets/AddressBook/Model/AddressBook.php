<?php
/**
 * AddressBook Gadget
 *
 * @category    GadgetModel
 * @package     AddressBook
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 */
class AddressBook_Model_AddressBook extends Jaws_Gadget_Model
{
    /**
     * Gets a list of Address Books
     *
     * @access  public
     * $gid     Array() list of Group ID, AddressBook Items must be member of one(minimum) Group ID has exist in this array
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressList($gid)
    {
        $catTable = Jaws_ORM::getInstance()->table('address_book');
        $catTable->select('*');

        if (!empty($gid) && count($gid) > 0) {
            $catTable->join('address_book_group', 'address_book_group.address', 'address_book.id', 'left');
            $catTable->where('address_book_group.group', $gid, 'in');
        }

        return $catTable->fetchAll();
    }
}