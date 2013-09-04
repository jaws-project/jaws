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
     * @param   array()     $gid      list of Group ID, AddressBook Items must be member of one(minimum) Group ID has exist in this array
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressList($user, $gid, $public = false, $limit, $offset = null)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('*');
        $adrTable->where('address_book.user', $user)->and();

        if ($public) {
            $adrTable->where('address_book.public', true)->and();
        }

        if (!empty($limit)) {
            $adrTable->limit($limit, $offset);
        }

        if (!empty($gid) && count($gid) > 0) {
            $adrTable->join('address_book_group', 'address_book_group.address', 'address_book.id', 'left');
            $adrTable->where('address_book_group.group', $gid, 'in');
        }

        return $adrTable->fetchAll();
    }

    /**
     * Gets count of Address Books
     *
     * @access  public
     * @param   array()     $gid      list of Group ID, AddressBook Items must be member of one(minimum) Group ID has exist in this array
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressListCount($user, $gid, $public = false)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('count(address_book.id) as address_count:integer');
        $adrTable->where('address_book.user', $user)->and();

        if ($public) {
            $adrTable->where('address_book.public', true)->and();
        }

        if (!empty($gid) && count($gid) > 0) {
            $adrTable->join('address_book_group', 'address_book_group.address', 'address_book.id', 'left');
            $adrTable->where('address_book_group.group', $gid, 'in');
        }

        return $adrTable->fetchOne();
    }

    /**
     * Gets info of selected Address Book
     *
     * @access  public
     * @param   int     $id      Index of Address Book for show info
     * @returns array of Selected Address Book Info or Jaws_Error on error
     */
    function GetAddressInfo($id)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('*')->where('id', $id);
        return $adrTable->fetchRow();
    }

    /**
     * Insert New AddressBook Data to DB
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function InsertAddress($data)
    {
        $data['public']         = (bool) $data['public'];
        $data['createtime']     = time();
        $data['updatetime']     = time();

        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        return $adrTable->insert($data)->exec();
    }

    /**
     * Insert New AddressBook Data to DB
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function UpdateAddress($id, $data)
    {
        $data['public']         = (bool) $data['public'];
        $data['updatetime']     = time();

        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        return $adrTable->update($data)->where('id', (int) $id)->exec();
    }
}








