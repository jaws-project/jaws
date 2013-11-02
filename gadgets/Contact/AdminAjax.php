<?php
/**
 * Contact AJAX API
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_AdminAjax extends Jaws_Gadget_Action
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadModel('Contacts');
        $contact = $model->GetContact($id);
        if (Jaws_Error::IsError($contact)) {
            return false; //we need to handle errors on ajax
        }

        return $contact;
    }

    /**
     * Update contact information
     *
     * @access  public
     * @internal param  int     $id Contact ID
     * @internal param  string  $name Name
     * @internal param  string  $email Email address
     * @internal param  string  $company
     * @internal param  string  $url
     * @internal param  string  $tel
     * @internal param  string  $fax
     * @internal param  string  $mobile
     * @internal param  string  $address
     * @internal param  int     $recipient Recipient ID
     * @internal param  string  $subject Subject of message
     * @internal param  string  $message Message content
     * @return  array   Response array (notice or error)
     */
    function UpdateContact()
    {
        $this->gadget->CheckPermission('ManageContacts');
        @list($id, $name, $email, $company, $url, $tel, $fax,
            $mobile, $address, $recipient, $subject, $message
        ) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Contacts');
        $model->UpdateContact($id, $name, $email, $company, $url, $tel, $fax, $mobile, $address, $recipient, $subject, $message);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update contact reply
     *
     * @access  public
     * @internal param  int     $id         Contact ID
     * @internal param  string  $reply      Reply content
     * @internal param  bool    $send_reply whether to send a reply
     * @return  array   Response array (notice or error)
     */
    function UpdateReply()
    {
        $this->gadget->CheckPermission('ManageContacts');
        @list($id, $reply, $send_reply) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Contacts');
        $res = $model->UpdateReply($id, $reply);
        if (!Jaws_Error::IsError($res) && $send_reply) {
            $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
            $gadget = $this->gadget->loadAdminAction('Contacts');
            $gadget->SendReply($id);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Contacts');
        $model->DeleteContact($id);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Contacts');
        $replyData = $model->GetReply($id);
        if (Jaws_Error::IsError($replyData)) {
            return false; //we need to handle errors on ajax
        }
        if (isset($replyData['id'])) {
            $replyData['readonly'] = (bool)$replyData['reply_sent'] && !(bool)$this->gadget->GetPermission('EditSentMessage');
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
        $gadget = $this->gadget->loadAdminAction('Contacts');
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadModel('Recipients');
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
     * @internal param  string  $name           Name of the recipient
     * @internal param  string  $email          Email of recipient
     * @internal param  string  $tel            Phone number of recipient
     * @internal param  string  $fax            Fax number of recipient
     * @internal param  string  $mobile         Mobile number of recipient
     * @internal param  int     $inform_type    Inform Type
     * @internal param  string  $visible        The visible of the recipient
     * @return  array   Response array (notice or error)
     */
    function InsertRecipient()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        @list($name, $email, $tel, $fax, $mobile, $inform_type, $visible) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Recipients');
        $model->InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Recipients');
        $model->UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->loadAdminModel('Recipients');
        $model->DeleteRecipient($id);
        return $GLOBALS['app']->Session->PopLastResponse();
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
        @list($use_antispam, $email_format, $enable_attachment, $comments) = jaws()->request->fetchAll('post');
        $comments = jaws()->request->fetch(3, 'post', false);

        $model = $this->gadget->loadAdminModel('Properties');
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
        @list($recipient, $offset) = jaws()->request->fetchAll('post');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        $gadget = $this->gadget->loadAdminAction('Contacts');

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
        @list($recipient) = jaws()->request->fetchAll('post');
        if(empty($recipient)) {
            $recipient = -1;
        }
        $model = $this->gadget->loadAdminModel('Contacts');
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
        @list($group) = jaws()->request->fetchAll('post');
        $userModel = new Jaws_User();
        return $userModel->GetUsers($group, null, true);
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
        $message = jaws()->request->fetch(0, 'post', false);

        $gadget = $this->gadget->loadAdminAction('Mailer');
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
        @list($target, $subject, $message, $attachment) = jaws()->request->fetchAll('post');
        $message = jaws()->request->fetch(2, 'post', false);

        $gadget = $this->gadget->loadAdminAction('Mailer');
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
    function GetData()
    {
        @list($offset, $grid) = jaws()->request->fetchAll('post');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        $gadget = $this->gadget->loadAdminAction('Recipients');

        return $gadget->GetRecipients($offset);
    }

}