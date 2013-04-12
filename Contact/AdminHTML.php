<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Admin of Gadget
     *
     * @access  public
     * @return  string  XHTML content of administration
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManageContacts')) {
            return $this->Contacts();
        } elseif ($this->gadget->GetPermission('ManageRecipients')) {
            return $this->Recipients();
        }

        $this->gadget->CheckPermission('UpdateProperties');
        return $this->Properties();
    }

    /**
     * Prepares the contacs menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Contacts', 'Recipients', 'Mailer', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Contacts';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageContacts')) {
            $menubar->AddOption('Contacts',
                                _t('CONTACT_NAME'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Admin',
                                'gadgets/Contact/images/contact_mini.png');
        }
        if ($this->gadget->GetPermission('ManageRecipients')) {
            $menubar->AddOption('Recipients',
                                _t('CONTACT_RECIPIENTS'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Recipients',
                                'gadgets/Contact/images/recipients_mini.png');
        }
        if ($this->gadget->GetPermission('AccessToMailer')) {
            $menubar->AddOption('Mailer',
                                _t('CONTACT_MAILER'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Mailer',
                                'gadgets/Contact/images/email_send.png');
        }
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $menubar->AddOption('Properties',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Properties',
                                'gadgets/Contact/images/properties_mini.png');
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Prepares the data of contacts
     *
     * @access  public
     * @param   int    $recipient   Recipient ID
     * @param   int    $offset      Offset of data array
     * @return  array  Data array
     */
    function GetContacts($recipient = -1, $offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel');

        $contacts = $model->GetContacts($recipient, 12, $offset);
        if (Jaws_Error::IsError($contacts)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $newData = array();
        foreach ($contacts as $contact) {
            $contactData = array();

            // Name
            $label =& Piwi::CreateWidget('Label', $contact['name']);
            $label->setTitle($contact['subject']);
            if (empty($contact['reply'])) {
                $label->setStyle('font-weight:bold;');
            }
            $contactData['name'] = $label->get();

            // Attachment
            if (empty($contact['attachment'])) {
                $contactData['attach'] = '';
            } else {
                $image =& Piwi::CreateWidget('Image', 'gadgets/Contact/images/attachment.png');
                $image->setTitle($contact['attachment']);
                $contactData['attach'] = $image->get();
            }

            // Date
            $label =& Piwi::CreateWidget('Label', $date->Format($contact['createtime'],'Y-m-d'));
            $label->setTitle($date->Format($contact['createtime'],'H:i:s'));
            $contactData['time'] = $label->get();

            // Actions
            $actions = '';
            if ($this->gadget->GetPermission('ManageContacts')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editContact(this, '".$contact['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('CONTACT_CONTACTS_MESSAGE_REPLY'),
                                            "javascript: editReply(this, '" . $contact['id'] . "');",
                                            'gadgets/Contact/images/contact_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteContact(this, '".$contact['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $contactData['actions'] = $actions;
            $newData[] = $contactData;
        }
        return $newData;
    }

    /**
     * Prepares the datagrid view (XHTML of datagrid)
     *
     * @access  public
     * @return  string  XHTML template of datagrid
     */
    function ContactsDataGrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel');
        $total = $model->TotalOfData('contacts');

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('contacts_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_NAME'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', '', null, false);
        $grid->AddColumn($column2);
        $column2->SetStyle('width:16px;');
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column3->SetStyle('width: 72px; white-space: nowrap;');
        $grid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column4->SetStyle('width: 64px; white-space: nowrap;');
        $grid->AddColumn($column4);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Show contacts list
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Contacts()
    {
        $this->gadget->CheckPermission('ManageContacts');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminContacts.html');
        $tpl->SetBlock('Contacts');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Contacts'));

        //Recipient filter
        $recipientCombo =& Piwi::CreateWidget('Combo', 'recipient_filter');
        $recipientCombo->SetID('recipient_filter');
        $recipientCombo->setStyle('width: 220px;');
        $recipientCombo->AddEvent(ON_CHANGE, "getContacts('contacts_datagrid', 0, true)");
        $recipientCombo->AddOption('', -1);
        $recipientCombo->AddOption($this->gadget->GetRegistry('site_author', 'Settings'), 0);
        $model = $GLOBALS['app']->LoadGadget('Contact', 'Model');
        $recipients = $model->GetRecipients();
        if (!Jaws_Error::IsError($result)) {
            foreach ($recipients as $recipient) {
                $recipientCombo->AddOption($recipient['name'], $recipient['id']);
            }
        }
        $recipientCombo->SetDefault(-1);
        $tpl->SetVariable('lbl_recipient_filter', _t('CONTACT_RECIPIENT'));
        $tpl->SetVariable('recipient_filter', $recipientCombo->Get());
        $tpl->SetVariable('lbl_recipient_filter', _t('CONTACT_RECIPIENT'));

        //DataGrid
        $tpl->SetVariable('grid', $this->ContactsDataGrid());

        //ContactUI
        $tpl->SetVariable('contact_ui', $this->ContactUI());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('visibility: hidden;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled($this->gadget->GetPermission('ManageContacts'));
        $btnSave->AddEvent(ON_CLICK, 'updateContact(false);');
        $btnSave->SetStyle('visibility: hidden;');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnSaveSend =& Piwi::CreateWidget('Button', 'btn_save_send', _t('CONTACT_REPLAY_SAVE_SEND'), STOCK_SAVE);
        $btnSaveSend->SetEnabled($this->gadget->GetPermission('ManageContacts'));
        $btnSaveSend->AddEvent(ON_CLICK, 'updateContact(true);');
        $btnSaveSend->SetStyle('visibility: hidden;');
        $tpl->SetVariable('btn_save_send', $btnSaveSend->Get());

        $tpl->SetVariable('incompleteContactFields', _t('CONTACT_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmContactDelete',    _t('CONTACT_CONTACTS_CONFIRM_DELETE'));
        $tpl->SetVariable('legend_title',            _t('CONTACT_CONTACTS_MESSAGE_DETAILS'));
        $tpl->SetVariable('messageDetail_title',     _t('CONTACT_CONTACTS_MESSAGE_DETAILS'));
        $tpl->SetVariable('contactReply_title',      _t('CONTACT_CONTACTS_MESSAGE_REPLY'));
        $tpl->SetVariable('dataURL',                 $GLOBALS['app']->getDataURL() . 'contact/');

        $tpl->ParseBlock('Contacts');
        return $tpl->Get();
    }

    /**
     * Show a form to show/edit a given contact
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ContactUI()
    {
        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminContacts.html');
        $tpl->SetBlock('ContactUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //company
        $nameEntry =& Piwi::CreateWidget('Entry', 'company', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_company', _t('CONTACT_COMPANY'));
        $tpl->SetVariable('company', $nameEntry->Get());

        //url
        $nameEntry =& Piwi::CreateWidget('Entry', 'url', '');
        $nameEntry->setStyle('width: 310px;');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $nameEntry->Get());

        //tel
        $nameEntry =& Piwi::CreateWidget('Entry', 'tel', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
        $tpl->SetVariable('tel', $nameEntry->Get());

        //fax
        $nameEntry =& Piwi::CreateWidget('Entry', 'fax', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
        $tpl->SetVariable('fax', $nameEntry->Get());

        //mobile
        $nameEntry =& Piwi::CreateWidget('Entry', 'mobile', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
        $tpl->SetVariable('mobile', $nameEntry->Get());

        //address
        $nameEntry =& Piwi::CreateWidget('Entry', 'address', '');
        $nameEntry->setStyle('width: 310px;');
        $tpl->SetVariable('lbl_address', _t('CONTACT_ADDRESS'));
        $tpl->SetVariable('address', $nameEntry->Get());

        //recipient
        $recipientCombo =& Piwi::CreateWidget('Combo', 'rid');
        $recipientCombo->SetID('rid');
        $recipientCombo->setStyle('width: 318px;');
        $recipientCombo->AddOption($this->gadget->GetRegistry('site_author', 'Settings'), 0);
        $model = $GLOBALS['app']->LoadGadget('Contact', 'Model');
        $recipients = $model->GetRecipients();
        if (!Jaws_Error::IsError($result)) {
            foreach ($recipients as $recipient) {
                $recipientCombo->AddOption($recipient['name'], $recipient['id']);
            }
        }
        $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));
        $tpl->SetVariable('recipient', $recipientCombo->Get());

        //subject
        $subjectEntry =& Piwi::CreateWidget('Entry', 'subject', '');
        $subjectEntry->setStyle('width: 310px;');
        $tpl->SetVariable('lbl_subject', _t('CONTACT_SUBJECT'));
        $tpl->SetVariable('subject', $subjectEntry->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetStyle('width: 310px;');
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('CONTACT_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());

        $tpl->ParseBlock('ContactUI');
        return $tpl->Get();
    }

    /**
     * Show a form to edit/send contact reply
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReplyUI()
    {
        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminContacts.html');
        $tpl->SetBlock('ReplyUI');

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $nameEntry->setStyle('width: 160px;');
        $nameEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $nameEntry->setStyle('width: 160px;');
        $nameEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //subject
        $subjectEntry =& Piwi::CreateWidget('Entry', 'subject', '');
        $subjectEntry->setStyle('width: 310px;');
        $subjectEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_subject', _t('CONTACT_SUBJECT'));
        $tpl->SetVariable('subject', $subjectEntry->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetStyle('width: 310px;');
        $messageText->SetReadOnly(true);
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('CONTACT_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());

        //reply
        $replyText =& Piwi::CreateWidget('TextArea', 'reply','');
        $replyText->SetStyle('width: 310px;');
        $replyText->SetRows(10);
        $tpl->SetVariable('lbl_reply', _t('CONTACT_REPLY'));
        $tpl->SetVariable('reply', $replyText->Get());

        $tpl->ParseBlock('ReplyUI');
        return $tpl->Get();
    }

    /**
     * Send contact reply
     *
     * @access  public
     * @param   int     $cid    Contact ID
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function SendReply($cid)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel');
        $contact = $model->GetReply($cid);
        if (Jaws_Error::IsError($contact)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'),
                                                       RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('CONTACT_NAME'));
        }

        if (!isset($contact['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'),
                                                       RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'), _t('CONTACT_NAME'));
        }

        $from_name  = '';
        $from_email = '';
        $to  = $contact['email'];
        $rid = $contact['recipient'];
        if ($rid != 0) {
            $recipient = $model->GetRecipient($rid);
            if (Jaws_Error::IsError($recipient)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'),
                                                           RESPONSE_ERROR);
                return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('CONTACT_NAME'));
            }
            if (!isset($recipient['id'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'),
                                                           RESPONSE_ERROR);
                return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'), _t('CONTACT_NAME'));
            }
            $from_name  = $recipient['name'];
            $from_email = $recipient['email'];
        }

        $format = $this->gadget->GetRegistry('email_format');
        if ($format == 'html') {
            require_once JAWS_PATH . 'include/Jaws/String.php';
            $reply = $this->gadget->ParseText($contact['reply']);
        } else {
            $reply = $contact['reply'];
        }

        $jDate = $GLOBALS['app']->loadDate();
        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->GetRegistry('site_name', 'Settings');
        $site_language = $this->gadget->GetRegistry('site_language', 'Settings');
        $profile_url = $GLOBALS['app']->getSiteURL('/'). $GLOBALS['app']->Map->GetURLFor(
            'Users',
            'Profile',
            array('user' => $GLOBALS['app']->Session->GetAttribute('username'))
        );
        $GLOBALS['app']->Translate->LoadTranslation('Global', JAWS_COMPONENT_OTHERS, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Contact', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('SendReplyTo.html', false, true, _t_lang($site_language, 'GLOBAL_LANG_DIRECTION'));
        $tpl->SetBlock($format);

        $tpl->SetVariable('lbl_name',    _t_lang($site_language, 'GLOBAL_NAME'));
        $tpl->SetVariable('lbl_email',   _t_lang($site_language, 'GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_message', _t_lang($site_language, 'CONTACT_MESSAGE'));
        $tpl->SetVariable('lbl_reply',   _t_lang($site_language, 'CONTACT_REPLY'));
        $tpl->SetVariable('name',        $contact['name']);
        $tpl->SetVariable('email',       $contact['email']);
        $tpl->SetVariable('subject',     $contact['subject']);
        $tpl->SetVariable('message',     $contact['msg_txt']);
        $tpl->SetVariable('reply',       $reply);
        $tpl->SetVariable('createtime',  $jDate->Format($contact['createtime']));
        $tpl->SetVariable('nickname',    $GLOBALS['app']->Session->GetAttribute('nickname'));
        $tpl->SetVariable('profile_url', $profile_url);
        $tpl->SetVariable('site-name',   $site_name);
        $tpl->SetVariable('site-url',    $site_url);
        $tpl->ParseBlock($format);
        $template = $tpl->Get();
        $subject = _t_lang($site_language, 'CONTACT_REPLY_TO', Jaws_XSS::defilter($contact['subject']));

        require_once JAWS_PATH . 'include/Jaws/Mail.php';
        $mail = new Jaws_Mail;
        $mail->SetFrom($from_email, $from_name);
        $mail->AddRecipient($to);
        $mail->AddRecipient('', 'cc');
        $mail->SetSubject($subject);
        $mail->SetBody($template, $format);
        $result = $mail->send();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_REPLY_NOT_SENT'), RESPONSE_ERROR);
            return false;
        }

        $model->UpdateReplySent($cid, true);
        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_REPLY_SENT'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Prepares the data of recipient
     *
     * @access  public
     * @param   int    $offset  offset of data
     * @return  array  Data array
     */
    function GetRecipients($offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel');

        $recipients = $model->GetRecipients(false, 10, $offset);
        if (Jaws_Error::IsError($recipients)) {
            return array();
        }

        $newData = array();
        foreach ($recipients as $recipient) {
            $recipientData = array();
            $recipientData['name']  = $recipient['name'];
            $recipientData['email'] = $recipient['email'];
            $recipientData['visible'] = ($recipient['visible']?_t('GLOBAL_YES') : _t('GLOBAL_NO'));
            $actions = '';
            if ($this->gadget->GetPermission('ManageRecipients')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editRecipient(this, '".$recipient['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteRecipient(this, '".$recipient['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $recipientData['actions'] = $actions;
            $newData[] = $recipientData;
        }
        return $newData;
    }

    /**
     * Prepares the datagrid view (XHTML of datagrid)
     *
     * @access  public
     * @return  string XHTML template of datagrid
     */
    function RecipientsDataGrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Contact', 'AdminModel');
        $total = $model->TotalOfData('contacts_recipients');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->SetID('recipient_datagrid');
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_EMAIL'), null, false);
        $column2->SetStyle('width: 160px; white-space:nowrap;');
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
        $column3->SetStyle('width: 56px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column4->SetStyle('width: 60px; white-space: nowrap;');
        $datagrid->AddColumn($column4);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');


        return $datagrid->Get();
    }

    /**
     * Show recipients list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Recipients()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminRecipients.html');
        $tpl->SetBlock('recipients');

        $tpl->SetVariable('menubar', $this->MenuBar('Recipients'));
        $tpl->SetVariable('grid', $this->RecipientsDataGrid());

        // Tabs titles
        $tpl->SetVariable('legend_title', _t('CONTACT_RECIPIENTS_ADD'));

        $titleentry =& Piwi::CreateWidget('Entry', 'name', '');
        $titleentry->setStyle('width: 250px;');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('name', $titleentry->Get());

        $emailentry =& Piwi::CreateWidget('Entry', 'email', '');
        $emailentry->setStyle('direction: ltr; width: 250px;');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $emailentry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'tel', '');
        $entry->setStyle('width: 250px;');
        $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
        $tpl->SetVariable('tel', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fax', '');
        $entry->setStyle('width: 250px;');
        $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
        $tpl->SetVariable('fax', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'mobile', '');
        $entry->setStyle('width: 250px;');
        $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
        $tpl->SetVariable('mobile', $entry->Get());

        $informType =& Piwi::CreateWidget('Combo', 'inform_type');
        $informType->SetID('inform_type');
        $informType->setStyle('width: 164px;');
        $informType->AddOption(_t('GLOBAL_DISABLE'), 0);
        $informType->AddOption(_t('GLOBAL_EMAIL'),   1);
        $informType->SetDefault(0);
        $tpl->SetVariable('lbl_inform_type', _t('CONTACT_RECIPIENTS_INFORM_TYPE'));
        $tpl->SetVariable('inform_type', $informType->Get());

        $visibleType =& Piwi::CreateWidget('Combo', 'visible');
        $visibleType->SetID('visible');
        $visibleType->setStyle('width: 80px;');
        $visibleType->AddOption(_t('GLOBAL_NO'),  0);
        $visibleType->AddOption(_t('GLOBAL_YES'), 1);
        $visibleType->SetDefault(1);
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE'));
        $tpl->SetVariable('visible', $visibleType->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled($this->gadget->GetPermission('ManageRecipients'));
        $btnSave->AddEvent(ON_CLICK, 'updateRecipient();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('incompleteRecipientFields', _t('CONTACT_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmRecipientDelete',    _t('CONTACT_CONFIRM_DELETE_RECIPIENT'));

        $tpl->ParseBlock('recipients');

        return $tpl->Get();
    }

    /**
     * Show contacts Setting
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Properties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminProperties.html');
        $tpl->SetBlock('Properties');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $use_antispam = $this->gadget->GetRegistry('use_antispam');
        $antispamCombo =& Piwi::CreateWidget('Combo', 'use_antispam');
        $antispamCombo->SetID('use_antispam');
        $antispamCombo->setStyle('width: 140px;');
        $antispamCombo->AddOption(_t('GLOBAL_NO'),  'false');
        $antispamCombo->AddOption(_t('GLOBAL_YES'), 'true');
        $antispamCombo->SetDefault($use_antispam);
        $tpl->SetVariable('lbl_use_antispam', _t('CONTACT_PROPERTIES_USE_ANTISPAM'));
        $tpl->SetVariable('use_antispam', $antispamCombo->Get());

        $email_format = $this->gadget->GetRegistry('email_format');
        $formatCombo =& Piwi::CreateWidget('Combo', 'email_format');
        $formatCombo->SetID('email_format');
        $formatCombo->setStyle('width: 140px;');
        $formatCombo->AddOption(_t('CONTACT_PROPERTIES_EMAIL_FORMAT_PLAINTEXT'), 'text');
        $formatCombo->AddOption(_t('CONTACT_PROPERTIES_EMAIL_FORMAT_HTML'),      'html');
        $formatCombo->SetDefault($email_format);
        $tpl->SetVariable('lbl_email_format', _t('CONTACT_PROPERTIES_EMAIL_FORMAT'));
        $tpl->SetVariable('email_format', $formatCombo->Get());

        $attachment = $this->gadget->GetRegistry('enable_attachment');
        $combo =& Piwi::CreateWidget('Combo', 'enable_attachment');
        $combo->setStyle('width: 140px;');
        $combo->AddOption(_t('GLOBAL_NO'), 'false');
        $combo->AddOption(_t('GLOBAL_YES'), 'true');
        $combo->SetDefault($attachment);
        $tpl->SetVariable('lbl_enable_attachment', _t('CONTACT_PROPERTIES_ENABLE_ATTACHMENT'));
        $tpl->SetVariable('enable_attachment', $combo->Get());

        // Comments
        $comments = $this->gadget->GetRegistry('comments');
        $editor =& $GLOBALS['app']->LoadEditor('Contact', 'comments', $comments, false);
        $editor->SetId('comments');
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('95%');
        $tpl->SetVariable('lbl_comments', _t('CONTACT_PROPERTIES_COMMENTS'));
        $tpl->SetVariable('comments', $editor->Get());

        if ($this->gadget->GetPermission('UpdateSetting')) {
            $btnupdate =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnupdate->AddEvent(ON_CLICK, 'updateProperties();');
            $tpl->SetVariable('btn_save', $btnupdate->Get());
        }

        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }

    /**
     * Prepares UI for sending Email
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Mailer()
    {
        $this->gadget->CheckPermission('AccessToMailer');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('AdminMailer.html');
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

        require_once JAWS_PATH . 'include/Jaws/User.php';
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
        $entry->SetStyle('direction:ltr;');
        $tpl->SetVariable('to', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_TO'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_to', $label->Get());

        // Cc
        $entry =& Piwi::CreateWidget('Entry', 'cc');
        $entry->SetStyle('direction:ltr;');
        $tpl->SetVariable('cc', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_CC'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_cc', $label->Get());

        // Bcc
        $entry =& Piwi::CreateWidget('Entry', 'bcc');
        $entry->SetStyle('direction:ltr;');
        $tpl->SetVariable('bcc', $entry->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_BCC'), $entry);
        $label->SetID('');
        $tpl->SetVariable('lbl_bcc', $label->Get());

        // From
        $from_title = $this->gadget->GetRegistry('gate_title', 'Settings');
        $from_email = $this->gadget->GetRegistry('gate_email', 'Settings');
        if (!empty($from_email)) {
            $from = !empty($from_title)? "$from_title <$from_email>" : $from_email;
        } else {
            $from = '';
        }
        $entry =& Piwi::CreateWidget('Entry', 'from', $from);
        $entry->SetStyle('direction:ltr;');
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
        $editor->SetWidth('100%');
        $editor->TextArea->SetStyle('width:520px;');
        $tpl->SetVariable('message', $editor->Get());
        $label =& Piwi::CreateWidget('Label', _t('CONTACT_MAILER_MESSAGE'), $editor->TextArea);
        $label->SetID('');
        $tpl->SetVariable('lbl_message', $label->Get());

        // Actions
        $button =& Piwi::CreateWidget('Button',
                                      'btn_new',
                                      _t('CONTACT_MAILER_BUTTON_NEW'),
                                      'gadgets/Contact/images/contact_mini.png');
        $button->AddEvent(ON_CLICK, 'newEmail();');
        $tpl->SetVariable('btn_new', $button->Get());

        $button =& Piwi::CreateWidget('Button',
                                      'btn_preview',
                                      _t('CONTACT_MAILER_BUTTON_PREVIEW'),
                                      'gadgets/Contact/images/email_preview.png');
        $button->AddEvent(ON_CLICK, 'previewMessage();');
        $tpl->SetVariable('btn_preview', $button->Get());

        $button =& Piwi::CreateWidget('Button',
                                      'btn_send',
                                      _t('CONTACT_MAILER_BUTTON_SEND'),
                                      'gadgets/Contact/images/email_send.png');
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
        $format = $this->gadget->GetRegistry('email_format');
        if ($format == 'html') {
            require_once JAWS_PATH . 'include/Jaws/String.php';
            $message = $this->gadget->ParseText($message);
        } else {
            $message = strip_tags($message);
        }

        $site_language = $this->gadget->GetRegistry('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Global',  JAWS_COMPONENT_OTHERS, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Contact', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = new Jaws_Template('gadgets/Contact/templates/');
        $tpl->Load('SendEmail.html', null, true, _t_lang($site_language, 'GLOBAL_LANG_DIRECTION'));
        $tpl->SetBlock($format);

        $tpl->SetVariable('message', $message);

        $site_name = $this->gadget->GetRegistry('site_name', 'Settings');
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
        require_once JAWS_PATH . 'include/Jaws/Mail.php';
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
            require_once JAWS_PATH . 'include/Jaws/User.php';
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
        $format = $this->gadget->GetRegistry('email_format');
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