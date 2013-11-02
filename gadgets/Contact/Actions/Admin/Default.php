<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Admin_Default extends Jaws_Gadget_Action
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
            $gadget = $this->gadget->loadAdminAction('Contacts');
            return $gadget->Contacts();
        } elseif ($this->gadget->GetPermission('ManageRecipients')) {
            $gadget = $this->gadget->loadAdminAction('Recipients');
            return $gadget->Recipients();
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

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageContacts')) {
            $menubar->AddOption('Contacts',
                                _t('CONTACT_NAME'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Admin',
                                'gadgets/Contact/Resources/images/contact_mini.png');
        }
        if ($this->gadget->GetPermission('ManageRecipients')) {
            $menubar->AddOption('Recipients',
                                _t('CONTACT_RECIPIENTS'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Recipients',
                                'gadgets/Contact/Resources/images/recipients_mini.png');
        }
        if ($this->gadget->GetPermission('AccessToMailer')) {
            $menubar->AddOption('Mailer',
                                _t('CONTACT_MAILER'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Mailer',
                                'gadgets/Contact/Resources/images/email_send.png');
        }
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $menubar->AddOption('Properties',
                                _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Contact&amp;action=Properties',
                                'gadgets/Contact/Resources/images/properties_mini.png');
        }

        $menubar->Activate($action);
        return $menubar->Get();
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
        $tpl = $this->gadget->loadAdminTemplate('Properties.html');
        $tpl->SetBlock('Properties');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $use_antispam = $this->gadget->registry->fetch('use_antispam');
        $antispamCombo =& Piwi::CreateWidget('Combo', 'use_antispam');
        $antispamCombo->SetID('use_antispam');
        $antispamCombo->AddOption(_t('GLOBAL_NO'),  'false');
        $antispamCombo->AddOption(_t('GLOBAL_YES'), 'true');
        $antispamCombo->SetDefault($use_antispam);
        $tpl->SetVariable('lbl_use_antispam', _t('CONTACT_PROPERTIES_USE_ANTISPAM'));
        $tpl->SetVariable('use_antispam', $antispamCombo->Get());

        $email_format = $this->gadget->registry->fetch('email_format');
        $formatCombo =& Piwi::CreateWidget('Combo', 'email_format');
        $formatCombo->SetID('email_format');
        $formatCombo->AddOption(_t('CONTACT_PROPERTIES_EMAIL_FORMAT_PLAINTEXT'), 'text');
        $formatCombo->AddOption(_t('CONTACT_PROPERTIES_EMAIL_FORMAT_HTML'),      'html');
        $formatCombo->SetDefault($email_format);
        $tpl->SetVariable('lbl_email_format', _t('CONTACT_PROPERTIES_EMAIL_FORMAT'));
        $tpl->SetVariable('email_format', $formatCombo->Get());

        $attachment = $this->gadget->registry->fetch('enable_attachment');
        $combo =& Piwi::CreateWidget('Combo', 'enable_attachment');
        $combo->AddOption(_t('GLOBAL_NO'), 'false');
        $combo->AddOption(_t('GLOBAL_YES'), 'true');
        $combo->SetDefault($attachment);
        $tpl->SetVariable('lbl_enable_attachment', _t('CONTACT_PROPERTIES_ENABLE_ATTACHMENT'));
        $tpl->SetVariable('enable_attachment', $combo->Get());

        // Comments
        $comments = $this->gadget->registry->fetch('comments');
        $editor =& $GLOBALS['app']->LoadEditor('Contact', 'comments', $comments, false);
        $editor->SetId('comments');
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
}