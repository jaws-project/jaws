<?php
/**
 * Weather Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WeatherAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Default action of the gadget
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        if ($this->GetPermission('ManageRegions')) {
            return $this->Regions();
        }

        $this->CheckPermission('UpdateProperties');
        return $this->Properties();
    }

    /**
     * Builds the weather menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('Regions', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Regions';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->GetPermission('ManageRegions')) {
            $menubar->AddOption('Regions', _t('WEATHER_REGIONS'),
                                BASE_SCRIPT . '?gadget=Weather&amp;action=Regions', 'gadgets/Weather/images/regions.png');
        }

        if ($this->GetPermission('UpdateProperties')) {
            $menubar->AddOption('Properties', _t('GLOBAL_PROPERTIES'),
                                BASE_SCRIPT . '?gadget=Weather&amp;action=Properties', 'gadgets/Weather/images/properties.png');
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Displays the management UI for regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Regions()
    {
        $this->CheckPermission('ManageRegions');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Weather/templates/');
        $tpl->Load('AdminWeather.html');
        $tpl->SetBlock('Weather');

        $tpl->SetVariable('menubar', $this->MenuBar('Regions'));
        $tpl->SetVariable('datagrid', $this->RegionsDataGrid());

        $tpl->SetVariable('legend_title', _t('WEATHER_GEOPOSITION'));
        $tpl->SetVariable('map_title', _t('WEATHER_MAP_HINT'));

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $fasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $tpl->SetVariable('lbl_fast_url', _t('WEATHER_FASTURL'));
        $tpl->SetVariable('fast_url', $fasturl->Get());

        $latitude =& Piwi::CreateWidget('Entry', 'latitude', '');
        $latitude->setStyle('direction: ltr;');
        $tpl->SetVariable('lbl_latitude', _t('WEATHER_LATITUDE'));
        $tpl->SetVariable('latitude', $latitude->Get());

        $longitude =& Piwi::CreateWidget('Entry', 'longitude', '');
        $longitude->setStyle('direction: ltr;');
        $tpl->SetVariable('lbl_longitude', _t('WEATHER_LONGITUDE'));
        $tpl->SetVariable('longitude', $longitude->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->AddOption(_t('GLOBAL_NO'), 0);
        $published->setStyle('width:50px');
        $tpl->SetVariable('lbl_published', _t('WEATHER_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'updateRegion();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('confirmDelete', _t('WEATHER_CONFIRM_DELETE'));
        $tpl->SetVariable('incompleteFields', _t('WEATHER_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('default_latitude',
                          $GLOBALS['app']->Registry->Get('/gadgets/Weather/latitude'));
        $tpl->SetVariable('default_longitude',
                          $GLOBALS['app']->Registry->Get('/gadgets/Weather/longitude'));

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
        $model = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $total = $model->TotalOfData('weather');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->SetID('weather_datagrid');
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
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
        $model = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $regions = $model->GetRegions(null, 10, $offset);
        if (Jaws_Error::IsError($regions)) {
            return array();
        }

        $newData = array();
        foreach ($regions as $region) {
            $posData = array();
            $posData['title'] = $region['title'];
            $actions = '';
            if ($this->GetPermission('ManageRegions')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editRegion(this, '".$region['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteRegion(this, '".$region['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $posData['actions'] = $actions;
            $newData[] = $posData;
        }
        return $newData;
    }

    /**
     * Returns google map image
     *
     * @access  public
     * @return  void
     */
    function GetGoogleMapImage()
    {
        $request =& Jaws_Request::getInstance();
        $gMapParams = $request->get(array('latitude', 'longitude', 'zoom', 'size'), 'get');

        $gMapURL = 'http://maps.google.com/maps/api/staticmap?center='.
                   $gMapParams['latitude']. ',' . $gMapParams['longitude'].
                   '&zoom='. $gMapParams['zoom']. '&size='. $gMapParams['size'].
                   '&maptype=roadmap&markers=color:blue|label:x|'.
                   $gMapParams['latitude']. ','. $gMapParams['longitude'].
                   '&sensor=false';

        $options = array();
        $options['timeout'] = (int)$GLOBALS['app']->Registry->Get('/config/connection_timeout');
        if ($GLOBALS['app']->Registry->Get('/network/proxy_enabled') == 'true') {
            if ($GLOBALS['app']->Registry->Get('/network/proxy_auth') == 'true') {
                $options['proxy_user'] = $GLOBALS['app']->Registry->Get('/network/proxy_user');
                $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('/network/proxy_pass');
            }
            $options['proxy_host'] = $GLOBALS['app']->Registry->Get('/network/proxy_host');
            $options['proxy_port'] = $GLOBALS['app']->Registry->Get('/network/proxy_port');
        }

        require_once 'HTTP/Request.php';
        $httpRequest = new HTTP_Request($gMapURL, $options);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
        $resRequest  = $httpRequest->sendRequest();
        if (!PEAR::isError($resRequest) && $httpRequest->getResponseCode() == 200) {
            $data = $httpRequest->getResponseBody();
        } else {
            $data = @file_get_contents($gMapURL);
        }

        header("Content-Type: image/png");
        header("Pragma: public");
        if ($data === false) {
            $data = @file_get_contents('gadgets/Weather/images/gmap.png');
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        } else {
            $expires = 60*60*48;
            header("Cache-Control: max-age=".$expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        }

        echo $data;
    }

    /**
     * Builds Properties section of the gadget 
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Properties()
    {
        $this->CheckPermission('UpdateProperties');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Weather/templates/');
        $tpl->Load('AdminProperties.html');
        $tpl->SetBlock('Properties');

        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $unit =& Piwi::CreateWidget('Combo', 'unit');
        $unit->AddOption(_t('WEATHER_UNIT_METRIC'), 'metric');
        $unit->AddOption(_t('WEATHER_UNIT_IMPERIAL'), 'imperial');
        $unit->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Weather/unit'));
        $tpl->SetVariable('lbl_unit', _t('WEATHER_UNIT'));
        $tpl->SetVariable('unit', $unit->Get());

        $period =& Piwi::CreateWidget('Combo', 'update_period');
        $period->AddOption(_t('GLOBAL_DISABLE'),              0);
        $period->AddOption(_t('GLOBAL_DATE_MINUTES', 30),  1800);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   1),   3600);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   3),  10800);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   6),  21600);
        $period->AddOption(_t('GLOBAL_DATE_HOURS',   8),  28800);
        $period->AddOption(_t('GLOBAL_DATE_DAYS',    1),  86400);
        $period->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Weather/update_period'));
        $tpl->SetVariable('lbl_update_period', _t('WEATHER_UPDATE_PERIOD'));
        $tpl->SetVariable('update_period', $period->Get());

        $now = time();
        $objDate = $GLOBALS['app']->loadDate();
        $dFormat =& Piwi::CreateWidget('Combo', 'date_format');
        $dFormat->setStyle('width:208px;');
        $dFormat->AddOption($objDate->Format($now, 'DN'), 'DN');
        $dFormat->AddOption($objDate->Format($now, 'd MN'), 'd MN');
        $dFormat->AddOption($objDate->Format($now, 'DN d MN'), 'DN d MN');
        $dFormat->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Weather/date_format'));
        $tpl->SetVariable('lbl_date_format', _t('WEATHER_DATE_FORMAT'));
        $tpl->SetVariable('date_format', $dFormat->Get());

        $apikey =& Piwi::CreateWidget('Entry',
                                      'api_key',
                                      $GLOBALS['app']->Registry->Get('/gadgets/Weather/api_key'));
        $apikey->setStyle('width:200px; direction: ltr;');
        $tpl->SetVariable('lbl_api_key', _t('WEATHER_API_KEY'));
        $tpl->SetVariable('lbl_api_key_desc', _t('WEATHER_API_KEY_DESC'));
        $tpl->SetVariable('api_key', $apikey->Get());

        if ($this->GetPermission('UpdateSetting')) {
            $btnupdate =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnupdate->AddEvent(ON_CLICK, 'updateProperties();');
            $tpl->SetVariable('btn_save', $btnupdate->Get());
        }

        $tpl->ParseBlock('Properties');
        return $tpl->Get();
    }

}