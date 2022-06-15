<?php
/**
 * Weather Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Actions_Admin_Regions extends Weather_Actions_Admin_Default
{
    /**
     * Displays the management UI for regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Regions()
    {
        $this->gadget->CheckPermission('ManageRegions');
        $this->AjaxMe('script.js');
        $this->gadget->define('base_script', BASE_SCRIPT);
        $this->gadget->define('confirmDelete', $this::t('CONFIRM_DELETE'));
        $this->gadget->define('incompleteFields', $this::t('INCOMPLETE_FIELDS'));

        $tpl = $this->gadget->template->loadAdmin('Weather.html');
        $tpl->SetBlock('Weather');

        $tpl->SetVariable('menubar', $this->MenuBar('Regions'));
        $tpl->SetVariable('datagrid', $this->RegionsDataGrid());

        $tpl->SetVariable('legend_title', $this::t('GEOPOSITION'));
        $tpl->SetVariable('map_title', $this::t('MAP_HINT'));

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $fasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $tpl->SetVariable('lbl_fast_url', $this::t('FASTURL'));
        $tpl->SetVariable('fast_url', $fasturl->Get());

        $latitude =& Piwi::CreateWidget('Entry', 'latitude', '');
        $latitude->setStyle('direction: ltr;');
        $tpl->SetVariable('lbl_latitude', $this::t('LATITUDE'));
        $tpl->SetVariable('latitude', $latitude->Get());

        $longitude =& Piwi::CreateWidget('Entry', 'longitude', '');
        $longitude->setStyle('direction: ltr;');
        $tpl->SetVariable('lbl_longitude', $this::t('LONGITUDE'));
        $tpl->SetVariable('longitude', $longitude->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->AddOption(Jaws::t('YESS'), 1);
        $published->AddOption(Jaws::t('NOO'), 0);
        $published->setStyle('width:50px');
        $tpl->SetVariable('lbl_published', $this::t('PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'updateRegion();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('default_latitude',
            $this->gadget->registry->fetch('latitude'));
        $tpl->SetVariable('default_longitude',
            $this->gadget->registry->fetch('longitude'));

        $tpl->ParseBlock('Weather');
        return $tpl->Get();
    }

    /**
     * Builds datagrid structure
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function RegionsDataGrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('weather');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->SetID('weather_datagrid');
        $column1 = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column4 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column4->SetStyle('width:40px; white-space:nowrap;');
        $datagrid->AddColumn($column4);

        return $datagrid->Get();
    }

    /**
     * Prepares data for regions datagrid
     *
     * @access  public
     * @param   int     $offset  Data offset
     * @return  array   Grid data
     */
    function GetRegions($offset = null)
    {
        $model = $this->gadget->model->load('Regions');
        $regions = $model->GetRegions(null, 0, 10, $offset);
        if (Jaws_Error::IsError($regions)) {
            return array();
        }

        $newData = array();
        foreach ($regions as $region) {
            $posData = array();
            $posData['title'] = $region['title'];
            $actions = '';
            if ($this->gadget->GetPermission('ManageRegions')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                    "javascript:editRegion(this, '".$region['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                    "javascript:deleteRegion(this, '".$region['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $posData['actions'] = $actions;
            $newData[] = $posData;
        }
        return $newData;
    }
}