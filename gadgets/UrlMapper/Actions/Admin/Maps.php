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
class UrlMapper_Actions_Admin_Maps extends UrlMapper_Actions_Admin_Default
{
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
        $model = $this->gadget->model->loadAdmin('Maps');
        $maps  = $model->GetActionMaps($gadget, $action);
        if (Jaws_Error::IsError($maps)) {
            return array();
        }

        $newData = array();
        foreach($maps as $map) {
            $mapData = array();
            $mapData['map'] = $map['map'];
            $actions = '';
            if ($this->gadget->GetPermission('ManageMaps')) {
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
     * Builds maps UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Maps()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Maps.html');
        $tpl->SetBlock('UrlMapper');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('Maps'));

        //Combo for gadgets
        $model = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true, null, true);
        $comboGadgets =& Piwi::CreateWidget('Combo', 'gadgets_combo');
        $comboGadgets->SetStyle('width: 200px;');
        foreach($gadgets as $gadget) {
            $comboGadgets->AddOption($gadget['title'], $gadget['name']);
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
}