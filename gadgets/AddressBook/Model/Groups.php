<?php
/**
 * AddressBook Gadget
 *
 * @category    GadgetModel
 * @package     AddressBook
 */
class AddressBook_Model_Groups extends Jaws_Gadget_Model
{
    /**
     * Gets a list of Address Books Group
     *
     * @access  public
     * @param   array()     $user      list of Group ID, AddressBook Items must be member of one(minimum) Group ID has exist in this array
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetGroups($user)
    {
        $gTable = Jaws_ORM::getInstance()->table('address_group');
        $gTable->select('*');
        $gTable->where('user', $user);

        return $gTable->fetchAll();
    }

    /**
     * Gets info of selected Group
     *
     * @access  public
     * @param   integer     $gid      Group ID
     * @returns array of Address Books or Jaws_Error on error
     */
    function GetGroupInfo($gid)
    {
        $gTable = Jaws_ORM::getInstance()->table('address_group');
        $gTable->select('*')->where('id', (int) $gid);

        return $gTable->fetchRow();
    }

    /**
     * Insert New AddressBook Group Data to DB
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function InsertGroup($data)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_group');
        return $adrTable->insert($data)->exec();
    }

    /**
     * Uodate AddressBook Group Data to DB
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function UpdateGroup($gid, $data)
    {
        $adrTable = Jaws_ORM::getInstance()->table('address_group');
        return $adrTable->update($data)->where('id', $gid)->exec();
    }

    /**
     * Delete one group
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteGroup($group, $user)
    {
        $agModel = $this->gadget->model->load('AddressBookGroup');
        $agModel->DeleteAddressForGroup($group, $user);
        $aTable = Jaws_ORM::getInstance()->table('address_group');
        return $aTable->delete()->where('user', (int) $user)->and()->where('id', (int) $group)->exec();
    }

    /**
     * Delete many group
     *
     * @access  public
     * @returns array of Address Books or Jaws_Error on error
     */
    function DeleteGroups($groups, $user)
    {
        $agModel = $this->gadget->model->load('AddressBookGroup');
        $agModel->DeleteAddressForGroups($groups, $user);
        $aTable = Jaws_ORM::getInstance()->table('address_group');
        return $aTable->delete()->where('user', (int) $user)->and()->where('id', $groups, 'in')->exec();
    }
}
