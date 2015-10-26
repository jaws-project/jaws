<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetAdmin
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Actions_Admin_Settings extends Shoutbox_Actions_Admin_Default
{
    /**
     * Displays shoutbox Settings
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Settings()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');

        $tpl->SetVariable('menubar',  $this->MenuBar('Settings'));

        ///Config properties
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Shoutbox'));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $fieldset = new Jaws_Widgets_FieldSet(_t('SHOUTBOX_SETTINGS'));
            $fieldset->SetDirection('vertical');

            //
            $limitcombo =& Piwi::CreateWidget('Combo', 'limit_entries');
            $limitcombo->SetTitle(_t('SHOUTBOX_ENTRY_LIMIT'));
            for ($i = 1; $i <= 20; ++$i) {
                $limitcombo->AddOption($i, $i);
            }
            $limit = $this->gadget->registry->fetch('limit');
            if (Jaws_Error::IsError($limit)) {
                $limit = 10;
            }
            $limitcombo->SetDefault($limit);
            $fieldset->Add($limitcombo);

            // max length
            $max_lencombo =& Piwi::CreateWidget('Combo', 'max_strlen');
            $max_lencombo->SetTitle(_t('SHOUTBOX_ENTRY_MAX_LEN'));
            for ($i = 1; $i <= 10; ++$i) {
                $max_lencombo->AddOption($i*25, $i*25);
            }
            $max_strlen = $this->gadget->registry->fetch('max_strlen');
            if (Jaws_Error::IsError($max_strlen)) {
                $max_strlen = 125;
            }
            $max_lencombo->SetDefault($max_strlen);
            $fieldset->Add($max_lencombo);

            //Anonymous post authority
            $authority =& Piwi::CreateWidget('Combo', 'authority');
            $authority->SetTitle(_t('SHOUTBOX_ANON_POST_AUTHORITY'));
            $authority->AddOption(_t('GLOBAL_DISABLED'), 'false');
            $authority->AddOption(_t('GLOBAL_ENABLED'),  'true');
            $anon_authority = $this->gadget->registry->fetch('anon_post_authority');
            $authority->SetDefault($anon_authority == 'true'? 'true' : 'false');
            $fieldset->Add($authority);

            $form->Add($fieldset);
            $submit =& Piwi::CreateWidget('Button', 'saveproperties', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $submit->AddEvent(ON_CLICK, 'javascript:updateProperties(this.form);');

            $form->Add($submit);
            $tpl->SetVariable('config_form', $form->Get());
        }

        $tpl->ParseBlock('settings');
        return $tpl->Get();

    }
}