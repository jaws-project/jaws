<?php
/**
 * AddressBook Gadget
 *
 * @category    GadgetModel
 * @package     AddressBook
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 */
class AddressBook_Model_AddressBookGroup extends Jaws_Gadget_Model
{
    /**
     * Get list of AddressBooks Groups
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetData($address, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        $agTable->select('*');
        $agTable->join('address_group', 'address_book_group.group', 'address_group.id');
        $agTable->where('address', $address)->and();
        return $agTable->where('address_book_group.user', $user)->fetchAll();
    }

    /**
     * Get list of GroupIDs AddressBooks Groups
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetGroupIDs($address, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->select('group')->where('address', $address)->and()->where('user', $user)->fetchAll();
    }

    /**
     * Get list of AddressBooks Groups
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressList($gid, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        $agTable->select('*');
        $agTable->join('address_book', 'address_book_group.address', 'address_book.id');
        $agTable->where('group', $gid)->and();
        return $agTable->where('address_book_group.user', $user)->fetchAll();
    }

    /**
     * Add relation between Group and AddressBook item
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function AddGroupToAddress($address, $group, $user)
    {
        $data['address']    = (int) $address;
        $data['group']      = (int) $group;
        $data['[user]']       = (int) $user;

        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->insert($data)->exec();
    }

    /**
     * Delete all group for one address
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteGroupForAddress($address, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->delete()->where('user', (int) $user)->and()->where('address', (int) $address)->exec();
    }
}