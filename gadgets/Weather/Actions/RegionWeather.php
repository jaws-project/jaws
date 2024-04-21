<?php
/**
 * Weather Gadget
 *
 * @category   Gadget
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
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
                'title' => $this::t('ACTIONS_REGIONWEATHER'),
                'value' => $pregions
            );
        }

        return $result;
    }

    /**
     * Get UserRegionWeather action params
     *
     * @access  public
     * @return  array   list of RegionWeather action params
     */
    function UserRegionWeatherLayoutParams()
    {
        $result = array();
        $wModel = $this->gadget->model->load('Regions');
        $user = (int)$this->app->session->user->id;
        $regions = $wModel->GetRegions(true, $user);
        if (!Jaws_Error::isError($regions)) {
            $pregions = array();
            foreach ($regions as $region) {
                $pregions[$region['id']] = $region['title'];
            }

            $result[] = array(
                'title' => $this::t('ACTIONS_REGIONWEATHER'),
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
            if ($region['user'] != $this->app->session->user->id) {
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

        require_once ROOT_JAWS_PATH . 'gadgets/Weather/include/Underground.php';
        $metric = $this->gadget->registry->fetchByUser('unit') == 'metric';
        $wService = new Underground_Weather(
            $this->gadget->registry->fetch('api_key'),
            $metric,
            ROOT_DATA_PATH . 'weather',
            $this->gadget->registry->fetch('update_period'),
            $options);
        $rWeather = $wService->getWeather($region['latitude'], $region['longitude']);
        if (!PEAR::isError($rWeather)) {
            $tpl->SetVariable('title', $this::t('REGION', $region['title']));
            $rid = empty($region['fast_url'])? $region['id'] : $region['fast_url'];
            $url = $this->gadget->urlMap('RegionWeather', array('id' => $rid));
            $tpl->SetVariable('url',  $url);
            $tpl->SetBlock('weather/current');
            if ($forecast) {
                $tpl->SetBlock('weather/current/head');
                $tpl->SetVariable('lbl_current',$this::t('CURRENT'));
                $tpl->ParseBlock('weather/current/head');
            }
            $tpl->SetVariable('url',  $url);
            $tpl->SetVariable('temp', $rWeather['temp']);
            $tpl->SetVariable('unit', $metric? $this::t('UNIT_METRIC_TEMP') :
                $this::t('UNIT_IMPERIAL_TEMP'));
            $tpl->SetVariable('alt',  $rWeather['icon']);
            $tpl->SetVariable('icon', "gadgets/Weather/Resources/images/states/{$rWeather['icon']}.png");
            $tpl->ParseBlock('weather/current');

            if ($forecast) {
                $this->app->layout->SetTitle($this::t('REGION', $region['title']));
                $this->app->layout->AddToMetaKeywords($this->gadget->title. ','. $region['title']);
                $objDate = Jaws_Date::getInstance();
                $tpl->SetBlock('weather/forecast');
                $tpl->SetVariable('lbl_forecast', $this::t('FORECAST'));
                $dFormat = $this->gadget->registry->fetchByUser('date_format');
                foreach ($rWeather['forecast'] as $dayIndex => $fWeather) {
                    $tpl->SetBlock('weather/forecast/item');
                    //86400 = 3600 * 24
                    $tpl->SetVariable('forecast_date',
                        $objDate->Format(time() + $dayIndex * 86400, $dFormat));
                    $tpl->SetVariable('lbl_low',   $this::t('LOW'));
                    $tpl->SetVariable('low_temp',  $fWeather['low']);
                    $tpl->SetVariable('lbl_high',  $this::t('HIGH'));
                    $tpl->SetVariable('high_temp', $fWeather['high']);
                    $tpl->SetVariable('unit', $metric? $this::t('UNIT_METRIC_TEMP') :
                        $this::t('UNIT_IMPERIAL_TEMP'));
                    $tpl->SetVariable('alt',  $fWeather['icon']);
                    $tpl->SetVariable('icon', "gadgets/Weather/Resources/images/states/{$fWeather['icon']}.png");
                    $tpl->ParseBlock('weather/forecast/item');
                }
                $tpl->ParseBlock('weather/forecast');
            }
        } else {
            $GLOBALS['log']->Log(JAWS_ERROR, $rWeather->getMessage());
        }

        $tpl->ParseBlock('weather');
        return $tpl->Get();
    }

    /**
     * Displays the weather of a specific region for user
     *
     * @access  public
     * @param   int      $region     Region ID
     * @param   bool     $forecast   Whether displays forecast or not
     * @return  string   XHTML content
     */
    function UserRegionWeather($region = null, $forecast = false)
    {
        $this->RegionWeather($region, $forecast);
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
            'title' => $this::t('REGIONS'),
            'value' => array(
                0 => $this::t('GLOBAL_REGIONS'),
                1 => $this::t('USER_REGIONS'),
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
        $tpl->SetVariable('title', $this::t('ALL_REGIONS'));

        $model = $this->gadget->model->load('Regions');

        $user = empty($user)? 0 : (int)$this->app->session->user->id;
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

            require_once ROOT_JAWS_PATH . 'gadgets/Weather/include/Underground.php';
            $metric = $this->gadget->registry->fetch('unit') == 'metric';
            $wService = new Underground_Weather(
                $this->gadget->registry->fetch('api_key'),
                $metric,
                ROOT_DATA_PATH . 'weather',
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
                    $tpl->SetVariable('unit', $metric? $this::t('UNIT_METRIC_TEMP') :
                        $this::t('UNIT_IMPERIAL_TEMP'));
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_title', Jaws::t('TITLE'));
        $this->gadget->define('lbl_published', $this::t('PUBLISHED'));
        $this->gadget->define('lbl_edit', Jaws::t('EDIT'));
        $this->gadget->define('lbl_delete', Jaws::t('DELETE'));
        $this->gadget->define('lbl_geo_position', $this::t('GEOPOSITION'));
        $this->gadget->define('lbl_search', Jaws::t('SEARCH'));
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('base_script', BASE_SCRIPT);

        $tpl = $this->gadget->template->load('UserRegions.html');
        $tpl->SetBlock('UserRegions');
        $tpl->SetVariable('title', $this::t('ALL_REGIONS'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_add', Jaws::t('ADD'));
        $tpl->SetVariable('lbl_fast_url', $this::t('FASTURL'));
        $tpl->SetVariable('lbl_latitude', $this::t('LATITUDE'));
        $tpl->SetVariable('lbl_longitude', $this::t('LONGITUDE'));
        $tpl->SetVariable('lbl_published', $this::t('PUBLISHED'));
        $tpl->SetVariable('lbl_yes', Jaws::t('YESS'));
        $tpl->SetVariable('lbl_no', Jaws::t('NOO'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(
            array('limit', 'offset', 'searchBy'),
            'post'
        );

        $model = $this->gadget->model->load('Regions');
        $user = (int)$this->app->session->user->id;
        $filters = array();
        if (!empty($post['searchBy'])) {
            $filters = array('term' => $post['searchBy']);
        }
        $regions = $model->GetRegions($filters, $user, $post['limit'], $post['offset']);
        $total = $model->GetRegionsCount($filters, $user);

        foreach ($regions as $key => $region) {
            $region['published'] = ($region['published']) ? Jaws::t('YESS') : Jaws::t('NOO');
            $regions[$key] = $region;
        }
        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $total,
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
     * Insert user's region
     *
     * @access  public
     * @return  string  XHTML content
     */
    function InsertRegion()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $data = $this->gadget->request->fetch('data:array', 'post');
        $model = $this->gadget->model->load('Regions');
        $data['user'] = (int)$this->app->session->user->id;
        $res = $model->InsertUserRegion($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_REGION_NOT_ADDED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('REGION_ADDED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Update user's region
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UpdateRegion()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $model = $this->gadget->model->load('Regions');
        $user = (int)$this->app->session->user->id;
        $res = $model->UpdateUserRegion($post['id'], $post['data'], $user);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_REGION_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('REGION_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete user's regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DeleteUserRegion()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int)$this->gadget->request->fetch('id', 'post');
        $user = (int)$this->app->session->user->id;
        $res = $this->gadget->model->load('Regions')->DeleteUserRegion($user, $id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_REGION_NOT_DELETED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('REGION_DELETED'), RESPONSE_NOTICE);
        }
    }
}