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
class Contact_Actions_Admin_Properties extends Contact_Actions_Admin_Default
{
    /**
     * Builds Properties UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Properties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Properties.html');
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