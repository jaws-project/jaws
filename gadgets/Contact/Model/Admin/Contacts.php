<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
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
     * @param   int     $id         Contact ID
     * @param   string  $name       Name
     * @param   string  $email      Email address
     * @param   string  $$company
     * @param   string  $url
     * @param   string  $tel
     * @param   string  $fax
     * @param   string  $mobile
     * @param   string  $address
     * @param   int     $recipient  Recipient ID
     * @param   string  $subject    Subject of message
     * @param   string  $message    Message content
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateContact($id, $name, $email, $company, $url, $tel, $fax, $mobile, $address, $recipient, $subject, $message)
    {
        $data = array();
        $data['name']       = $name;
        $data['email']      = $email;
        $data['company']    = $company;
        $data['url']        = $url;
        $data['tel']        = $tel;
        $data['fax']        = $fax;
        $data['mobile']     = $mobile;
        $data['address']    = $address;
        $data['recipient']  = (int)$recipient;
        $data['subject']    = $subject;
        $data['msg_txt']    = $message;
        $data['updatetime'] = $GLOBALS['db']->Date();

        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $result = $cntctTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error($result->GetMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_CONTACTS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update contact reply
     *
     * @access  public
     * @param   int     $id     Contact ID
     * @param   string  $reply  Reply content
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateReply($id, $reply)
    {
        $data               = array();
        $data['reply']      = $reply;
        $data['updatetime'] = $GLOBALS['db']->Date();

        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $result = $cntctTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_REPLY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($result->GetMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_REPLY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update reply send field
     *
     * @access  public
     * @param   int     $id         Contact ID
     * @param   int     $reply_sent
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateReplySent($id, $reply_sent)
    {
        $data = array();
        $data['reply_sent'] = (int)$reply_sent;
        $data['updatetime'] = $GLOBALS['db']->Date();

        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $result = $cntctTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_REPLY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($result->GetMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_REPLY_UPDATED'), RESPONSE_NOTICE);
        return true;
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
        $objORM = Jaws_ORM::getInstance();
        $result = $objORM->delete()->table('contacts')->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_CONTACT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_CONTACTS_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}
