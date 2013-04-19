<?php
/**
 * Weather Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_LayoutHTML extends Jaws_Gadget_HTML
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
        $wModel = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $regions = $wModel->GetRegions();
        if (!Jaws_Error::isError($regions)) {
            $pregions = array();
            foreach ($regions as $region) {
                $pregions[$region['id']] = $region['title'];
            }

            $result[] = array(
                'title' => _t('WEATHER_LAYOUT_REGION'),
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
    function RegionWeather($region, $forecast = false)
    {
        $model = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $region = $model->GetRegion($region);
        if (Jaws_Error::IsError($region) || empty($region)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Weather/templates/');
        $tpl->Load('Weather.html');
        $tpl->SetBlock('weather');

        $options = array();
        $options['timeout'] = (int)$this->gadget->registry->get('connection_timeout', 'Settings');
        if ($this->gadget->registry->get('proxy_enabled', 'Settings') == 'true') {
            if ($this->gadget->registry->get('proxy_auth', 'Settings') == 'true') {
                $options['proxy_user'] = $this->gadget->registry->get('proxy_user', 'Settings');
                $options['proxy_pass'] = $this->gadget->registry->get('proxy_pass', 'Settings');
            }
            $options['proxy_host'] = $this->gadget->registry->get('proxy_host', 'Settings');
            $options['proxy_port'] = $this->gadget->registry->get('proxy_port', 'Settings');
        }

        require_once JAWS_PATH . 'gadgets/Weather/include/Underground.php';
        $metric = $this->gadget->registry->get('unit') == 'metric';
        $wService = new Underground_Weather(
                                $this->gadget->registry->get('api_key'),
                                $metric,
                                JAWS_DATA . 'weather',
                                $this->gadget->registry->get('update_period'),
                                $options);
        $rWeather = $wService->getWeather($region['latitude'], $region['longitude']);
        if (!PEAR::isError($rWeather)) {
            $tpl->SetVariable('title', _t('WEATHER_TITLE', $region['title']));
            $rid = empty($region['fast_url'])? $region['id'] : $region['fast_url'];
            $url = $GLOBALS['app']->Map->GetURLFor('Weather', 'RegionWeather', array('id' => $rid));
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
            $tpl->SetVariable('icon', "gadgets/Weather/images/states/{$rWeather['icon']}.png");
            $tpl->ParseBlock('weather/current');

            if ($forecast) {
                $GLOBALS['app']->Layout->SetTitle(_t('WEATHER_TITLE', $region['title']));
                $GLOBALS['app']->Layout->AddToMetaKeywords(_t('WEATHER_NAME'). ','. $region['title']);
                $objDate = $GLOBALS['app']->loadDate();
                $tpl->SetBlock('weather/forecast');
                $tpl->SetVariable('lbl_forecast', _t('WEATHER_FORECAST'));
                $dFormat = $this->gadget->registry->get('date_format');
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
                    $tpl->SetVariable('icon', "gadgets/Weather/images/states/{$fWeather['icon']}.png");
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
     * Displays the weather for all regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AllRegionsWeather()
    {
        $tpl = new Jaws_Template('gadgets/Weather/templates/');
        $tpl->Load('AllWeather.html');
        $tpl->SetBlock('weather');
        $tpl->SetVariable('title', _t('WEATHER_ALL_REGIONS'));

        $model = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $regions = $model->GetRegions();
        if (!Jaws_Error::isError($regions)) {
            $options = array();
            $options['timeout'] = (int)$this->gadget->registry->get('connection_timeout', 'Settings');
            if ($this->gadget->registry->get('proxy_enabled', 'Settings') == 'true') {
                if ($this->gadget->registry->get('proxy_auth', 'Settings') == 'true') {
                    $options['proxy_user'] = $this->gadget->registry->get('proxy_user', 'Settings');
                    $options['proxy_pass'] = $this->gadget->registry->get('proxy_pass', 'Settings');
                }
                $options['proxy_host'] = $this->gadget->registry->get('proxy_host', 'Settings');
                $options['proxy_port'] = $this->gadget->registry->get('proxy_port', 'Settings');
            }

            require_once JAWS_PATH . 'gadgets/Weather/include/Underground.php';
            $metric = $this->gadget->registry->get('unit') == 'metric';
            $wService = new Underground_Weather(
                                            $this->gadget->registry->get('api_key'),
                                            $metric,
                                            JAWS_DATA . 'weather',
                                            $this->gadget->registry->get('update_period'),
                                            $options);

            foreach ($regions as $region) {
                $rWeather = $wService->getWeather($region['latitude'],
                                                  $region['longitude']);
                if (!PEAR::isError($rWeather)) {
                    $tpl->SetBlock('weather/region');
                    $tpl->SetVariable('region', $region['title']);
                    $rid = empty($region['fast_url'])? $region['id'] : $region['fast_url'];
                    $tpl->SetVariable('url',  $GLOBALS['app']->Map->GetURLFor('Weather',
                                                                              'RegionWeather',
                                                                              array('id' => $rid)));
                    $tpl->SetVariable('temp', $rWeather['temp']);
                    $tpl->SetVariable('unit', $metric? _t('WEATHER_UNIT_METRIC_TEMP') :
                                                       _t('WEATHER_UNIT_IMPERIAL_TEMP'));
                    $tpl->SetVariable('alt',  $rWeather['icon']);
                    $tpl->SetVariable('icon', "gadgets/Weather/images/states/{$rWeather['icon']}.png");
                    $tpl->ParseBlock('weather/region');
                }
            }
        }

        $tpl->ParseBlock('weather');
        return $tpl->Get();
    }

}