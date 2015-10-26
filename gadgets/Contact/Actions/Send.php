<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Send extends Jaws_Gadget_Action
{
    /**
     * Save contact in database
     *
     * @access  public
     */
    function Send()
    {
        $post = jaws()->request->fetch(
            array(
                'name', 'email', 'company', 'url', 'tel', 'fax',
                'mobile', 'address', 'recipient', 'subject', 'message'
            ),
            'post'
        );

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['name'])    == '' ||
            trim($post['subject']) == '' ||
            trim($post['message']) == '')
        {
            $GLOBALS['app']->Session->PushResponse(
                _t('CONTACT_INCOMPLETE_FIELDS'),
                'Contact.Response',
                RESPONSE_ERROR
            );
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact.Data');
            Jaws_Header::Referrer();
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushResponse(
                $resCheck->getMessage(),
                'Contact.Response',
                RESPONSE_ERROR
            );
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact.Data');
            Jaws_Header::Referrer();
        }

        if ($this->gadget->registry->fetch('use_antispam') == 'true') {
            if (!preg_match("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $post['email'])) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'),
                    'Contact.Response',
                    RESPONSE_ERROR
                );
                $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact.Data');
                Jaws_Header::Referrer();
            }
        }

        $attachment = null;
        if (($this->gadget->registry->fetch('enable_attachment') == 'true') &&
            $this->gadget->GetPermission('AllowAttachment')) 
        {
            $attach = Jaws_Utils::UploadFiles($_FILES,
                                              JAWS_DATA. 'contact',
                                              '',
                                              false);
            if (Jaws_Error::IsError($attach)) {
                $GLOBALS['app']->Session->PushResponse(
                    $attach->getMessage(),
                    'Contact.Response',
                    RESPONSE_ERROR
                );
                $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact.Data');
                Jaws_Header::Referrer();
            }

            if (!empty($attach)) {
                $attachment = $attach['attachment'][0]['host_filename'];
            }
        }

        $model = $this->gadget->model->load('Contacts');
        $result = $model->InsertContact(
            $post['name'],
            $post['email'],
            $post['company'],
            $post['url'],
            $post['tel'],
            $post['fax'],
            $post['mobile'],
            $post['address'],
            $post['recipient'],
            $post['subject'],
            $attachment,
            $post['message']
        );
        if (Jaws_Error::IsError($result)) {
            $res_msg = _t('CONTACT_RESULT_ERROR_DB');
            $res_type = RESPONSE_ERROR;
        } else {
            $to = '';
            $cid = $result;
            $rid = (int)$post['recipient'];
            if (!empty($rid)) {
                $model = $this->gadget->model->load('Recipients');
                $recipient = $model->GetRecipient((int)$post['recipient']);
                if (Jaws_Error::IsError($recipient) || !isset($recipient['id'])) {
                    $res_msg = _t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS');
                    $res_type = RESPONSE_ERROR;
                } elseif ($recipient['inform_type'] == 1) { //Send To Email
                    $to = $recipient['email'];
                }
            }
            $this->SendEmailToRecipient($to, $cid);
            $res_msg = _t('CONTACT_RESULT_SENT');
            $res_type = RESPONSE_NOTICE;
        }

        $GLOBALS['app']->Session->PushResponse(
            $res_msg,
            'Contact.Response',
            $res_type
        );
        Jaws_Header::Referrer();
    }

    /**
     * Send email to recipient
     *
     * @access  public
     * @param   string   $to   Recipient email address
     * @param   int      $cid   Contact ID
     * @return  mixed    True on Success or Jaws_Error on Failure
     */
    function SendEmailToRecipient($to, $cid)
    {
        $model = $this->gadget->model->load('Contacts');
        $contact = $model->GetContact($cid);
        if (Jaws_Error::IsError($contact)) {
            return $contact;
        }
        if (!isset($contact['id'])) {
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'));
        }

        $from_name  = $contact['name'];
        $from_email = $contact['email'];
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $format = $this->gadget->registry->fetch('email_format');
        if ($format == 'html') {
            $message = Jaws_String::AutoParagraph($contact['msg_txt']);
        } else {
            $message = $contact['msg_txt'];
        }

        $tpl = $this->gadget->template->load('SendToRecipient.html');
        $tpl->SetBlock($format);
        $tpl->SetVariable('lbl_name',      _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_email',     _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_company',   _t('CONTACT_COMPANY'));
        $tpl->SetVariable('lbl_url',       _t('GLOBAL_URL'));
        $tpl->SetVariable('lbl_tel',       _t('CONTACT_TEL'));
        $tpl->SetVariable('lbl_fax',       _t('CONTACT_FAX'));
        $tpl->SetVariable('lbl_mobile',    _t('CONTACT_MOBILE'));
        $tpl->SetVariable('lbl_address',   _t('CONTACT_ADDRESS'));
        $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));
        $tpl->SetVariable('lbl_subject',   _t('CONTACT_SUBJECT'));
        $tpl->SetVariable('lbl_message',   _t('CONTACT_MESSAGE'));
        $tpl->SetVariable('name',          $contact['name']);
        $tpl->SetVariable('email',         $contact['email']);
        $tpl->SetVariable('company',       $contact['company']);
        $tpl->SetVariable('url',           $contact['url']);
        $tpl->SetVariable('tel',           $contact['tel']);
        $tpl->SetVariable('fax',           $contact['fax']);
        $tpl->SetVariable('mobile',        $contact['mobile']);
        $tpl->SetVariable('address',       $contact['address']);
        $tpl->SetVariable('recipient',     $to);
        $tpl->SetVariable('subject',       $contact['subject']);
        $tpl->SetVariable('message',       $message);

        $tpl->SetVariable('site-name',     $site_name);
        $tpl->SetVariable('site-url',      $site_url);
        $tpl->ParseBlock($format);
        $template = $tpl->Get();

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom($from_email, $from_name);
        $mail->AddRecipient($to);
        $mail->SetSubject(Jaws_XSS::defilter($contact['subject']));
        $mail->SetBody($template, $format);
        $result = $mail->send();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

}