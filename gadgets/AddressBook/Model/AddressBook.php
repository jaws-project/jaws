<?php
/**
 * AddressBook Gadget
 *
 * @category    GadgetModel
 * @package     AddressBook
 */
class AddressBook_Model_AddressBook extends Jaws_Gadget_Model
{
    /**
     * Gets a list of Address Books
     *
     * @access  public
     * @param   int     $user     User ID
     * @param   int     $gid      Group ID, AddressBook Items must be member of this Group ID
     * @param   boolean $public   If true show only public addressbooks
     * @param   string  $term     Search term
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddressList($user, $gid, $public = false, $term = '', $limit = null, $offset = null)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('*', 'address_book.id as address_id');
        $adrTable->where('address_book.user', $user);

        if ($public) {
            $adrTable->and()->where('address_book.public', true);
        }

        if (!empty($limit)) {
            $adrTable->limit($limit, $offset);
        }

        if (!empty($gid) && $gid != 0) {
            $adrTable->join('address_book_group', 'address_book_group.address', 'address_book.id', 'left');
            $adrTable->and()->where('address_book_group.group', $gid);
        }

        if (!empty($term)) {
            $term = Jaws_UTF8::strtolower($term);
            $adrTable->and()->openWhere('lower(name)',   $term, 'like');
            $adrTable->or()->where('lower(nickname)',    $term, 'like');
            $adrTable->or()->where('lower(title)',       $term, 'like');
            $adrTable->or()->where('lower(tel_home)',    $term, 'like');
            $adrTable->or()->where('lower(tel_work)',    $term, 'like');
            $adrTable->or()->where('lower(tel_other)',   $term, 'like');
            $adrTable->or()->where('lower(email_home)',  $term, 'like');
            $adrTable->or()->where('lower(email_work)',  $term, 'like');
            $adrTable->or()->where('lower(email_other)', $term, 'like');
            $adrTable->or()->closeWhere('lower(notes)',  $term, 'like');
        }

        return $adrTable->fetchAll();
    }

    /**
     * Gets info of selected Address Books
     *
     * @access  public
     * @param   array   $addresses  array of address for get info
     * @param   int     $user       User ID
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetAddresses($addresses, $user)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $adrTable->select('*', 'id as address_id');
        $adrTable->where('user', $user);
        $adrTable->and()->where('id', $addresses, 'in');

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
        $targetDir = JAWS_DATA. 'addressbook'. DIRECTORY_SEPARATOR;

        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        $insertResult = $adrTable->insert($data)->exec();

        if (array_key_exists('image', $data) && !Jaws_Error::IsError($insertResult) && !empty($data['image'])) {
            $fileinfo = pathinfo($data['image']);
            if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                if (!in_array($fileinfo['extension'], array('gif','jpg','jpeg','png'))) {
                    return false;
                } else {
                    if (!is_dir($targetDir)) {
                        Jaws_Utils::mkdir($targetDir);
                    }
                    $targetDir = $targetDir. 'image'. DIRECTORY_SEPARATOR;
                    if (!is_dir($targetDir)) {
                        Jaws_Utils::mkdir($targetDir);
                    }

                    $new_image = $insertResult . '.' . $fileinfo['extension'];
                    rename(Jaws_Utils::upload_tmp_dir(). '/'. $data['image'],
                            $targetDir. $new_image);
                    $data['image'] = $new_image;
                }
            }
        }

        if (!Jaws_Error::IsError($insertResult) && !empty($data['image'])) {
            $fileinfo = pathinfo($data['image']);
            if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                $new_image = $insertResult . '.'. $fileinfo['extension'];
                $adrTable->update(array('image' => $new_image))->where('id', (int) $insertResult)->exec();
            }
        }
        return $insertResult;
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
        $targetDir = JAWS_DATA. 'addressbook'. DIRECTORY_SEPARATOR;

        if (array_key_exists('image', $data)) {
            // get address information
            $adr = $this->GetAddressInfo((int)$id);
            if (Jaws_Error::IsError($adr) || empty($adr)) {
                return false;
            }

            if (!empty($adr['image'])) {
                Jaws_Utils::Delete($targetDir. 'image'. DIRECTORY_SEPARATOR . $adr['image']);
            }

            if (!empty($data['image'])) {
                $fileinfo = pathinfo($data['image']);
                if (isset($fileinfo['extension']) && !empty($fileinfo['extension'])) {
                    if (!in_array($fileinfo['extension'], array('gif','jpg','jpeg','png'))) {
                        return false;
                    } else {
                        if (!is_dir($targetDir)) {
                            Jaws_Utils::mkdir($targetDir);
                        }
                        $targetDir = $targetDir. 'image'. DIRECTORY_SEPARATOR;
                        if (!is_dir($targetDir)) {
                            Jaws_Utils::mkdir($targetDir);
                        }

                        $new_image = $adr['id']. '.'. $fileinfo['extension'];
                        rename(Jaws_Utils::upload_tmp_dir(). '/'. $data['image'],
                                $targetDir. $new_image);
                        $data['image'] = $new_image;
                    }
                }
            }
        }

        $adrTable = Jaws_ORM::getInstance()->table('address_book');
        return $adrTable->update($data)->where('id', (int) $id)->exec();
    }

    /**
     * Delete one address
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteAddress($address, $user)
    {
        $adrInfo = $this->GetAddressInfo($address);
        if (Jaws_Error::IsError($adrInfo) || empty($adrInfo)) {
            return;
        }
        // TODO: Use transaction
        $agModel = $this->gadget->model->load('AddressBookGroup');
        $agModel->DeleteGroupForAddress($address, $user);

        $aTable = Jaws_ORM::getInstance()->table('address_book');
        $result = $aTable->delete()->where('user', (int) $user)->and()->where('id', (int) $address)->exec();
        if (!Jaws_Error::IsError($result) && !empty($adrInfo['image']) && trim($adrInfo['image']) != '') {
            $targetDir = JAWS_DATA. 'addressbook'. DIRECTORY_SEPARATOR . 'image'. DIRECTORY_SEPARATOR;
            Jaws_Utils::Delete($targetDir . $adrInfo['image']);
        }
        return $result;
    }

    /**
     * Delete many address
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteAddressSection($addresses, $user)
    {
        // TODO: Use transaction
        $agModel = $this->gadget->model->load('AddressBookGroup');
        $agModel->DeleteGroupForAddresses($addresses, $user);

        $aTable = Jaws_ORM::getInstance()->table('address_book');
        $result = $aTable->delete()->where('user', (int) $user)->and()->where('id', $addresses, 'in')->exec();
        if (!Jaws_Error::IsError($result) && !empty($adrInfo['image']) && trim($adrInfo['image']) != '') {
            $targetDir = JAWS_DATA. 'addressbook'. DIRECTORY_SEPARATOR . 'image'. DIRECTORY_SEPARATOR;
            Jaws_Utils::Delete($targetDir . $adrInfo['image']);
        }
        return $result;
    }
}