<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Contact extends Jaws_Gadget_Action
{
    /**
     * Show default contact form
     *
     * @access  public
     * @return  string   XHTML template content
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

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('Contact.html');
        $tpl->SetBlock('contact');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('CONTACT_US'));
        $comments = $this->gadget->registry->fetch('comments');
        $tpl->SetVariable('comments', $this->gadget->plugin->parseAdmin($comments));
        $tpl->SetVariable('send', _t('CONTACT_SEND'));

        $btnSend =& Piwi::CreateWidget('Button', 'send', _t('CONTACT_SEND'));
        $btnSend->SetSubmit();
        $tpl->SetVariable('btn_send', $btnSend->Get());

        $response = $this->gadget->session->pop('Contact');
        if (isset($response['data'])) {
            $message = $response['data'];
        } else {
            $message = array(
                'name'      => $this->app->session->getCookie('visitor_name'),
                'email'     => $this->app->session->getCookie('visitor_email'),
                'url'       => $this->app->session->getCookie('visitor_url')?: 'http://',
                'company'   => '',
                'tel'       => '',
                'fax'       => '',
                'mobile'    => '',
                'address'   => '',
                'recipient' => '',
                'subject'   => '',
                'message'   => '',
            );
        }

        if (!empty($response)) {
            $tpl->SetVariable('response_text', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        if (!$this->app->session->user->logged) {
            //name
            if (in_array('name', $items_array)) {
                $tpl->SetBlock('contact/name');
                $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
                $tpl->SetVariable('name', $message['name']);
                $tpl->ParseBlock('contact/name');
            }

            //email
            if (in_array('email', $items_array)) {
                $tpl->SetBlock('contact/email');
                $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('email', $message['email']);
                $tpl->ParseBlock('contact/email');
            }

            //url
            if (in_array('url', $items_array)) {
                $tpl->SetBlock('contact/url');
                $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
                $tpl->SetVariable('url', $message['url']);
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
            $tpl->SetVariable('company', $message['company']);
            $tpl->ParseBlock('contact/company');
        }

        //tel
        if (in_array('tel', $items_array)) {
            $tpl->SetBlock('contact/tel');
            $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
            $tpl->SetVariable('tel', $message['tel']);
            $tpl->ParseBlock('contact/tel');
        }

        //fax
        if (in_array('fax', $items_array)) {
            $tpl->SetBlock('contact/fax');
            $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
            $tpl->SetVariable('fax', $message['fax']);
            $tpl->ParseBlock('contact/fax');
        }

        //mobile
        if (in_array('mobile', $items_array)) {
            $tpl->SetBlock('contact/mobile');
            $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
            $tpl->SetVariable('mobile', $message['mobile']);
            $tpl->ParseBlock('contact/mobile');
        }

        //address
        if (in_array('address', $items_array)) {
            $tpl->SetBlock('contact/address');
            $tpl->SetVariable('lbl_address',  _t('CONTACT_ADDRESS'));
            $tpl->SetVariable('address', $message['address']);
            $tpl->ParseBlock('contact/address');
        }

        //recipient
        if (in_array('recipient', $items_array)) {
            $model = $this->gadget->model->load('Recipients');
            $recipients = $model->GetRecipients(true);
            if (!Jaws_Error::IsError($recipients) && !empty($recipients)) {
                $tpl->SetBlock('contact/recipient');
                $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));

                foreach ($recipients as $recipient) {
                    $tpl->SetBlock('contact/recipient/item');
                    $tpl->SetVariable('recipient_id',   $recipient['id']);
                    $tpl->SetVariable('recipient_name', $recipient['name']);
                    $tpl->SetVariable(
                        'selected',
                        ($message['recipient'] == $recipient['id'])? 'selected="selected"': ''
                    );
                    $tpl->ParseBlock('contact/recipient/item');
                }
                $tpl->ParseBlock('contact/recipient');
            }
        }

        //subject
        if (in_array('subject', $items_array)) {
            $tpl->SetBlock('contact/subject');
            $tpl->SetVariable('lbl_subject',  _t('CONTACT_SUBJECT'));
            $tpl->SetVariable('subject', $message['subject']);
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
            $tpl->SetVariable('message', $message['message']);
            $tpl->ParseBlock('contact/message');
        }

        $tpl->ParseBlock('contact');
        return $tpl->Get();
    }

}