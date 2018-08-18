<?php
/**
 * Contact AJAX API
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get information of a Contact
     *
     * @access  public
     * @internal param  int $id Contact ID
     * @return  array   Contact info array
     */
    function GetContact()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Contacts');
        $contact = $model->GetContact($id);
        if (Jaws_Error::IsError($contact) ||
            empty($contact) ||
            !$this->gadget->GetPermission('ManageRecipientContacts', $contact['recipient'])
        ) {
            return false; //we need to handle errors on ajax
        }

        return $contact;
    }

    /**
     * Update contact information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateContact()
    {
        $this->gadget->CheckPermission('ManageContacts');
        $data = $this->gadget->request->fetchAll('post');
        $id = $data['id'];
        unset($data['id']);

        $result = $this->gadget->model->loadAdmin('Contacts')->UpdateContact($id, $data);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('CONTACT_CONTACTS_UPDATED'),
            RESPONSE_NOTICE,
            $result
        );
    }

    /**
     * Delete a Contact
     *
     * @access  public
     * @internal param  string $id ID of the Contact
     * @return  array   Response array (notice or error)
     */
    function DeleteContact()
    {
        $this->gadget->CheckPermission('ManageContacts');
        @list($id) = $this->gadget->request->fetchAll('post');
        $result = $this->gadget->model->loadAdmin('Contacts')->DeleteContact($id);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('CONTACT_CONTACTS_DELETED'),
            RESPONSE_NOTICE,
            $result
        );
    }

    /**
     * Get information of a Contact Reply
     *
     * @access  public
     * @internal param  int     Contact ID
     * @return  mixed   Reply info or False on error
     */
    function GetReply()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Contacts');
        $replyData = $model->GetReply($id);
        if (Jaws_Error::IsError($replyData) ||
            empty($replyData) ||
            !$this->gadget->GetPermission('ManageRecipientContacts', $replyData['recipient'])
        ) {
            return false; //we need to handle errors on ajax
        }

        if (isset($replyData['id'])) {
            $replyData['readonly'] = (bool)$replyData['reply_sent'] &&
                !(bool)$this->gadget->GetPermission('EditSentMessage');
        }
        return $replyData;
    }

    /**
     * Returns the reply form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReplyUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Contacts');
        return $gadget->ReplyUI();
    }

    /**
     * Get information of a Recipient
     *
     * @access  public
     * @internal param  int $id Recipient ID
     * @return  mixed   Recipient info or False on error
     */
    function GetRecipient()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Recipients');
        $RecipientInfo = $model->GetRecipient($id);
        if (Jaws_Error::IsError($RecipientInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $RecipientInfo;
    }

    /**
     * Insert the information of a Recipient
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertRecipient()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $data = $this->gadget->request->fetchAll('post');
        unset($data['id']);
        $result = $this->gadget->model->loadAdmin('Recipients')->InsertRecipient($data);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('CONTACT_RECIPIENT_ADDED'),
            RESPONSE_NOTICE,
            $result
        );
    }

    /**
     * Update the information of a Recipient
     *
     * @access  public
     * @internal param  string  $id             ID of the recipient
     * @internal param  string  $name           Name of the recipient
     * @internal param  string  $email          Email of recipient
     * @internal param  string  $tel            Phone number of recipient
     * @internal param  string  $fax            Fax number of recipient
     * @internal param  string  $mobile         Mobile number of recipient
     * @internal param  int     $inform_type    Inform Type
     * @internal param  string  $visible        The visible of the recipient
     * @return  array   Response array (notice or error)
     */
    function UpdateRecipient()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $data = $this->gadget->request->fetchAll('post');
        $id = $data['id'];
        unset($data['id']);
        $result = $this->gadget->model->loadAdmin('Recipients')->UpdateRecipient($id, $data);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('CONTACT_RECIPIENT_UPDATED'),
            RESPONSE_NOTICE,
            $result
        );
    }

    /**
     * Delete a Recipient
     *
     * @access  public
     * @internal param  string  $id     ID of the Recipient
     * @return  array   Response array (notice or error)
     */
    function DeleteRecipient()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        @list($id) = $this->gadget->request->fetchAll('post');
        $result = $this->gadget->model->loadAdmin('Recipients')->DeleteRecipient($id);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('CONTACT_RECIPIENT_DELETED'),
            RESPONSE_NOTICE,
            $result
        );
    }

    /**
     * Set properties of the gadget
     *
     * @access  public
     * @internal param  bool    $use_antispam
     * @internal param  string  $email_format
     * @internal param  bool    $enable_attachment
     * @internal param  string  $comments
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        @list($use_antispam, $email_format, $enable_attachment, $comments) = $this->gadget->request->fetchAll('post');
        $comments = $this->gadget->request->fetch(3, 'post', 'strip_crlf');

        $model = $this->gadget->model->loadAdmin('Properties');
        $model->UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get Data
     *
     * @access  public
     * @internal param  int     $recipient
     * @internal param  int     $offset
     * @return  array   Recipients or Contacts
     */
    function GetContacts()
    {
        @list($recipient, $offset) = $this->gadget->request->fetchAll('post');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        $gadget = $this->gadget->action->loadAdmin('Contacts');
        return $gadget->GetContacts($recipient, $offset);
    }

    /**
     * Gets contacts count
     *
     * @access  public
     * @internal param  int     $recipient  Recipient ID of contacts
     * @return  mixed   Count of available contacts and False on failure
     */
    function GetContactsCount()
    {
        @list($recipient) = $this->gadget->request->fetchAll('post');
        if(empty($recipient)) {
            $recipient = -1;
        }
        $model = $this->gadget->model->loadAdmin('Contacts');
        $res = $model->GetContactsCount($recipient);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

    /**
     * Gets users of the specified group
     *
     * @access  public
     * @internal param  int     $group  ID of the group
     * @return  array   array of users or false on error
     */
    function GetUsers()
    {
        @list($group) = $this->gadget->request->fetchAll('post');
        $userModel = new Jaws_User();
        return $userModel->GetUsers($group, false, null, true);
    }

    /**
     * Gets a preview of the email
     *
     * @access  public
     * @internal param  string  $message    Content of the message body
     * @return  string  XHTML template content
     */
    function GetMessagePreview()
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $message = $this->gadget->request->fetch(0, 'post', 'strip_crlf');

        $gadget = $this->gadget->action->loadAdmin('Mailer');
        return $gadget->PrepareMessage($message);
    }

    /**
     * Sends the email
     *
     * @access  public
     * @internal param  string  $target     JSON decoded array ([to, cc, bcc] or [user, group])
     * @internal param  string  $subject    Subject of the Email
     * @internal param  string  $message    Message body of the Email
     * @internal param  string  $attachment attachment
     * @return  array   Response array (notice or error)
     */
    function SendEmail()
    {
        $this->gadget->CheckPermission('AccessToMailer');
        @list($target, $subject, $message, $attachment) = $this->gadget->request->fetchAll('post');
        $message = $this->gadget->request->fetch(2, 'post', 'strip_crlf');
        $target = $this->gadget->request->fetch('0:array', 'post');

        $gadget = $this->gadget->action->loadAdmin('Mailer');
        $gadget->SendEmail($target, $subject, $message, $attachment);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get Data
     *
     * @access  public
     * @internal param  int     $offset
     * @internal param  string  $grid
     * @return  array   Recipients or Contacts
     */
    function getData()
    {
        @list($offset, $grid) = $this->gadget->request->fetchAll('post');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        $gadget = $this->gadget->action->loadAdmin('Recipients');

        return $gadget->GetRecipients($offset);
    }

}