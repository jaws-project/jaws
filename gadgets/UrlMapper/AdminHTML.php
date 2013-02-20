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
class UrlMapper_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  Template content
     */
    function Admin()
    {
        return $this->Maps();
    }

    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string   $action_selected   Selected action
     * @return  string   XHTML template content
     */
    function MenuBar($action_selected)
    {
        $actions = array('Admin', 'Aliases', 'ErrorMaps','Properties');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'Admin';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Admin', _t('URLMAPPER_MAPS'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Admin', STOCK_DOCUMENTS);
        $menubar->AddOption('Aliases', _t('URLMAPPER_ALIASES'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Aliases', 'gadgets/UrlMapper/images/aliases.png');
        $menubar->AddOption('ErrorMaps', _t('URLMAPPER_ERRORMAPS'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=ErrorMaps', STOCK_DOCUMENTS);
        $menubar->AddOption('Properties', _t('GLOBAL_PROPERTIES'),
                            BASE_SCRIPT . '?gadget=UrlMapper&amp;action=Properties', STOCK_PREFERENCES);
        $menubar->Activate($action_selected);
        return $menubar->Get();
    }

    /**
     * Prepares the data of maps for a certain gadget and action
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @param   string  $action  Action name
     * @return  array   List of maps
     */
    function GetMaps($gadget, $action)
    {
        $model = $GLOBALS['app']->LoadGadget('UrlMapper', 'AdminModel');
        $maps  = $model->GetActionMaps($gadget, $action);
        if (Jaws_Error::IsError($maps)) {
            return array();
        }

        $newData = array();
        foreach($maps as $map) {
            $mapData = array();
            $mapData['map'] = $map['map'];
            $actions = '';
            if ($this->gadget->GetPermission('EditMaps')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editMap(this, '".$map['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }
            $mapData['actions'] = $actions;
            $newData[] = $mapData;
        }
        return $newData;
    }

    /**
     * Builds maps datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function MapsDatagrid()
    {
        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->setID('maps_datagrid');

        $datagrid->addColumn(Piwi::CreateWidget('Column', _t('URLMAPPER_MAPS'), null, false));
        $colActions = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $colActions->SetStyle('width: 60px; white-space:nowrap;');
        $datagrid->addColumn($colActions);

        $datagrid->SetStyle('margin-top: 0px; width: 100%;');
        return $datagrid->Get();
    }

    /**
     * Builds error maps datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function ErrorMapsDatagrid()
    {
        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->setID('errormaps_datagrid');

        $column1 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_URL'), null, false);
        $column1->SetStyle('width:200px;');
        $datagrid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_CODE'), null, false);
        $column2->SetStyle('width:100px;');
        $datagrid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_NEW_URL'), null, false);
        $column3->SetStyle('width:200px;');
        $datagrid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_NEW_CODE'), null, false);
        $column4->SetStyle('width:100px;');
        $datagrid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('URLMAPPER_ERRORMAPS_HITS'), null, false);
        $column5->SetStyle('width:100px;');
        $datagrid->AddColumn($column5);


        $colActions = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $colActions->SetStyle('width: 60px; white-space:nowrap;');
        $datagrid->addColumn($colActions);

        $datagrid->SetStyle('margin-top: 0px; width: 100%;');
        return $datagrid->Get();
    }

    /**
     * Prepares list of error maps for datagrid
     *
     * @access  public
     * @param   int     $limit
     * @param   int     $offset
     * @return  array   Grid data
     */
    function GetErrorMaps($limit, $offset)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $model = $GLOBALS['app']->LoadGadget('UrlMapper', 'AdminModel');
        $errorMaps = $model->GetErrorMaps($limit, $offset);
        if (Jaws_Error::IsError($errorMaps)) {
            return array();
        }

        $retData = array();
        foreach ($errorMaps as $errorMap) {
            $usrData = array();
            $usrData['url'] = $errorMap['url'];
            $usrData['code'] = $errorMap['code'];
            $usrData['new_url'] = $errorMap['new_url'];
            $usrData['new_code'] = $errorMap['new_code'];
            $usrData['hits'] = $errorMap['hits'];

            $actions = '';
            if ($this->gadget->GetPermission('ManageErrorMaps')) {
                $link =& Piwi::CreateWidget('Link',
                    _t('GLOBAL_EDIT'),
                    "javascript: editErrorMap(this, '".$errorMap['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link',
                    _t('GLOBAL_DELETE'),
                    "javascript: deleteErrorMap(this, '".$errorMap['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }


            $usrData['actions'] = $actions;
            $retData[] = $usrData;
        }

        return $retData;
    }


    /**
     * Builds maps UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Maps()
    {
        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/UrlMapper/templates/');
        $tpl->Load('Maps.html');
        $tpl->SetBlock('UrlMapper');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Admin'));

        //Combo for gadgets
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadgets = $model->GetGadgetsList(null, true, true, null, true);
        $comboGadgets =& Piwi::CreateWidget('Combo', 'gadgets_combo');
        $comboGadgets->SetStyle('width: 200px;');
        foreach($gadgets as $gadget) {
            $comboGadgets->AddOption($gadget['name'], $gadget['realname']);
        }
        $comboGadgets->AddEvent(ON_CHANGE, 'javascript: rebuildActionCombo();');
        $tpl->SetVariable('lbl_gadgets', _t('GLOBAL_GADGETS'));
        $tpl->SetVariable('combo_gadgets', $comboGadgets->Get());

        //Combo for actions
        $comboActions =& Piwi::CreateWidget('Combo', 'actions_combo');
        $comboActions->SetSize(20);
        $comboActions->SetStyle('width: 200px; height: 280px;');
        $comboActions->AddEvent(ON_CHANGE, 'javascript: showActionMaps();');
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_GADGET_ACTIONS'));
        $tpl->SetVariable('combo_actions', $comboActions->Get());

        $tpl->SetVariable('lbl_maps',    _t('URLMAPPER_MAPS'));
        $tpl->SetVariable('datagrid', $this->MapsDatagrid());

        // map order
        $order =& Piwi::CreateWidget('HiddenEntry', 'map_order', '');
        $order->SetID('map_order');
        $tpl->SetVariable('map_order', $order->Get());

        // map route
        $route =& Piwi::CreateWidget('Entry', 'map_route', '');
        $route->SetID('map_route');
        $route->SetStyle('direction: ltr; width: 200px;');
        $route->SetEnabled(false);
        $tpl->SetVariable('lbl_map_route', _t('URLMAPPER_MAPS_ROUTE'));
        $tpl->SetVariable('map_route', $route->Get());

        // map extension
        $ext =& Piwi::CreateWidget('Entry', 'map_ext', '');
        $ext->SetID('map_ext');
        $ext->SetStyle('direction: ltr; width: 200px;');
        $ext->SetEnabled(false);
        $tpl->SetVariable('lbl_map_ext', _t('URLMAPPER_MAPS_EXTENSION'));
        $tpl->SetVariable('map_ext', $ext->Get());

        // custom route entry
        $custom_route =& Piwi::CreateWidget('Entry', 'custom_map_route', '');
        $custom_route->SetID('custom_map_route');
        $custom_route->SetStyle('direction: ltr; width: 200px;');
        $custom_route->SetEnabled(false);
        $tpl->SetVariable('lbl_custom_map_route', _t('URLMAPPER_MAPS_ROUTE'));
        $tpl->SetVariable('custom_map_route', $custom_route->Get());

        // custom map extension
        $custom_ext =& Piwi::CreateWidget('Entry', 'custom_map_ext', '');
        $custom_ext->SetID('custom_map_ext');
        $custom_ext->SetStyle('direction: ltr; width: 200px;');
        $custom_ext->SetEnabled(false);
        $tpl->SetVariable('lbl_custom_map_ext', _t('URLMAPPER_MAPS_EXTENSION'));
        $tpl->SetVariable('custom_map_ext', $custom_ext->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->SetEnabled(false);
        $btnCancel->AddEvent(ON_CLICK, "javascript: enableMapEditingArea(false);");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled(false);
        $btnSave->AddEvent(ON_CLICK, "javascript: saveMap();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('addMap_title',     _t('URLMAPPER_MAPS_ADD_TITLE'));
        $tpl->SetVariable('editMap_title',    _t('URLMAPPER_MAPS_EDIT_TITLE'));
        $tpl->SetVariable('confirmMapDelete', _t('URLMAPPER_MAPS_CONFIRM_DELETE'));

        $tpl->ParseBlock('UrlMapper');
        return $tpl->Get();
    }

    /**
     * Builds aliases UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Aliases()
    {
        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/UrlMapper/templates/');
        $tpl->Load('Aliases.html');
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

        $model = $GLOBALS['app']->LoadGadget('UrlMapper', 'AdminModel');
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

    /**
     * Builds error maps UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ErrorMaps()
    {
        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/UrlMapper/templates/');
        $tpl->Load('ErrorMaps.html');
        $tpl->SetBlock('ErrorMaps');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('ErrorMaps'));

        $tpl->SetVariable('lbl_maps',    _t('URLMAPPER_MAPS'));
        $tpl->SetVariable('datagrid', $this->ErrorMapsDatagrid());

        // url
        $code =& Piwi::CreateWidget('Entry', 'url', '');
        $code->SetID('url');
        $code->SetStyle('direction: ltr; width: 250px;');
        $tpl->SetVariable('lbl_url', _t('URLMAPPER_ERRORMAPS_URL'));
        $tpl->SetVariable('url', $code->Get());

        // Combo for code
        $codeCombo =& Piwi::CreateWidget('Combo', 'code');
        $codeCombo->SetID('code');
        $codeCombo->SetStyle('width: 200px;');
        $codeCombo->AddOption(_t('URLMAPPER_ERRORMAPS_CODE_404'), 404);
        $tpl->SetVariable('lbl_code', _t('URLMAPPER_ERRORMAPS_CODE'));
        $tpl->SetVariable('code', $codeCombo->Get());

        // new url
        $newUrl =& Piwi::CreateWidget('Entry', 'new_url', '');
        $newUrl->SetID('new_url');
        $newUrl->SetStyle('direction: ltr; width: 250px;');
        $tpl->SetVariable('lbl_new_url', _t('URLMAPPER_ERRORMAPS_NEW_URL'));
        $tpl->SetVariable('new_url', $newUrl->Get());

        // Combo for new code
        $codeCombo =& Piwi::CreateWidget('Combo', 'new_code');
        $codeCombo->SetID('new_code');
        $codeCombo->SetStyle('width: 200px;');
        $codeCombo->AddOption(_t('URLMAPPER_ERRORMAPS_CODE_301'), 301);
        $codeCombo->AddOption(_t('URLMAPPER_ERRORMAPS_CODE_302'), 302);
        $codeCombo->AddOption(_t('URLMAPPER_ERRORMAPS_CODE_410'), 410);
        $codeCombo->SetDefault(301);
        $tpl->SetVariable('lbl_new_code', _t('URLMAPPER_ERRORMAPS_NEW_CODE'));
        $tpl->SetVariable('new_code', $codeCombo->Get());


        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->SetID('btn_cancel');
        $btnCancel->SetStyle('visibility: hidden;');
        $btnCancel->AddEvent(ON_CLICK, "javascript: stopErrorMapAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript: saveErrorMap();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('addErrorMap_title',     _t('URLMAPPER_ERRORMAPS_ADD_TITLE'));
        $tpl->SetVariable('editErrorMap_title',    _t('URLMAPPER_ERRORMAPS_EDIT_TITLE'));
        $tpl->SetVariable('confirmErrorMapDelete', _t('URLMAPPER_ERRORMAPS_CONFIRM_DELETE'));
        $tpl->SetVariable('incompleteFieldsMsg',   _t('URLMAPPER_ERRORMAPS_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('ErrorMaps');
        return $tpl->Get();
    }


    /**
     * Builds Properties UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Properties()
    {
        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/UrlMapper/templates/');
        $tpl->Load('Properties.html');
        $tpl->SetBlock('Properties');

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('GLOBAL_PROPERTIES'));
        $fieldset->SetDirection('vertical');

        $useMapCombo =& Piwi::CreateWidget('Combo', 'enabled');
        $useMapCombo->SetTitle(_t('URLMAPPER_SETTINGS_MAP_ENABLED'));
        $useMapCombo->AddOption(_t('GLOBAL_YES'), 'true');
        $useMapCombo->AddOption(_t('GLOBAL_NO'), 'false');
        $useMapCombo->SetDefault($this->gadget->GetRegistry('map_enabled'));

        $precedence =& Piwi::CreateWidget('Combo', 'custom_precedence');
        $precedence->SetTitle(_t('URLMAPPER_CUSTOM_MAP_PRECEDENCE'));
        $precedence->AddOption(_t('GLOBAL_YES'), 'true');
        $precedence->AddOption(_t('GLOBAL_NO'), 'false');
        $precedence->SetDefault($this->gadget->GetRegistry('map_custom_precedence'));

        $useAliasesCombo =& Piwi::CreateWidget('Combo', 'use_aliases');
        $useAliasesCombo->SetTitle(_t('URLMAPPER_SETTINGS_MAP_USE_ALIASES'));
        $useAliasesCombo->AddOption(_t('GLOBAL_YES'), 'true');
        $useAliasesCombo->AddOption(_t('GLOBAL_NO'), 'false');
        $useAliasesCombo->SetDefault($this->gadget->GetRegistry('map_use_aliases'));

        $extension =& Piwi::CreateWidget('Entry', 'extension',
                                         $this->gadget->GetRegistry('map_extensions'));
        $extension->SetTitle(_t('URLMAPPER_SETTINGS_MAP_EXTENSION'));
        $extension->SetStyle('direction: ltr;');

        $fieldset->Add($useMapCombo);
        $fieldset->Add($useAliasesCombo);
        $fieldset->Add($precedence);
        $fieldset->Add($extension);

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: updateProperties(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($fieldset);
        $form->Add($buttonbox);


        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));
        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }
}