<?php
/**
 * Languages Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Languages_Actions_Admin_Languages extends Jaws_Gadget_Action
{
    /**
     * Calls default action(MainMenu)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Languages()
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmSaveData',     $this::t('SAVEDATA'));
        $this->gadget->define('add_language_title',  $this::t('LANGUAGE_ADD'));
        $this->gadget->define('save_language_title', $this::t('LANGUAGE_SAVE'));

        $tpl = $this->gadget->template->loadAdmin('Languages.html');
        $tpl->SetBlock('Languages');
        $tpl->SetVariable('language',   $this::t('LANGUAGE'));
        $tpl->SetVariable('component',  $this::t('COMPONENT'));
        $tpl->SetVariable('settings',   $this::t('SETTINGS'));
        $tpl->SetVariable('from',       $this::t('FROM'));
        $tpl->SetVariable('to',         $this::t('TO'));

        $btnExport =& Piwi::CreateWidget('Button','btn_export',
                                         $this::t('LANGUAGE_EXPORT'), STOCK_DOWN);
        $btnExport->AddEvent(ON_CLICK, 'javascript:export_lang();');
        $tpl->SetVariable('btn_export', $btnExport->Get());

        $tpl->SetBlock('Languages/properties');
        $langId =& Piwi::CreateWidget('Entry', 'lang_code', '');
        $tpl->SetVariable('lang_code', $langId->Get());
        $tpl->SetVariable('lbl_lang_code', $this::t('LANGUAGE_CODE'));

        $langName =& Piwi::CreateWidget('Entry', 'lang_name', '');
        $tpl->SetVariable('lang_name', $langName->Get());
        $tpl->SetVariable('lbl_lang_name', $this::t('LANGUAGE_NAME'));

        if ($this->gadget->GetPermission('ModifyLanguageProperties')) {
            $btnLang =& Piwi::CreateWidget('Button','btn_lang', '', STOCK_SAVE);
            $btnLang->AddEvent(ON_CLICK, 'javascript:save_lang();');
            $tpl->SetVariable('btn_lang', $btnLang->Get());
        }
        $tpl->ParseBlock('Languages/properties');

        // Langs
        $use_data_lang = $this->gadget->registry->fetch('use_data_lang') == 'true';
        $langs = Jaws_Utils::GetLanguagesList($use_data_lang);
        $tpl->SetBlock('Languages/lang');
        $tpl->SetVariable('selected', '');
        $tpl->SetVariable('code', '');
        $tpl->SetVariable('fullname', $this::t('LANGUAGE_NEW'));
        $tpl->ParseBlock('Languages/lang');

        foreach ($langs as $code => $fullname) {
            $tpl->SetBlock('Languages/lang');
            $tpl->SetVariable('selected', $code=='en'? 'selected="selected"': '');
            $tpl->SetVariable('code', $code);
            $tpl->SetVariable('fullname', $fullname);
            $tpl->ParseBlock('Languages/lang');
        }

        // Global, Install, Upgrade
        $model = $this->gadget->model->loadAdmin('Languages');
        $globals = array(
            0 => 'Global',
            4 => 'Install',
            5 => 'Upgrade'
        );
        $tpl->SetBlock('Languages/group');
        $tpl->SetVariable('group', 'Global');
        foreach ($globals as $k => $v) {
            $tpl->SetBlock('Languages/group/item');
            $tpl->SetVariable('key', "$k|$v");
            $tpl->SetVariable('value', $v);
            $tpl->ParseBlock('Languages/group/item');
        }
        $tpl->ParseBlock('Languages/group');
        
        // Gadgets
        $tpl->SetBlock('Languages/group');
        $tpl->SetVariable('group', 'Gadgets');
        $gCompModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $gCompModel->GetGadgetsList();
        ksort($gadgets);
        foreach ($gadgets as $gadget => $gInfo) {
            $tpl->SetBlock('Languages/group/item');
            $tpl->SetVariable('key', "1|$gadget");
            $tpl->SetVariable('value', $gadget);
            $tpl->ParseBlock('Languages/group/item');
        }
        $tpl->ParseBlock('Languages/group');

        // Plugins
        $tpl->SetBlock('Languages/group');
        $tpl->SetVariable('group', 'Plugins');
        $pCompModel = Jaws_Gadget::getInstance('Components')->model->load('Plugins');
        $plugins = $pCompModel->GetPluginsList();
        foreach ($plugins as $plugin => $pInfo) {
            $tpl->SetBlock('Languages/group/item');
            $tpl->SetVariable('key', "2|$plugin");
            $tpl->SetVariable('value', $plugin);
            $tpl->ParseBlock('Languages/group/item');
        }
        $tpl->ParseBlock('Languages/group');

        $tpl->SetBlock('Languages/buttons');
        //checkbox_filter
        $check_filter =& Piwi::CreateWidget('CheckButtons', 'checkbox_filter');
        $check_filter->AddEvent(ON_CLICK, 'javascript:filterTranslated();');
        $check_filter->AddOption($this::t('NOT_SHOW_TRANSLATED'), '', 'checkbox_filter');
        $tpl->SetVariable('checkbox_filter', $check_filter->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel',
                                        Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel_btn->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $cancel_btn->SetStyle('visibility: hidden;');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save',
                                        Jaws::t('SAVE', $this::t('CHANGES')), STOCK_SAVE);
        $save_btn->AddEvent(ON_CLICK, 'javascript:save_lang_data();');
        $save_btn->SetStyle('visibility: hidden;');
        $tpl->SetVariable('save', $save_btn->Get());
        $tpl->ParseBlock('Languages/buttons');

        $tpl->ParseBlock('Languages');
        return $tpl->Get();
    }

    /**
     * Calls default action(MainMenu)
     *
     * @access  public
     * @param   string  $module 
     * @param   string  $type   
     * @param   string  $langTo 
     * @return  string  XHTML template content
     */
    function GetLangDataUI($module, $type, $langTo)
    {
        $tpl = $this->gadget->template->loadAdmin('LangStrings.html');
        $tpl->SetBlock('LangStrings');

        $langFrom = $this->gadget->registry->fetch('base_lang');
        $model = $this->gadget->model->loadAdmin('Languages');
        $data = $model->GetLangData($module, $type, $langTo, $langFrom);
        $color = 'even';
        if (count($data['strings']) > 0) {
            foreach($data['strings'] as $k => $v) {
                $tpl->SetBlock('LangStrings/item');
                $tpl->SetVariable('color', $color);
                $color = ($color=='odd')? 'even' : 'odd';
                if ($v[$langTo] == '') {
                    $tpl->SetVariable('from', '<span style="color: #f00;">' . nl2br($v[$langFrom]) . '</span>');
                } else {
                    $tpl->SetVariable('from', '<span style="color: #000;">' . nl2br($v[$langFrom]) . '</span>');
                }

                $brakeLines = substr_count($v[$langFrom], "\n");
                $rows = floor((strlen($v[$langFrom]) - $brakeLines*2)/42) + $brakeLines;
                if ($brakeLines == 0) {
                    $rows++;
                }

                $tpl->SetVariable('dir', $data['lang_direction']);
                $tpl->SetVariable('row_count', $rows);
                $tpl->SetVariable('height', $rows*18);
                $tpl->SetVariable('field', $k);
                $tpl->SetVariable('to', str_replace(array('"', '\n'), array('&quot;', "\n"), $v[$langTo]));
                $tpl->ParseBlock('LangStrings/item');
            }
        }

        foreach($data['meta'] as $k => $v) {
            $tpl->SetBlock('LangStrings/MetaData');
            $tpl->SetVariable('label', $k);
            $tpl->SetVariable('value', $v);
            $tpl->ParseBlock('LangStrings/MetaData');
        }

        $tpl->ParseBlock('LangStrings');
        return $tpl->Get();
    }
}
