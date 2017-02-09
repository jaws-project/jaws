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
class Contact_Model_Admin_Contacts extends Jaws_Gadget_Model
{
    /**
     * Get a list of the Contacts
     *
     * @access  public
     * @param   int     $recipient  Recipient ID
     * @param   int     $limit      Count of contacts to be returned
     * @param   int     $offset     offset of data array
     * @return  mixed   Array of Contacts or Jaws_Error on failure
     */
    function GetContacts($recipient = -1, $limit = false, $offset = null)
    {
        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $cntctTable->select(
            'id:integer', 'name', 'email', 'subject', 'attachment', 
            'recipient:integer', 'reply', 'createtime'
        );
        if ($recipient != -1) {
            $cntctTable->where('recipient', (int)$recipient);
        }
        $cntctTable->orderBy('id desc');
        if (is_numeric($limit)) {
            $cntctTable->limit($limit, $offset);
        }
        return $cntctTable->fetchAll();
    }

    /**
     * Gets contacts count
     *
     * @access  public
     * @param   int     $recipient      Recipient ID
     * @return  mixed   Count of available contacts and Jaws_Error on failure
     */
    function GetContactsCount($recipient = -1)
    {
        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $cntctTable->select('count([id]):integer');
        if ($recipient != -1) {
            $cntctTable->where('recipient', (int)$recipient);
        }
        return $cntctTable->fetchOne();
    }

    /**
     * Get information of a Contact Reply
     *
     * @access  public
     * @param   int     $id     Contact ID
     * @return  mixed   Array of Contact Reply Information or Jaws_Error on failure
     */
    function GetReply($id)
    {
        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $cntctTable->select(
            'id:integer', 'name', 'email', 'recipient:integer', 'subject', 
            'msg_txt', 'reply', 'reply_sent:integer', 'createtime'
        );
        return $cntctTable->where('id', $id)->fetchRow();
    }

    /**
     * Update contact information
     *
     * @access  public
     * @param   int     $id     Contact ID
     * @param   array   $data   Contact data
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateContact($id, $data)
    {
        $contact = $this->gadget->model->load('Contacts')->GetContact($id);
        if (Jaws_Error::IsError($contact) || empty($contact)) {
            return Jaws_Error::raiseError(
                _t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'),
                __FUNCTION__
            );
        }

        if (!$this->gadget->GetPermission('ManageRecipientContacts', $contact['recipient'])) {
            return Jaws_Error::raiseError(
                _t('CONTACT_ERROR_CONTACT_NOT_UPDATED'),
                __FUNCTION__
            );
        }

        $data['updatetime'] = Jaws_DB::getInstance()->date();
        return Jaws_ORM::getInstance()->table('contacts')->update($data)->where('id', $id)->exec();
    }

    /**
     * Delete a Contact
     *
     * @access  public
     * @param   string  $id  ID of the Contact
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteContact($id)
    {
        $contact = $this->gadget->model->load('Contacts')->GetContact($id);
        if (Jaws_Error::IsError($contact) || empty($contact)) {
            return Jaws_Error::raiseError(
                _t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'),
                __FUNCTION__
            );
        }

        if (!$this->gadget->GetPermission('ManageRecipientContacts', $contact['recipient'])) {
            return Jaws_Error::raiseError(
                _t('CONTACT_ERROR_CONTACT_NOT_DELETED'),
                __FUNCTION__
            );
        }

        return Jaws_ORM::getInstance()->delete()->table('contacts')->where('id', $id)->exec();
    }

}