<?php
/**
 * UrlMapper Core Gadget Admin
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Actions_Admin_Aliases extends UrlMapper_Actions_Admin_Default
{
    /**
     * Builds aliases UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Aliases()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadAdminTemplate('Aliases.html');
        $tpl->SetBlock('Aliases');
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'alias_id', '-'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('URLMAPPER_ALIASES_ALIAS'));
        $fieldset->SetDirection('vertical');
        $fieldset->SetID('AliasFieldset');
        $fieldset->SetStyle('width: 300px; min-width: 250px; max-width: 300px;');

        $customUrl =& Piwi::CreateWidget('Entry', 'custom_url');
        $customUrl->SetTitle(_t('URLMAPPER_ALIASES_LINK'));
        $customUrl->SetId('custom_url');
        $customUrl->SetStyle('width: 230px;');
        $fieldset->Add($customUrl);

        $alias =& Piwi::CreateWidget('Entry', 'alias', '', _t('URLMAPPER_ALIASES_ALIAS'));
        $alias->SetId('alias');
        $alias->SetStyle('width: 230px;');
        $fieldset->Add($alias);

        $hbox =& Piwi::CreateWidget('HBox');
        $hbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;'); //hig style
        $delete =& Piwi::CreateWidget('Button', 'delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $delete->AddEvent(ON_CLICK, 'deleteCurrentAlias();');
        $delete->SetId('delete_button');
        $delete->SetStyle('visibility: hidden;');
        $hbox->Add($delete);
        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'stopAction();');
        $cancel->SetId('cancel_button');
        $hbox->Add($cancel);
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->SetId('save_button');
        $save->AddEvent(ON_CLICK, 'javascript: saveAlias();');

        $hbox->Add($save);

        $form->Add($fieldset);
        $form->Add($hbox);

        $tpl->SetVariable('edit_form', $form->Get());
        $tpl->SetVariable('menubar', $this->MenuBar('Aliases'));

        $model = $this->gadget->model->loadAdmin('Aliases');
        $aliases = $model->GetAliases();

        foreach($aliases as $alias) {
            $tpl->SetBlock('Aliases/alias');
            $tpl->SetVariable('id', $alias['id']);
            $tpl->SetVariable('alias_value', $alias['alias_url']);
            $tpl->ParseBlock('Aliases/alias');
        }

        $tpl->ParseBlock('Aliases');
        return $tpl->Get();
    }
}