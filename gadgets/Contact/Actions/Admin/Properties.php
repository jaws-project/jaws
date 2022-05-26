<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2021 Jaws Development Group
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
        $antispamCombo->AddOption(Jaws::t('NOO'),  'false');
        $antispamCombo->AddOption(Jaws::t('YESS'), 'true');
        $antispamCombo->SetDefault($use_antispam);
        $tpl->SetVariable('lbl_use_antispam', $this::t('PROPERTIES_USE_ANTISPAM'));
        $tpl->SetVariable('use_antispam', $antispamCombo->Get());

        $email_format = $this->gadget->registry->fetch('email_format');
        $formatCombo =& Piwi::CreateWidget('Combo', 'email_format');
        $formatCombo->SetID('email_format');
        $formatCombo->AddOption($this::t('PROPERTIES_EMAIL_FORMAT_PLAINTEXT'), 'text');
        $formatCombo->AddOption($this::t('PROPERTIES_EMAIL_FORMAT_HTML'),      'html');
        $formatCombo->SetDefault($email_format);
        $tpl->SetVariable('lbl_email_format', $this::t('PROPERTIES_EMAIL_FORMAT'));
        $tpl->SetVariable('email_format', $formatCombo->Get());

        $attachment = $this->gadget->registry->fetch('enable_attachment');
        $combo =& Piwi::CreateWidget('Combo', 'enable_attachment');
        $combo->AddOption(Jaws::t('NOO'), 'false');
        $combo->AddOption(Jaws::t('YESS'), 'true');
        $combo->SetDefault($attachment);
        $tpl->SetVariable('lbl_enable_attachment', $this::t('PROPERTIES_ENABLE_ATTACHMENT'));
        $tpl->SetVariable('enable_attachment', $combo->Get());

        // Comments
        $comments = $this->gadget->registry->fetch('comments');
        $editor =& $this->app->loadEditor('Contact', 'comments', $comments, false);
        $editor->SetId('comments');
        $tpl->SetVariable('lbl_comments', $this::t('PROPERTIES_COMMENTS'));
        $tpl->SetVariable('comments', $editor->Get());

        if ($this->gadget->GetPermission('UpdateSetting')) {
            $btnupdate =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
            $btnupdate->AddEvent(ON_CLICK, 'updateProperties();');
            $tpl->SetVariable('btn_save', $btnupdate->Get());
        }

        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }
}