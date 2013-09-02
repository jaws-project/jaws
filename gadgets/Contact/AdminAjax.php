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
class Contact_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Contact_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Get information of a Contact
     *
     * @access  public
     * @param   int     $id     Contact ID
     * @return  array   Contact info array
     */
    function GetContact($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'Model', 'Contacts');
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
     * @param   int     $id         Contact ID
     * @param   string  $name       Name
     * @param   string  $email      Email address
     * @param   string  $company
     * @param   string  $url
     * @param   string  $tel
     * @param   string  $fax
     * @param   string  $mobile
     * @param   string  $address
     * @param   int     $recipient  Rcipient ID
     * @param   string  $subject    Subject of message
     * @param   string  $message    Message content
     * @return  array   Response array (notice or error)
     */
    function UpdateContact($id, $name, $email, $company, $url, $tel, $fax, $mobile, $address, $recipient, $subject, $message)
    {
        $this->gadget->CheckPermission('ManageContacts');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Contacts');
        $model->UpdateContact($id, $name, $email, $company, $url, $tel, $fax, $mobile, $address, $recipient, $subject, $message);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update contact reply
     *
     * @access  public
     * @param   int     $id             Contact ID
     * @param   string  $reply          Reply content
     * @param   bool    $send_reply     whether to send a reply
     * @return  array   Response array (notice or error)
     */
    function UpdateReply($id, $reply, $send_reply)
    {
        $this->gadget->CheckPermission('ManageContacts');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Contacts');
        $res = $model->UpdateReply($id, $reply);
        if (!Jaws_Error::IsError($res) && $send_reply) {
            $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
            $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Contacts');
            $gadget->SendReply($id);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a Contact
     *
     * @access  public
     * @param   string  $id  ID of the Contact
     * @return  array   Response array (notice or error)
     */
    function DeleteContact($id)
    {
        $this->gadget->CheckPermission('ManageContacts');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Contacts');
        $model->DeleteContact($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get information of a Contact Reply
     *
     * @access  public
     * @param   int     Contact ID
     * @return  mixed   Reply info or False on error
     */
    function GetReply($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Contacts');
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
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Contacts');
        return $gadget->ReplyUI();
    }

    /**
     * Get information of a Recipient
     *
     * @access  public
     * @param   int     $id    Recipient ID
     * @return  mixed   Recipient info or False on error
     */
    function GetRecipient($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'Model', 'Recipients');
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
     * @param   string  $name           Name of the recipient
     * @param   string  $email          Email of recipient
     * @param   string  $tel            Phone number of recipient
     * @param   string  $fax            Fax number of recipient
     * @param   string  $mobile         Mobile number of recipient
     * @param   int     $inform_type    Inform Type
     * @param   string  $visible        The visible of the recipient
     * @return  array   Response array (notice or error)
     */
    function InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible)
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Recipients');
        $model->InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
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
     * @param   int     $inform_type    Inform Type
     * @param   string  $visible        The visible of the recipient
     * @return  array   Response array (notice or error)
     */
    function UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible)
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Recipients');
        $model->UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a Recipient
     *
     * @access  public
     * @param   string  $id  ID of the Recipient
     * @return  array   Response array (notice or error)
     */
    function DeleteRecipient($id)
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Recipients');
        $model->DeleteRecipient($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Set properties of the gadget
     *
     * @access  public
     * @param   bool    $use_antispam
     * @param   string  $email_format
     * @param   bool    $enable_attachment
     * @param   string  $comments
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments)
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $comments = jaws()->request->get(3, 'post', false);

        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel', 'Properties');
        $model->UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get Data
     *
     * @access  public
     * @param   int     $recipient
     * @param   int     $offset
     * @return  array   Recipients or Contacts
     */
    function GetContacts($recipient, $offset)
    {
        if (!is_numeric($offset)) {
            $offset = null;
        }
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Contacts');

        return $gadget->GetContacts($recipient, $offset);
    }

    /**
     * Gets contacts count
     *
     * @access  public
     * @param   int     $recipient  Recipient ID of contacts
     * @return  mixed   Count of available contacts and False on failure
     */
    function GetContactsCount($recipient = -1)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'Model', 'Contacts');
        $res = $this->_Model->GetContactsCount($recipient);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

    /**
     * Gets users of the specified group
     *
     * @access  public
     * @param   int     $group      ID of the group
     * @return  array   array of users or false on error
     */
    function GetUsers($group)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        return $userModel->GetUsers($group, null, true);
    }

    /**
     * Gets a preview of the email
     *
     * @access  public
     * @param   string  $message    Content of the message body
     * @return  string  XHTML template content
     */
    function GetMessagePreview($message)
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $message = jaws()->request->get(0, 'post', false);

        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Mailer');
        return $gadget->PrepareMessage($message);
    }

    /**
     * Sends the email
     *
     * @access  public
     * @param   string  $target     JSON decoded array ([to, cc, bcc] or [user, group])
     * @param   string  $subject    Subject of the Email
     * @param   string  $message    Message body of the Email
     * @param   string  $attachment attachment
     * @return  array   Response array (notice or error)
     */
    function SendEmail($target, $subject, $message, $attachment)
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $message = jaws()->request->get(2, 'post', false);

        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Mailer');
        $gadget->SendEmail($target, $subject, $message, $attachment);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get Data
     *
     * @access  public
     * @param   int     $offset
     * @param   string  $grid
     * @return  array   Recipients or Contacts
     */
    function GetData($offset, $grid)
    {
        if (!is_numeric($offset)) {
            $offset = null;
        }
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML', 'Recipients');

        return $gadget->GetRecipients($offset);
    }

}