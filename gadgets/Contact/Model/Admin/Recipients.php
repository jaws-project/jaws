<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Model_Admin_Recipients extends Jaws_Gadget_Model
{
    /**
     * Insert the information of a Recipient
     *
     * @access  public
     * @param   array   $data   Recipient data array
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRecipient($data)
    {
        $result = Jaws_ORM::getInstance()->table('contacts_recipients')->insert($data)->exec();
        if (!Jaws_Error::IsError($result)) {
            $this->gadget->acl->insert('ManageRecipientContacts', $result, false);
        }

        return $result;
    }

    /**
     * Update the information of a Recipient
     *
     * @access  public
     * @param   string  $id     ID of the recipient
     * @param   array   $data   Recipient data array
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRecipient($id, $data)
    {
        $rcptTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        return $rcptTable->update($data)->where('id', (int)$id)->exec();
    }

    /**
     * Delete a Recipient
     *
     * @access  public
     * @param   string  $id  ID of the Recipient
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteRecipient($id)
    {
        $result = Jaws_ORM::getInstance()->table('contacts_recipients')->delete()->where('id', (int)$id)->exec();
        if (!Jaws_Error::IsError($result)) {
            $this->gadget->acl->delete('ManageRecipientContacts', $id);
        }

        return $result;
    }

}