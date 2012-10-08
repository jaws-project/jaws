<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Contact/Model.php';

class ContactAdminModel extends ContactModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = JAWS_DATA . 'contact';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('CONTACT_NAME'));
        }

        //registry keys.
        $GLOBALS['app']->Registry->NewKeyEx(array('/gadgets/Contact/use_antispam',      'true'),
                                            array('/gadgets/Contact/email_format',      'html'),
                                            array('/gadgets/Contact/enable_attachment', 'false'),
                                            array('/gadgets/Contact/comments',          ''),
                                            array('/gadgets/Contact/default_items',
                                                  'name,email,url,recipient,subject,attachment,message')
                                            );
        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $tables = array('contacts',
                        'contacts_recipients');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('CONTACT_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/use_antispam');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/email_format');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/enable_attachment');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/comments');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/default_items');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.3.0', '<')) {
            $result = $this->installSchema('0.3.0.xml', '', '0.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/EditSentMessage',  'true');

            // Registry keys.
            $send_html = $GLOBALS['app']->Registry->Get('/gadgets/Contact/send_html') == 'true';
            $GLOBALS['app']->Registry->NewKey('/gadgets/Contact/use_captcha', 'true');
            $GLOBALS['app']->Registry->NewKey('/gadgets/Contact/email_format', $send_html? 'html' : 'text');
            $GLOBALS['app']->Registry->NewKey('/gadgets/Contact/enable_attachment', 'false');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/send_html');
        }

        if (version_compare($old, '0.3.1', '<')) {
            $GLOBALS['app']->Registry->NewKey('/gadgets/Contact/comments', '');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/comment');
        }

        if (version_compare($old, '0.3.2', '<')) {
            $result = $this->installSchema('0.3.2.xml', '', '0.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Contact/use_captcha');
        }

        if (version_compare($old, '0.3.3', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/AccessToMailer', 'false');
        }

        if (version_compare($old, '0.3.4', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.3.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Contact/AllowAttachment', 'false');

            // Registry keys
            $GLOBALS['app']->Registry->NewKey('/gadgets/Contact/default_items',
                                              'name,email,url,recipient,subject,attachment,message');

            $new_dir = JAWS_DATA . 'contact';
            if (!Jaws_Utils::mkdir($new_dir)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('CONTACT_NAME'));
            }
        }

        return true;
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
        $sql = '
            SELECT
                [id], [name], [email], [recipient], [subject], [msg_txt], [reply], [reply_sent], [createtime]
            FROM [[contacts]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
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
        if (is_numeric($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }
        $sql = '
            SELECT
                [id], [name], [email], [subject], [attachment], [recipient], [reply], [createtime]
            FROM [[contacts]]';

            if ($recipient != -1) {
                $sql .= ' WHERE [recipient] =  {recipient}';
            }

            $sql .= ' ORDER BY [id] DESC';

        $result = $GLOBALS['db']->queryAll($sql, array('recipient' => (int) $recipient));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
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
        $sql = '
            SELECT COUNT([id])
            FROM [[contacts]]';

        if ($recipient != -1) {
            $sql .= ' WHERE [[contacts]].[recipient] = {recipient}';
        }

        $res = $GLOBALS['db']->queryOne($sql, array('recipient' => (int) $recipient));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
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
        $sql = '
            UPDATE [[contacts]] SET
                [name]       = {name},
                [email]      = {email},
                [company]    = {company},
                [url]        = {url},
                [tel]        = {tel},
                [fax]        = {fax},
                [mobile]     = {mobile},
                [address]    = {address},
                [recipient]  = {recipient},
                [subject]    = {subject},
                [msg_txt]    = {message},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params              = array();
        $params['id']        = (int)$id;;
        $params['name']      = $xss->parse($name);
        $params['email']     = $xss->parse($email);
        $params['company']   = $xss->parse($company);
        $params['url']       = $xss->parse($url);
        $params['tel']       = $xss->parse($tel);
        $params['fax']       = $xss->parse($fax);
        $params['mobile']    = $xss->parse($mobile);
        $params['address']   = $xss->parse($address);
        $params['recipient'] = (int)$recipient;
        $params['subject']   = $xss->parse($subject);
        $params['message']   = $xss->parse($message);
        $params['now']       = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = '
            UPDATE [[contacts]] SET
                [reply]      = {reply},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params          = array();
        $params['id']    = (int)$id;;
        $params['reply'] = $xss->parse($reply);
        $params['now']   = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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
     * @param   int     $id             Contact ID
     * @param   int     $reply_sent     
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateReplySent($id, $reply_sent)
    {
        $sql = '
            UPDATE [[contacts]] SET
                [reply_sent] = {reply_sent},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['id']         = (int)$id;;
        $params['reply_sent'] = (int)$reply_sent;
        $params['now']        = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'DELETE FROM [[contacts]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
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
        $sql = '
            INSERT INTO [[contacts_recipients]]
                ([name], [email], [tel], [fax], [mobile], [inform_type], [visible])
            VALUES
                ({name}, {email}, {tel}, {fax}, {mobile}, {inform_type}, {visible})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['name']        = $xss->parse($name);
        $params['email']       = $xss->parse($email);
        $params['tel']         = $xss->parse($tel);
        $params['fax']         = $xss->parse($fax);
        $params['mobile']      = $xss->parse($mobile);
        $params['inform_type'] = (int)$inform_type;
        $params['visible']     = (int)$visible;

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = '
            UPDATE [[contacts_recipients]] SET
                [name]        = {name},
                [email]       = {email},
                [tel]         = {tel},
                [fax]         = {fax},
                [mobile]      = {mobile},
                [inform_type] = {inform_type},
                [visible]     = {visible}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['id']          = (int)$id;
        $params['name']        = $xss->parse($name);
        $params['email']       = $xss->parse($email);
        $params['tel']         = $xss->parse($tel);
        $params['fax']         = $xss->parse($fax);
        $params['mobile']      = $xss->parse($mobile);
        $params['inform_type'] = (int)$inform_type;
        $params['visible']     = (int)$visible;

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'DELETE FROM [[contacts_recipients]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
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
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Contact/use_antispam',      $use_antispam);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Contact/email_format',      $email_format);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Contact/enable_attachment', $enable_attachment);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Contact/comments',          $comments);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || !$r) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'), _t('CONTACT_NAME'));
            }
        }
        $GLOBALS['app']->Registry->Commit('Contact');
        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}
