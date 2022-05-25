<?php
/**
 * UrlMapper Core Gadget Admin
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Actions_Admin_Properties extends UrlMapper_Actions_Admin_Default
{
    /**
     * Builds Properties UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Properties()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Properties.html');
        $tpl->SetBlock('Properties');

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');

        include_once ROOT_JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(Jaws::t('PROPERTIES'));
        $fieldset->SetDirection('vertical');

        $useMapCombo =& Piwi::CreateWidget('Combo', 'enabled');
        $useMapCombo->SetTitle(_t('URLMAPPER_SETTINGS_MAP_ENABLED'));
        $useMapCombo->AddOption(Jaws::t('YESS'), 'true');
        $useMapCombo->AddOption(Jaws::t('NOO'), 'false');
        $useMapCombo->SetDefault($this->gadget->registry->fetch('map_enabled'));

        $precedence =& Piwi::CreateWidget('Combo', 'custom_precedence');
        $precedence->SetTitle(_t('URLMAPPER_CUSTOM_MAP_PRECEDENCE'));
        $precedence->AddOption(Jaws::t('YESS'), 'true');
        $precedence->AddOption(Jaws::t('NOO'), 'false');
        $precedence->SetDefault($this->gadget->registry->fetch('map_custom_precedence'));

        $useAliasesCombo =& Piwi::CreateWidget('Combo', 'use_aliases');
        $useAliasesCombo->SetTitle(_t('URLMAPPER_SETTINGS_MAP_USE_ALIASES'));
        $useAliasesCombo->AddOption(Jaws::t('YESS'), 'true');
        $useAliasesCombo->AddOption(Jaws::t('NOO'), 'false');
        $useAliasesCombo->SetDefault($this->gadget->registry->fetch('map_use_aliases'));

        $extension =& Piwi::CreateWidget('Entry', 'extension',
            $this->gadget->registry->fetch('map_extensions'));
        $extension->SetTitle(_t('URLMAPPER_SETTINGS_MAP_EXTENSION'));
        $extension->SetStyle('direction: ltr;');

        $fieldset->Add($useMapCombo);
        $fieldset->Add($useAliasesCombo);
        $fieldset->Add($precedence);
        $fieldset->Add($extension);

        $save =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:updateProperties(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(Jaws::t('LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));
        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }
}