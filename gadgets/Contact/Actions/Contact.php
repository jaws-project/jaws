<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Contact extends Jaws_Gadget_Action
{
    /**
     * Show default contact form
     *
     * @access  public
     * @return  string   XHTML templatecontent
     */
    function Contact()
    {
        $this->SetTitle(_t('CONTACT_US'));
        return $this->GetContactForm('');
    }

    /**
     * ContactMini Action
     *
     * @access  public
     * @return  string  XHTML content of ContactMini
     */
    function ContactMini()
    {
        $this->SetTitle(_t('CONTACT_US'));
        return $this->GetContactForm('mini');
    }

    /**
     * ContactSimple Action
     *
     * @access  public
     * @return  string  XHTML content of ContactSimple
     */
    function ContactSimple()
    {
        $this->SetTitle(_t('CONTACT_US'));
        return $this->GetContactForm('simple');
    }

    /**
     * ContactFull Action
     *
     * @access  public
     * @return  string  XHTML content of ContactFull
     */
    function ContactFull()
    {
        $this->SetTitle(_t('CONTACT_US'));
        return $this->GetContactForm('full');
    }

    /**
     * Show contact us form
     *
     * @access  public
     * @param   string   $type
     * @return  string   XHTML template content
     */
    function GetContactForm($type = '')
    {
        switch (strtolower($type))
        {
            case 'mini':
                $items_array = array('name', 'email', 'recipient', 'subject', 'message');
                break;
            case 'simple':
                $items_array = array('name', 'email', 'url',  'tel', 'recipient', 'subject', 'attachment', 'message');
                break;
            case 'full':
                $items_array = array('name', 'email', 'company', 'url', 'tel', 
                                     'fax', 'mobile', 'address', 'recipient', 'subject', 'attachment', 'message');
                break;
            default:
                $items_array = array_filter(explode(',',
                                            $this->gadget->registry->fetch('default_items')));
                break;
        }

        $tpl = $this->gadget->template->load('Contact.html');
        $tpl->SetBlock('contact');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('CONTACT_US'));
        $comments = $this->gadget->registry->fetch('comments');
        $tpl->SetVariable('comments', $this->gadget->ParseText($comments));
        $tpl->SetVariable('send', _t('CONTACT_SEND'));

        $btnSend =& Piwi::CreateWidget('Button', 'send', _t('CONTACT_SEND'));
        $btnSend->SetSubmit();
        $tpl->SetVariable('btn_send', $btnSend->Get());

        $last_message = $GLOBALS['app']->Session->PopSimpleResponse('Contact_Data');
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Contact')) {
            $tpl->SetBlock('contact/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('contact/response');
        }

        if (!$GLOBALS['app']->Session->Logged()) {
            //name
            if (in_array('name', $items_array)) {
                $tpl->SetBlock('contact/name');
                $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
                $name = isset($last_message['contact_name'])?
                        $last_message['contact_name'] : $GLOBALS['app']->Session->GetCookie('visitor_name');
                $tpl->SetVariable('name', isset($name)? $name : '');
                $tpl->ParseBlock('contact/name');
            }

            //email
            if (in_array('email', $items_array)) {
                $tpl->SetBlock('contact/email');
                $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
                $email = isset($last_message['contact_email'])?
                         $last_message['contact_email'] : $GLOBALS['app']->Session->GetCookie('visitor_email');
                $tpl->SetVariable('email', isset($email)? $email : '');
                $tpl->ParseBlock('contact/email');
            }

            //url
            if (in_array('url', $items_array)) {
                $tpl->SetBlock('contact/url');
                $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
                $url = isset($last_message['contact_url'])?
                       $last_message['contact_url'] : $GLOBALS['app']->Session->GetCookie('visitor_url');
                $tpl->SetVariable('url', isset($url)? $url : 'http://');
                $tpl->ParseBlock('contact/url');
            }
        }

        //captcha
        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $mPolicy->loadCaptcha($tpl, 'contact');

        //company
        if (in_array('company', $items_array)) {
            $tpl->SetBlock('contact/company');
            $tpl->SetVariable('lbl_company', _t('CONTACT_COMPANY'));
            $company = isset($last_message['contact_company'])? $last_message['contact_company'] : '';
            $tpl->SetVariable('company', $company);
            $tpl->ParseBlock('contact/company');
        }

        //tel
        if (in_array('tel', $items_array)) {
            $tpl->SetBlock('contact/tel');
            $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
            $tel = isset($last_message['contact_tel'])? $last_message['contact_tel'] : '';
            $tpl->SetVariable('tel', $tel);
            $tpl->ParseBlock('contact/tel');
        }

        //fax
        if (in_array('fax', $items_array)) {
            $tpl->SetBlock('contact/fax');
            $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
            $fax = isset($last_message['contact_fax'])? $last_message['contact_fax'] : '';
            $tpl->SetVariable('fax', $fax);
            $tpl->ParseBlock('contact/fax');
        }

        //mobile
        if (in_array('mobile', $items_array)) {
            $tpl->SetBlock('contact/mobile');
            $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
            $mobile = isset($last_message['contact_mobile'])? $last_message['contact_mobile'] : '';
            $tpl->SetVariable('mobile', $mobile);
            $tpl->ParseBlock('contact/mobile');
        }

        //address
        if (in_array('address', $items_array)) {
            $tpl->SetBlock('contact/address');
            $tpl->SetVariable('lbl_address',  _t('CONTACT_ADDRESS'));
            $address = isset($last_message['contact_address'])? $last_message['contact_address'] : '';
            $tpl->SetVariable('address', $address);
            $tpl->ParseBlock('contact/address');
        }

        //recipient
        if (in_array('recipient', $items_array)) {
            $tpl->SetBlock('contact/recipient');
            $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));
            $model = $this->gadget->model->load('Recipients');
            $recipients = $model->GetRecipients(true);
            if (Jaws_Error::IsError($recipients) || empty($recipients)) {
                $recipients   = array();
                $recipients[] = array('id'   => 0,
                                      'name' => $this->gadget->registry->fetch('site_author', 'Settings'));
            }

            $rcpt = isset($last_message['contact_recipient'])? $last_message['contact_recipient'] : '';
            foreach ($recipients as $recipient) {
                $tpl->SetBlock('contact/recipient/item');
                $tpl->SetVariable('recipient_id',   $recipient['id']);
                $tpl->SetVariable('recipient_name', $recipient['name']);
                $tpl->SetVariable('selected', ($rcpt== $recipient['id'])? 'selected="selected"': '');
                $tpl->ParseBlock('contact/recipient/item');
            }
            $tpl->ParseBlock('contact/recipient');
        }

        //subject
        if (in_array('subject', $items_array)) {
            $tpl->SetBlock('contact/subject');
            $tpl->SetVariable('lbl_subject',  _t('CONTACT_SUBJECT'));
            $subject = isset($last_message['contact_subject'])? $last_message['contact_subject'] : '';
            $tpl->SetVariable('subject', $subject);
            $tpl->ParseBlock('contact/subject');
        }

        //attachment
        if (in_array('attachment', $items_array) &&
            ($this->gadget->registry->fetch('enable_attachment') == 'true') &&
            $this->gadget->GetPermission('AllowAttachment'))
        {
            $tpl->SetBlock('contact/attachment');
            $tpl->SetVariable('lbl_attachment',  _t('CONTACT_ATTACHMENT'));
            $tpl->ParseBlock('contact/attachment');
        }

        //message
        if (in_array('message', $items_array)) {
            $tpl->SetBlock('contact/message');
            $tpl->SetVariable('lbl_message',  _t('CONTACT_MESSAGE'));
            $message = isset($last_message['contact_message'])? $last_message['contact_message'] : '';
            $tpl->SetVariable('message', $message);
            $tpl->ParseBlock('contact/message');
        }

        $tpl->ParseBlock('contact');
        return $tpl->Get();
    }

    /**
     * Save contact in database
     *
     * @access  public
     */
    function Send()
    {
        $post = jaws()->request->fetch(array('contact_name', 'contact_email', 'contact_company', 'contact_url',
                                    'contact_tel', 'contact_fax', 'contact_mobile', 'contact_address',
                                    'contact_recipient', 'contact_subject', 'contact_message'),
                              'post');

        if ($GLOBALS['app']->Session->Logged()) {
            $post['contact_name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['contact_email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['contact_url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['contact_name'])    == '' ||
            trim($post['contact_subject']) == '' ||
            trim($post['contact_message']) == '')
        {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('CONTACT_INCOMPLETE_FIELDS'), 'Contact');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact_Data');
            Jaws_Header::Referrer();
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Contact');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact_Data');
            Jaws_Header::Referrer();
        }

        if ($this->gadget->registry->fetch('use_antispam') == 'true') {
            if (!preg_match("/^[[:alnum:]-_.]+\@[[:alnum:]-_.]+\.[[:alnum:]-_]+$/", $post['contact_email'])) {
                $GLOBALS['app']->Session->PushSimpleResponse(_t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'), 'Contact');
                $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact_Data');
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
                                              'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                                              false);
            if (Jaws_Error::IsError($attach)) {
                $GLOBALS['app']->Session->PushSimpleResponse($attach->getMessage(), 'Contact');
                $GLOBALS['app']->Session->PushSimpleResponse($post, 'Contact_Data');
                Jaws_Header::Referrer();
            }

            if (!empty($attach)) {
                $attachment = $attach['contact_attachment'][0]['host_filename'];
            }
        }

        $model = $this->gadget->model->load('Contacts');
        $result = $model->InsertContact($post['contact_name'],
                                        $post['contact_email'],
                                        $post['contact_company'],
                                        $post['contact_url'],
                                        $post['contact_tel'],
                                        $post['contact_fax'],
                                        $post['contact_mobile'],
                                        $post['contact_address'],
                                        $post['contact_recipient'],
                                        $post['contact_subject'],
                                        $attachment,
                                        $post['contact_message']);
        if (Jaws_Error::IsError($result)) {
            $res_msg = _t('CONTACT_RESULT_ERROR_DB');
        } else {
            $to = '';
            $cid = $GLOBALS['db']->lastInsertID('contacts', 'id');
            $rid = (int)$post['contact_recipient'];
            if (!empty($rid)) {
                $model = $this->gadget->model->load('Recipients');
                $recipient = $model->GetRecipient((int)$post['contact_recipient']);
                if (Jaws_Error::IsError($recipient) || !isset($recipient['id'])) {
                    $res_msg = _t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS');
                } elseif ($recipient['inform_type'] == 1) { //Send To Email
                    $to = $recipient['email'];
                }
            }
            $this->SendEmailToRecipient($to, $cid);
            $res_msg = _t('CONTACT_RESULT_SENT');
        }
        $GLOBALS['app']->Session->PushSimpleResponse($res_msg, 'Contact');
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
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'), _t('CONTACT_NAME'));
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

        $mail = new Jaws_Mail;
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