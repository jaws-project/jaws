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
                $items_array = array(
                    'name', 'email', 'recipient', 'subject', 'message'
                );
                break;
            case 'simple':
                $items_array = array(
                    'name', 'email', 'url',  'tel', 'recipient', 'subject', 'attachment', 'message'
                );
                break;
            case 'full':
                $items_array = array(
                    'name', 'email', 'company', 'url', 'tel', 
                    'fax', 'mobile', 'address', 'recipient', 'subject', 'attachment', 'message'
                );
                break;
            default:
                $items_array = array_filter(
                    explode(',', $this->gadget->registry->fetch('default_items'))
                );
                break;
        }

        $this->AjaxMe('index.js');

        $response = $this->gadget->session->pop('Contact');
        if (isset($response['data'])) {
            $message = $response['data'];
        } else {
            $message = array(
                'name'      => $this->app->session->getCookie('visitor_name')?: '',
                'email'     => $this->app->session->getCookie('visitor_email')?: '',
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
        // remove invalid keys
        $message = array_intersect_key($message, array_fill_keys($items_array, 0));

        $assigns = array();
        $assigns = array_merge($assigns, $message);
        $assigns['response'] = $response;
        $assigns['recipients'] = $this->gadget->model->load('Recipients')->GetRecipients(true);
        $assigns['selected_recipient'] = $message['recipient'];
        $assigns['comments'] = $this->gadget->registry->fetch('comments');

        //attachment
        if (in_array('attachment', $items_array) &&
            ($this->gadget->registry->fetch('enable_attachment') == 'true') &&
            $this->gadget->GetPermission('AllowAttachment'))
        {
            //
        }
        // captcha
        $assigns['captcha'] = Jaws_Gadget::getInstance('Policy')
            ->action
            ->load('Captcha')
            ->xloadCaptcha();

        return $this->gadget->template->xLoad('Contact.html')->render($assigns);
    }

}