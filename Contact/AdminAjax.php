<?php
/**
 * Contact AJAX API
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ContactAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object  $model  Jaws_Model reference
     */
    function ContactAdminAjax(&$model)
    {
        $this->_Model =& $model;
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
        $ContactInfo = $this->_Model->GetContact($id);
        if (Jaws_Error::IsError($ContactInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $ContactInfo;
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
        $this->CheckSession('Contact', 'ManageContacts');
        $this->_Model->UpdateContact($id, $name, $email, $company, $url, $tel, $fax, $mobile, $address, $recipient, $subject, $message);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update contact reply
     *
     * @access  public
     * @param   integer $id             Contact ID
     * @param   string  $reply          Reply content
     * @param   bool    $send_reply     whether to send a reply
     * @return  array   Response array (notice or error)
     */
    function UpdateReply($id, $reply, $send_reply)
    {
        $this->CheckSession('Contact', 'ManageContacts');
        $res = $this->_Model->UpdateReply($id, $reply);
        if (!Jaws_Error::IsError($res) && $send_reply) {
            $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
            $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
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
        $this->CheckSession('Contact', 'ManageContacts');
        $this->_Model->DeleteContact($id);
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
        $replyData = $this->_Model->GetReply($id);
        if (Jaws_Error::IsError($replyData)) {
            return false; //we need to handle errors on ajax
        }
        if (isset($replyData['id'])) {
            $replyData['readonly'] = (bool)$replyData['reply_sent'] && !(bool)$this->GetPermission('Contact', 'EditSentMessage');
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
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
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
        $RecipientInfo = $this->_Model->GetRecipient($id);
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
        $this->CheckSession('Contact', 'ManageRecipients');
        $this->_Model->InsertRecipient($name, $email, $tel, $fax, $mobile, $inform_type, $visible);
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
        $this->CheckSession('Contact', 'ManageRecipients');
        $this->_Model->UpdateRecipient($id, $name, $email, $tel, $fax, $mobile, $inform_type, $visible);
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
        $this->CheckSession('Contact', 'ManageRecipients');
        $this->_Model->DeleteRecipient($id);
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
        $this->CheckSession('Contact', 'UpdateProperties');
        $this->_Model->UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments);
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
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }

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
        $this->CheckSession('Contact', 'AccessToMailer');
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
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
        $this->CheckSession('Contact', 'AccessToMailer');
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
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
        $gadget = $GLOBALS['app']->LoadGadget('Contact', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        return $gadget->GetRecipients($offset);
    }

}