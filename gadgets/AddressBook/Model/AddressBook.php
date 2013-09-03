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
    function GetAddressList($gid)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('*');

        if (!empty($gid) && count($gid) > 0) {
            $adrTable->join('address_book_group', 'address_book_group.address', 'address_book.id', 'left');
            $adrTable->where('address_book_group.group', $gid, 'in');
        }

        return $adrTable->fetchAll();
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
    function InsertAddress($user, $name, $company, $title, $email, $phone, $mobile, $fax, $address, $postal_code, $url, $notes, $public)
    {
        $data['[user]']         = $user;
        $data['name']           = $name;
        $data['company']        = $company;
        $data['title']          = $title;
        $data['email']          = $email;
        $data['phone_number']   = $phone;
        $data['mobile_number']  = $mobile;
        $data['fax_number']     = $fax;
        $data['address']        = $address;
        $data['postal_code']    = $postal_code;
        $data['url']            = $url;
        $data['notes']          = $notes;
        $data['public']         = $public;
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
    function UpdateAddress($id, $name, $company, $title, $email, $phone, $mobile, $fax, $address, $postal_code, $url, $notes, $public)
    {
        $data['name']           = $name;
        $data['company']        = $company;
        $data['title']          = $title;
        $data['email']          = $email;
        $data['phone_number']   = $phone;
        $data['mobile_number']  = $mobile;
        $data['fax_number']     = $fax;
        $data['address']        = $address;
        $data['postal_code']    = $postal_code;
        $data['url']            = $url;
        $data['notes']          = $notes;
        $data['public']         = $public;
        $data['updatetime']     = time();

        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        return $adrTable->update($data)->where('id', (int) $id)->exec();
    }
}








