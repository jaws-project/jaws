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
                $name = isset($last_message['name'])?
                        $last_message['name'] : $GLOBALS['app']->Session->GetCookie('visitor_name');
                $tpl->SetVariable('name', isset($name)? $name : '');
                $tpl->ParseBlock('contact/name');
            }

            //email
            if (in_array('email', $items_array)) {
                $tpl->SetBlock('contact/email');
                $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
                $email = isset($last_message['email'])?
                         $last_message['email'] : $GLOBALS['app']->Session->GetCookie('visitor_email');
                $tpl->SetVariable('email', isset($email)? $email : '');
                $tpl->ParseBlock('contact/email');
            }

            //url
            if (in_array('url', $items_array)) {
                $tpl->SetBlock('contact/url');
                $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
                $url = isset($last_message['url'])?
                       $last_message['url'] : $GLOBALS['app']->Session->GetCookie('visitor_url');
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
            $company = isset($last_message['company'])? $last_message['company'] : '';
            $tpl->SetVariable('company', $company);
            $tpl->ParseBlock('contact/company');
        }

        //tel
        if (in_array('tel', $items_array)) {
            $tpl->SetBlock('contact/tel');
            $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
            $tel = isset($last_message['tel'])? $last_message['tel'] : '';
            $tpl->SetVariable('tel', $tel);
            $tpl->ParseBlock('contact/tel');
        }

        //fax
        if (in_array('fax', $items_array)) {
            $tpl->SetBlock('contact/fax');
            $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
            $fax = isset($last_message['fax'])? $last_message['fax'] : '';
            $tpl->SetVariable('fax', $fax);
            $tpl->ParseBlock('contact/fax');
        }

        //mobile
        if (in_array('mobile', $items_array)) {
            $tpl->SetBlock('contact/mobile');
            $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
            $mobile = isset($last_message['mobile'])? $last_message['mobile'] : '';
            $tpl->SetVariable('mobile', $mobile);
            $tpl->ParseBlock('contact/mobile');
        }

        //address
        if (in_array('address', $items_array)) {
            $tpl->SetBlock('contact/address');
            $tpl->SetVariable('lbl_address',  _t('CONTACT_ADDRESS'));
            $address = isset($last_message['address'])? $last_message['address'] : '';
            $tpl->SetVariable('address', $address);
            $tpl->ParseBlock('contact/address');
        }

        //recipient
        if (in_array('recipient', $items_array)) {
            $model = $this->gadget->model->load('Recipients');
            $recipients = $model->GetRecipients(true);
            if (!Jaws_Error::IsError($recipients) && !empty($recipients)) {
                $tpl->SetBlock('contact/recipient');
                $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));

                $rcpt = isset($last_message['recipient'])? $last_message['recipient'] : '';
                foreach ($recipients as $recipient) {
                    $tpl->SetBlock('contact/recipient/item');
                    $tpl->SetVariable('recipient_id',   $recipient['id']);
                    $tpl->SetVariable('recipient_name', $recipient['name']);
                    $tpl->SetVariable('selected', ($rcpt== $recipient['id'])? 'selected="selected"': '');
                    $tpl->ParseBlock('contact/recipient/item');
                }
                $tpl->ParseBlock('contact/recipient');
            }
        }

        //subject
        if (in_array('subject', $items_array)) {
            $tpl->SetBlock('contact/subject');
            $tpl->SetVariable('lbl_subject',  _t('CONTACT_SUBJECT'));
            $subject = isset($last_message['subject'])? $last_message['subject'] : '';
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
            $message = isset($last_message['message'])? $last_message['message'] : '';
            $tpl->SetVariable('message', $message);
            $tpl->ParseBlock('contact/message');
        }

        $tpl->ParseBlock('contact');
        return $tpl->Get();
    }

}