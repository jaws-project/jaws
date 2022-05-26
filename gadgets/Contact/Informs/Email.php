<?php
/**
 * Contact Gadget
 *
 * @category    Gadget
 * @package     Contact
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Informs_Email extends Jaws_Gadget_Action
{
    /**
     * Send email to recipient
     *
     * @access  public
     * @param   array   $recipient  Recipient data array
     * @param   array   $contact    Contact data array
     * @return  mixed    True on Success or Jaws_Error on Failure
     */
    function SendToRecipient($recipient, $contact)
    {
        $from_name  = $contact['name'];
        $from_email = $contact['email'];
        $site_url   = $this->app->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');
        if (!array_key_exists('email', $recipient)) {
            $recipient['email'] = $this->gadget->registry->fetch('site_email', 'Settings');
        }

        $format = $this->gadget->registry->fetch('email_format');
        if ($format == 'html') {
            $message = Jaws_String::AutoParagraph($contact['message']);
        } else {
            $message = $contact['message'];
        }

        $tpl = $this->gadget->template->load('SendToRecipient.html');
        $tpl->SetBlock($format);
        $tpl->SetVariable('lbl_name',      Jaws::t('NAME'));
        $tpl->SetVariable('lbl_email',     Jaws::t('EMAIL'));
        $tpl->SetVariable('lbl_company',   $this::t('COMPANY'));
        $tpl->SetVariable('lbl_url',       Jaws::t('URL'));
        $tpl->SetVariable('lbl_tel',       $this::t('TEL'));
        $tpl->SetVariable('lbl_fax',       $this::t('FAX'));
        $tpl->SetVariable('lbl_mobile',    $this::t('MOBILE'));
        $tpl->SetVariable('lbl_address',   $this::t('ADDRESS'));
        $tpl->SetVariable('lbl_recipient', $this::t('RECIPIENT'));
        $tpl->SetVariable('lbl_subject',   $this::t('SUBJECT'));
        $tpl->SetVariable('lbl_message',   $this::t('MESSAGE'));
        $tpl->SetVariable('name',          $contact['name']);
        $tpl->SetVariable('email',         $contact['email']);
        $tpl->SetVariable('company',       $contact['company']);
        $tpl->SetVariable('url',           $contact['url']);
        $tpl->SetVariable('tel',           $contact['tel']);
        $tpl->SetVariable('fax',           $contact['fax']);
        $tpl->SetVariable('mobile',        $contact['mobile']);
        $tpl->SetVariable('address',       $contact['address']);
        $tpl->SetVariable('recipient',     $recipient['email']);
        $tpl->SetVariable('subject',       $contact['subject']);
        $tpl->SetVariable('message',       $message);

        $tpl->SetVariable('site-name',     $site_name);
        $tpl->SetVariable('site-url',      $site_url);
        $tpl->ParseBlock($format);
        $template = $tpl->Get();

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom($from_email, $from_name);
        $mail->AddRecipient($recipient['email']);
        $mail->SetSubject(Jaws_XSS::defilter($contact['subject']));
        $mail->SetBody($template, array('format' => $format));
        $result = $mail->send();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

}