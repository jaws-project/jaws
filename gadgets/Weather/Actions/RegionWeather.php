<?php
/**
 * Weather Gadget
 *
 * @category   Gadget
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Actions_RegionWeather extends Jaws_Gadget_Action
{

    /**
     * Get RegionWeather action params
     *
     * @access  public
     * @return  array   list of RegionWeather action params
     */
    function RegionWeatherLayoutParams()
    {
        $result = array();
        $wModel = $this->gadget->model->load('Regions');
        $regions = $wModel->GetRegions(true, 0);
        if (!Jaws_Error::isError($regions)) {
            $pregions = array();
            foreach ($regions as $region) {
                $pregions[$region['id']] = $region['title'];
            }

            $result[] = array(
                'title' => _t('WEATHER_ACTIONS_REGIONWEATHER'),
                'value' => $pregions
            );
        }

        return $result;
    }

    /**
     * Displays the weather of a specific region
     *
     * @access  public
     * @param   int      $region     Region ID
     * @param   bool     $forecast   Whether displays forecast or not
     * @return  string   XHTML content
     */
    function RegionWeather($region = null, $forecast = false)
    {
        $region_get = $this->gadget->request->fetch('id', 'get');
        $region_get = Jaws_XSS::defilter($region_get);
        if(!empty($region_get)) {
            $region = $region_get;
            $forecast = true;
        }

        $model = $this->gadget->model->load('Regions');
        $region = $model->GetRegion($region);
        if (Jaws_Error::IsError($region) || empty($region)) {
            return false;
        }

        // check user permissions
        if (!empty($region['user'])) {
            if ($region['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $tpl = $this->gadget->template->load('Weather.html');
        $tpl->SetBlock('weather');

        $options = array();
        $options['timeout'] = (int)$this->gadget->registry->fetch('connection_timeout', 'Settings');
        if ($this->gadget->registry->fetch('proxy_enabled', 'Settings') == 'true') {
            if ($this->gadget->registry->fetch('proxy_auth', 'Settings') == 'true') {
                $options['proxy_user'] = $this->gadget->registry->fetch('proxy_user', 'Settings');
                $options['proxy_pass'] = $this->gadget->registry->fetch('proxy_pass', 'Settings');
            }
            $options['proxy_host'] = $this->gadget->registry->fetch('proxy_host', 'Settings');
            $options['proxy_port'] = $this->gadget->registry->fetch('proxy_port', 'Settings');
        }

        require_once JAWS_PATH . 'gadgets/Weather/include/Underground.php';
        $metric = $this->gadget->registry->fetchByUser('unit') == 'metric';
        $wService = new Underground_Weather(
            $this->gadget->registry->fetch('api_key'),
            $metric,
            JAWS_DATA . 'weather',
            $this->gadget->registry->fetch('update_period'),
            $options);
        $rWeather = $wService->getWeather($region['latitude'], $region['longitude']);
        if (!PEAR::isError($rWeather)) {
            $tpl->SetVariable('title', _t('WEATHER_REGION', $region['title']));
            $rid = empty($region['fast_url'])? $region['id'] : $region['fast_url'];
            $url = $this->gadget->urlMap('RegionWeather', array('id' => $rid));
            $tpl->SetVariable('url',  $url);
            $tpl->SetBlock('weather/current');
            if ($forecast) {
                $tpl->SetBlock('weather/current/head');
                $tpl->SetVariable('lbl_current',_t('WEATHER_CURRENT'));
                $tpl->ParseBlock('weather/current/head');
            }
            $tpl->SetVariable('url',  $url);
            $tpl->SetVariable('temp', $rWeather['temp']);
            $tpl->SetVariable('unit', $metric? _t('WEATHER_UNIT_METRIC_TEMP') :
                _t('WEATHER_UNIT_IMPERIAL_TEMP'));
            $tpl->SetVariable('alt',  $rWeather['icon']);
            $tpl->SetVariable('icon', "gadgets/Weather/Resources/images/states/{$rWeather['icon']}.png");
            $tpl->ParseBlock('weather/current');

            if ($forecast) {
                $GLOBALS['app']->Layout->SetTitle(_t('WEATHER_REGION', $region['title']));
                $GLOBALS['app']->Layout->AddToMetaKeywords($this->gadget->title. ','. $region['title']);
                $objDate = Jaws_Date::getInstance();
                $tpl->SetBlock('weather/forecast');
                $tpl->SetVariable('lbl_forecast', _t('WEATHER_FORECAST'));
                $dFormat = $this->gadget->registry->fetchByUser('date_format');
                foreach ($rWeather['forecast'] as $dayIndex => $fWeather) {
                    $tpl->SetBlock('weather/forecast/item');
                    //86400 = 3600 * 24
                    $tpl->SetVariable('forecast_date',
                        $objDate->Format(time() + $dayIndex * 86400, $dFormat));
                    $tpl->SetVariable('lbl_low',   _t('WEATHER_LOW'));
                    $tpl->SetVariable('low_temp',  $fWeather['low']);
                    $tpl->SetVariable('lbl_high',  _t('WEATHER_HIGH'));
                    $tpl->SetVariable('high_temp', $fWeather['high']);
                    $tpl->SetVariable('unit', $metric? _t('WEATHER_UNIT_METRIC_TEMP') :
                        _t('WEATHER_UNIT_IMPERIAL_TEMP'));
                    $tpl->SetVariable('alt',  $fWeather['icon']);
                    $tpl->SetVariable('icon', "gadgets/Weather/Resources/images/states/{$fWeather['icon']}.png");
                    $tpl->ParseBlock('weather/forecast/item');
                }
                $tpl->ParseBlock('weather/forecast');
            }
        } else {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, $rWeather->getMessage());
        }

        $tpl->ParseBlock('weather');
        return $tpl->Get();
    }

    /**
     * Get AllRegionsWeather action params
     *
     * @access  public
     * @return  array   list of AllRegionsWeather action params
     */
    function AllRegionsWeatherLayoutParams()
    {
        $result = array();
        $result[] = array(
            'title' => _t('WEATHER_REGIONS'),
            'value' => array(
                0 => _t('WEATHER_GLOBAL_REGIONS'),
                1 => _t('WEATHER_USER_REGIONS'),
            )
        );

        return $result;
    }

    /**
     * Displays the weather for all regions
     *
     * @access  public
     * @param   int     $user  $user = 0 => global regions, otherwise display current user regions
     * @return  string  XHTML content
     */
    function AllRegionsWeather($user = 0)
    {
        $tpl = $this->gadget->template->load('AllWeather.html');
        $tpl->SetBlock('weather');
        $tpl->SetVariable('title', _t('WEATHER_ALL_REGIONS'));

        $model = $this->gadget->model->load('Regions');

        $user = empty($user)? 0 : (int)$GLOBALS['app']->Session->GetAttribute('user');
        $regions = $model->GetRegions(true, $user);
        if (!Jaws_Error::isError($regions)) {
            $options = array();
            $options['timeout'] = (int)$this->gadget->registry->fetch('connection_timeout', 'Settings');
            if ($this->gadget->registry->fetch('proxy_enabled', 'Settings') == 'true') {
                if ($this->gadget->registry->fetch('proxy_auth', 'Settings') == 'true') {
                    $options['proxy_user'] = $this->gadget->registry->fetch('proxy_user', 'Settings');
                    $options['proxy_pass'] = $this->gadget->registry->fetch('proxy_pass', 'Settings');
                }
                $options['proxy_host'] = $this->gadget->registry->fetch('proxy_host', 'Settings');
                $options['proxy_port'] = $this->gadget->registry->fetch('proxy_port', 'Settings');
            }

            require_once JAWS_PATH . 'gadgets/Weather/include/Underground.php';
            $metric = $this->gadget->registry->fetch('unit') == 'metric';
            $wService = new Underground_Weather(
                $this->gadget->registry->fetch('api_key'),
                $metric,
                JAWS_DATA . 'weather',
                $this->gadget->registry->fetch('update_period'),
                $options);

            foreach ($regions as $region) {
                $rWeather = $wService->getWeather($region['latitude'],
                    $region['longitude']);
                if (!PEAR::isError($rWeather)) {
                    $tpl->SetBlock('weather/region');
                    $tpl->SetVariable('region', $region['title']);
                    $rid = empty($region['fast_url'])? $region['id'] : $region['fast_url'];
                    $tpl->SetVariable(
                        'url',
                        $this->gadget->urlMap('RegionWeather', array('id' => $rid))
                    );
                    $tpl->SetVariable('temp', $rWeather['temp']);
                    $tpl->SetVariable('unit', $metric? _t('WEATHER_UNIT_METRIC_TEMP') :
                        _t('WEATHER_UNIT_IMPERIAL_TEMP'));
                    $tpl->SetVariable('alt',  $rWeather['icon']);
                    $tpl->SetVariable('icon', "gadgets/Weather/Resources/images/states/{$rWeather['icon']}.png");
                    $tpl->ParseBlock('weather/region');
                }
            }
        }

        $tpl->ParseBlock('weather');
        return $tpl->Get();
    }

    /**
     * Displays the UI for managing user's regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserRegionsList()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->AddScriptLink('libraries/w2ui/w2ui.js');
//        $GLOBALS['app']->Layout->AddHeadLink('libraries/w2ui/w2ui.rtl.css');
        $GLOBALS['app']->Layout->AddHeadLink('libraries/w2ui/w2ui.css');
        $this->AjaxMe('index.js');

        $tpl = $this->gadget->template->load('UserRegions.html');
        $tpl->SetBlock('UserRegions');
        $tpl->SetVariable('title', _t('WEATHER_ALL_REGIONS'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_geo_position', _t('WEATHER_GEOPOSITION'));
        $tpl->SetVariable('lbl_fast_url', _t('WEATHER_FASTURL'));
        $tpl->SetVariable('lbl_latitude', _t('WEATHER_LATITUDE'));
        $tpl->SetVariable('lbl_longitude', _t('WEATHER_LONGITUDE'));
        $tpl->SetVariable('lbl_published', _t('WEATHER_PUBLISHED'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_no', _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->ParseBlock('UserRegions');
        return $tpl->Get();
    }

    /**
     * Return user's regions list
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetUserRegions()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->model->load('Regions');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $regions = $model->GetRegions(true, $user);
        $total = $model->GetRegionsCount(true, $user);

        return $GLOBALS['app']->Session->GetResponse(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $regions
            )
        );
    }

    /**
     * Return user's region info
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetRegion()
    {
        $id = $this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->load('Regions');
        return $model->GetRegion($id);
    }

    /**
     * Delete user's regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DeleteUserRegions()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $ids = $this->gadget->request->fetch('ids:array', 'post');

        $model = $this->gadget->model->load('Regions');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->DeleteUserRegions($user, $ids);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('WEATHER_ERROR_REGION_NOT_DELETED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('WEATHER_REGION_DELETED'), RESPONSE_NOTICE);
        }


//        return $GLOBALS['app']->Session->GetResponse(
//            '',
//            RESPONSE_NOTICE,
//            array(
//                'total'   => $total,
//                'records' => $regions
//            )
//        );
    }
}