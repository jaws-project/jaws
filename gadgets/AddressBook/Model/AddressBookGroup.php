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
        return $agTable->select('group')->where('address', $address)->and()->where('user', $user)->fetchColumn();
    }

    /**
     * Get list of Group Names AddressBooks Groups
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetGroupNames($address, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        $agTable->join('address_group', 'address_book_group.group', 'address_group.id');
        return $agTable->select('name')->where('address', $address)->and()->where('address_group.user', $user)->fetchColumn();
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
        $data['[user]']     = (int) $user;

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

    /**
     * Delete all group for many address
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteGroupForAddresses($addresses, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->delete()->where('user', (int) $user)->and()->where('address', $addresses, 'in')->exec();
    }

    /**
     * Delete all address for one group
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteAddressForGroup($group, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->delete()->where('user', (int) $user)->and()->where('group', (int) $group)->exec();
    }

    /**
     * Delete all address for many group
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteAddressForGroups($groups, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        return $agTable->delete()->where('user', (int) $user)->and()->where('group', $groups, 'in')->exec();
    }

    /**
     * Delete one address for one group
     *
     * @access  public
     * @param   int     $address    Address ID
     * @param   int     $group      Group ID
     * @param   int     $user       User ID
     * @returns boolean of delete result or Jaws_Error on error
     */
    function DeleteAddressBookGroup($address, $group, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        $agTable->delete()->where('user', (int) $user);
        $agTable->and()->where('group', (int) $group);
        return $agTable->and()->where('address', (int) $address)->exec();
    }

    /**
     * Get list of AddressBooks Is Not To Selecred Group
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   int     $user       User ID
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressListNotInGroup($gid, $user)
    {
        $agTable = Jaws_ORM::getInstance()->table('address_book_group');
        $agTable->select('address')->where('group', $gid);
        $result = $agTable->and()->where('address_book_group.user', $user)->fetchColumn();
        if (Jaws_Error::IsError($addressItems)) {
            return $result;
        } else if (is_array($result) && count($result) > 0) {
            $agTable->where('id', $result, 'not in')->and()->where('user', $user);
        } else {
            $agTable->where('user', $user);
        }
        $agTable->table('address_book')->select('*');
        return $agTable->fetchAll();
    }
}