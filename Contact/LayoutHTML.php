<?php
/**
 * Contact Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Contact
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ContactLayoutHTML extends Jaws_Gadget_HTML
{
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
                                            $this->gadget->GetRegistry('default_items')));
                break;
        }

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('Contact.html');
        $tpl->SetBlock('contact');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('CONTACT_US'));
        $comments = $this->gadget->GetRegistry('comments');
        $tpl->SetVariable('comments', $this->gadget->ParseText($comments, 'Contact'));
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

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
            $tpl->SetBlock('contact/captcha');
            $tpl->SetVariable('lbl_captcha', $label);
            $tpl->SetVariable('captcha', $captcha);
            if (!empty($entry)) {
                $tpl->SetVariable('captchavalue', $entry);
            }
            $tpl->SetVariable('captcha_msg', $description);
            $tpl->ParseBlock('contact/captcha');
        }

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
            $model = $GLOBALS['app']->LoadGadget('Contact', 'Model');
            $recipients = $model->GetRecipients(true);
            if (Jaws_Error::IsError($recipients) || empty($recipients)) {
                $recipients   = array();
                $recipients[] = array('id'   => 0,
                                      'name' => $this->gadget->GetRegistry('site_author', 'Settings'));
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
            ($this->gadget->GetRegistry('enable_attachment') == 'true') &&
            $GLOBALS['app']->Session->GetPermission('Contact', 'AllowAttachment'))
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
     * Show default contact us form
     *
     * @access  public
     * @return  string   XHTML templatecontent
     */
    function Display()
    {
        return $this->GetContactForm('');
    }

    /**
     * Show mini contact us form
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DisplayMini()
    {
        return $this->GetContactForm('mini');
    }

    /**
     * Show simple contact us form
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DisplaySimple()
    {
        return $this->GetContactForm('simple');
    }

    /**
     * Show full contact us form
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DisplayFull()
    {
        return $this->GetContactForm('full');
    }
}
