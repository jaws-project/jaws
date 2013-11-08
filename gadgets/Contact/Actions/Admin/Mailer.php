<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Admin_Mailer extends Contact_Actions_Admin_Default
{
    /**
     * Builds Mailer UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Mailer()
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Mailer.html');
        $tpl->SetBlock('mailer');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Mailer'));

        // Options
        $radio =& Piwi::CreateWidget('RadioButtons', 'options', 'horizontal');
        $radio->AddOption(_t('CONTACT_MAILER_SEND_TO_USERS'), 1);
        $radio->AddOption(_t('CONTACT_MAILER_SEND_TO_ADDRESS'), 2);
        $radio->SetDefault(1);
        $radio->AddEvent(ON_CLICK, 'switchEmailTarget(this.value);');
        $tpl->SetVariable('options', $radio->Get());

        $userModel = new Jaws_User();

        // Group
        $groups = $userModel->GetGroups();
        $combo =& Piwi::CreateWidget('Combo', 'groups');
        $combo->AddEvent(ON_CHANGE, 'updateUsers(this.value)');
        $combo->AddOption(_t('CONTACT_MAILER_ALL_GROUPS'), 0);
        foreach($groups as $group) {
            $combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('groups', $combo->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_GROUP'), $combo);
        $label->SetID('');
        $tpl->SetVariable('lbl_group', $label->Get());

        // Users
        $users = $userModel->GetUsers();
        $combo =& Piwi::CreateWidget('Combo', 'users');
        $combo->AddOption(_t('CONTACT_MAILER_ALL_GROUP_USERS'), 0);
        foreach($users as $user) {
            $combo->AddOption($user['nickname'], $user['id']);
        }
        $tpl->SetVariable('users', $combo->Get());
        $tpl->SetVariable('target_user', count($users));
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_USER'), $combo);
        $label->SetID('');
        $tpl->SetVariable('lbl_user', $label->Get());

        // To
        $entry =& Piwi::CreateWidget('Entry', 'to');
        $tpl->SetVariable('to', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_TO'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_to', $label->Get());

        // Cc
        $entry =& Piwi::CreateWidget('Entry', 'cc');
        $tpl->SetVariable('cc', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_CC'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_cc', $label->Get());

        // Bcc
        $entry =& Piwi::CreateWidget('Entry', 'bcc');
        $tpl->SetVariable('bcc', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_BCC'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_bcc', $label->Get());

        // From
        $from_title = $this->gadget->registry->fetch('gate_title', 'Settings');
        $from_email = $this->gadget->registry->fetch('gate_email', 'Settings');
        if (!empty($from_email)) {
            $from = !empty($from_title)? "$from_title <$from_email>" : $from_email;
        } else {
            $from = '';
        }
        $entry =& Piwi::CreateWidget('Entry', 'from', $from);
        $entry->SetEnabled(false);
        $tpl->SetVariable('from', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_FROM'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_from', $label->Get());

        // Subject
        $entry =& Piwi::CreateWidget('Entry', 'subject');
        $tpl->SetVariable('subject', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_SUBJECT'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_subject', $label->Get());

        // Attachment
        $entry =& Piwi::CreateWidget('FileEntry', 'attachment', '');
        $entry->SetID('attachment');
        $entry->SetSize(1);
        $entry->AddEvent(ON_CHANGE, 'uploadFile();');
        $tpl->SetVariable('attachment', $entry->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_upload', _t('CONTACT_MAILER_ADD_ATTACHMENT'));
        $tpl->SetVariable('btn_upload', $button->Get());

        $link =& Piwi::CreateWidget('Link',
                                    _t('CONTACT_MAILER_REMOVE_ATTACHMENT'),
                                    'javascript:removeAttachment();',
                                    'images/stock/cancel.png');
        $tpl->SetVariable('remove', $link->get());

        // Message
        $editor =& $GLOBALS['app']->LoadEditor('Contact', 'message');
        $editor->setID('message');
        $editor->SetWidth('1000px');
        $tpl->SetVariable('message', $editor->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_MESSAGE'), $editor->TextArea);
        $label->SetID('');
        $tpl->SetVariable('lbl_message', $label->Get());

        // Actions
        $button =& Piwi::CreateWidget('Button',
                                      'btn_new',
                                      _t('CONTACT_MAILER_BUTTON_NEW'),
                                      'gadgets/Contact/Resources/images/contact_mini.png');
        $button->AddEvent(ON_CLICK, 'newEmail();');
        $tpl->SetVariable('btn_new', $button->Get());

        $button =& Piwi::CreateWidget('Button',
                                      'btn_preview',
                                      _t('CONTACT_MAILER_BUTTON_PREVIEW'),
                                      'gadgets/Contact/Resources/images/email_preview.png');
        $button->AddEvent(ON_CLICK, 'previewMessage();');
        $tpl->SetVariable('btn_preview', $button->Get());

        $button =& Piwi::CreateWidget('Button',
                                      'btn_send',
                                      _t('CONTACT_MAILER_BUTTON_SEND'),
                                      'gadgets/Contact/Resources/images/email_send.png');
        $button->AddEvent(ON_CLICK, 'sendEmail();');
        $tpl->SetVariable('btn_send', $button->Get());

        $tpl->SetVariable('lblAllGroupUsers', _t('CONTACT_MAILER_ALL_GROUP_USERS'));
        $tpl->SetVariable('groupHasNoUser', _t('CONTACT_ERROR_GROUP_HAS_NO_USER'));
        $tpl->SetVariable('incompleteMailerFields', _t('CONTACT_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('mailer');
        return $tpl->Get();
    }

    /**
     * Uploads attachment file
     *
     * @access  public
     * @return  string  javascript script segment
     */
    function UploadFile()
    {
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir());
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } elseif (empty($res)) {
            $response = array('type'    => 'error',
                              'message' => _t('GLOBAL_ERROR_UPLOAD_4'));
        } else {
            $response = array('type'    => 'notice',
                              'filename' => $res['attachment'][0]['host_filename'],
                              'filesize' => Jaws_Utils::FormatSize($_FILES['attachment']['size']));
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

    /**
     * Prepares the message body of the Email
     *
     * @access  public
     * @param   string  $message  Body part of the Email
     * @return  string  XHTML template content
     */
    function PrepareMessage($message)
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $format = $this->gadget->registry->fetch('email_format');
        if ($format == 'html') {
            $message = $this->gadget->ParseText($message);
        } else {
            $message = strip_tags($message);
        }

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Global',  JAWS_COMPONENT_OTHERS, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Contact', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = $this->gadget->template->load('SendEmail.html',
            array(
                'loadFromTheme' => true,
                'loadRTLDirection' => _t_lang($site_language, 'GLOBAL_LANG_DIRECTION') == 'rtl',
            )
        );

        $tpl->SetBlock($format);
        $tpl->SetVariable('message', $message);

        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');
        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url', $site_url);

        $tpl->ParseBlock($format);
        return $tpl->Get();
    }

    /**
     * Sends the Email
     *
     * @access  public
     * @param   string  $target     JSON decoded array ([to, cc, bcc] or [user, group])
     * @param   string  $subject    Subject of the Email
     * @param   string  $message    Message body of the Email
     * @param   string  $attachment Attachment
     * @return  string  XHTML template content
     */
    function SendEmail($target, $subject, $message, $attachment)
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $mail = new Jaws_Mail;
        $mail->SetFrom();
        $mail->SetSubject(Jaws_XSS::defilter($subject));

        // To, Cc, Bcc
        if (isset($target['to'])) {
            if (!empty($target['to'])) {
                $recipients = explode(',', $target['to']);
                foreach ($recipients as $recpt) {
                    $mail->AddRecipient($recpt, 'To');
                }
            }
            if (!empty($target['cc'])) {
                $recipients = explode(',', $target['cc']);
                foreach ($recipients as $recpt) {
                    $mail->AddRecipient($recpt, 'Cc');
                }
            }
            if (!empty($target['bcc'])) {
                $recipients = explode(',', $target['bcc']);
                foreach ($recipients as $recpt) {
                    $mail->AddRecipient($recpt, 'Bcc');
                }
            }
        } else {
            $userModel = new Jaws_User();
            if ($target['user'] != 0) {
                $user = $userModel->GetUser((int)$target['user']);
                if (!Jaws_Error::IsError($user)) {
                    $mail->AddRecipient($user['nickname'] . ' <' . $user['email'] . '>', 'To');
                }
            } else {
                if ($target['group'] == 0) {$target['group'] = false;}
                $users = $userModel->GetUsers($target['group'], null, true);
                foreach ($users as $user) {
                    $mail->AddRecipient($user['nickname'] . ' <' . $user['email'] . '>', 'Bcc');
                }
            }
        }

        $message = $this->PrepareMessage($message);
        $format = $this->gadget->registry->fetch('email_format');
        $mail->SetBody($message, $format);
        if (!empty($attachment)) {
            $attachment = Jaws_Utils::upload_tmp_dir() . '/' . $attachment;
            if (file_exists($attachment)) {
                $mail->SetBody($attachment, 'file');
                Jaws_Utils::Delete($attachment);
            }
        }

        $result = $mail->send();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_EMAIL_NOT_SENT'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_NOTICE_EMAIL_SENT'), RESPONSE_NOTICE);
        return true;
    }
}