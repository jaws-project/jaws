<?php
require_once JAWS_PATH . 'gadgets/Contact/Model.php';
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
class Contact_AdminModel extends Contact_Model
{
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
        return $cntctTable->where('id', $id)->getRow();
    }

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
        $cntctTable->orderBy('id DESC');
        if (is_numeric($limit)) {
            $cntctTable->limit($limit, $offset);
        }
        return $cntctTable->getAll();
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
        return $cntctTable->getOne();
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
            return new Jaws_Error($result->GetMessage(), _t('CONTACT_NAME'));
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
            return new Jaws_Error($result->GetMessage(), _t('CONTACT_NAME'));
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
        $data['now']        = $GLOBALS['db']->Date();

        $cntctTable = Jaws_ORM::getInstance()->table('contacts');
        $result = $cntctTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_REPLY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($result->GetMessage(), _t('CONTACT_NAME'));
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
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_NOT_DELETED'), _t('CONTACT_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_CONTACTS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Insert the information of a Recipient
     *
     * @access  public
     * @param   string  $name           Name of the recipient
     * @param   string  $email          Email of recipient
     * @param   string  $tel            Phone number of recipient
     * @param   string  $fax            Fax number of recipient
     * @param   string  $mobile         Mobile number of recipient
     * @param   string  $inform_type
     * @param   string  $visible        The visible of the recipient
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible)
    {
        $data = array();
        $data['name']        = $name;
        $data['email']       = $email;
        $data['tel']         = $tel;
        $data['fax']         = $fax;
        $data['mobile']      = $mobile;
        $data['inform_type'] = (int)$inform_type;
        $data['visible']     = (int)$visible;

        $rcptTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $result = $rcptTable->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_ADDED'),_t('CONTACT_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update the information of a Recipient
     *
     * @access  public
     * @param   string  $id             ID of the recipient
     * @param   string  $name           Name of the recipient
     * @param   string  $email          Email of recipient
     * @param   string  $tel            Phone number of recipient
     * @param   string  $fax            Fax number of recipient
     * @param   string  $mobile         Mobile number of recipient
     * @param   string  $inform_type    
     * @param   string  $visible        The visible of the recipient
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible)
    {
        $data = array();
        $data['name']        = $name;
        $data['email']       = $email;
        $data['tel']         = $tel;
        $data['fax']         = $fax;
        $data['mobile']      = $mobile;
        $data['inform_type'] = (int)$inform_type;
        $data['visible']     = (int)$visible;

        $rcptTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $result = $rcptTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_UPDATED'), _t('CONTACT_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_UPDATED'), RESPONSE_NOTICE);
        return true;
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
        $objORM = Jaws_ORM::getInstance();
        $result = $objORM->delete()->table('contacts_recipients')->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_NOT_DELETED'), _t('CONTACT_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_RECIPIENT_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Set properties of the gadget
     *
     * @access  public
     * @param   bool    $use_antispam
     * @param   string  $email_format
     * @param   bool    $enable_attachment
     * @param   bool    $comments
     * @return  mixed   True if change is successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments)
    {
        $rs = array();
        $rs[] = $this->gadget->registry->update('use_antispam',      $use_antispam);
        $rs[] = $this->gadget->registry->update('email_format',      $email_format);
        $rs[] = $this->gadget->registry->update('enable_attachment', $enable_attachment);
        $rs[] = $this->gadget->registry->update('comments',          $comments);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || !$r) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'), _t('CONTACT_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}
