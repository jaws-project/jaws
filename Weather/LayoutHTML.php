<?php
/**
 * Weather Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WeatherLayoutHTML
{
    /**
     * Loads layout actions
     *
     * @access  private
     * @return  array   List of actions
     */
    function LoadLayoutActions()
    {
        $actions = array();
        $model = $GLOBALS['app']->LoadGadget('Weather', 'Model');
        $regions = $model->GetRegions();
        if (!Jaws_Error::isError($regions)) {
            foreach ($regions as $region) {
                $actions['RegionWeather(' . $region['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $region['title'],
                    'desc' => ''
                );
            }
        }

        return $actions;
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
        $options['timeout'] = (int)$GLOBALS['app']->Registry->Get('/config/connection_timeout');
        if ($GLOBALS['app']->Registry->Get('/network/proxy_enabled') == 'true') {
            if ($GLOBALS['app']->Registry->Get('/network/proxy_auth') == 'true') {
                $options['proxy_user'] = $GLOBALS['app']->Registry->Get('/network/proxy_user');
                $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('/network/proxy_pass');
            }
            $options['proxy_host'] = $GLOBALS['app']->Registry->Get('/network/proxy_host');
            $options['proxy_port'] = $GLOBALS['app']->Registry->Get('/network/proxy_port');
        }

        require_once JAWS_PATH . 'gadgets/Weather/include/Underground.php';
        $metric = $GLOBALS['app']->Registry->Get('/gadgets/Weather/unit') == 'metric';
        $wService = new Underground_Weather($metric,
                                       JAWS_DATA . 'weather',
                                       $GLOBALS['app']->Registry->Get('/gadgets/Weather/update_period'),
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
                $dFormat = $GLOBALS['app']->Registry->Get('/gadgets/Weather/date_format');
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
            $options['timeout'] = (int)$GLOBALS['app']->Registry->Get('/config/connection_timeout');
            if ($GLOBALS['app']->Registry->Get('/network/proxy_enabled') == 'true') {
                if ($GLOBALS['app']->Registry->Get('/network/proxy_auth') == 'true') {
                    $options['proxy_user'] = $GLOBALS['app']->Registry->Get('/network/proxy_user');
                    $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('/network/proxy_pass');
                }
                $options['proxy_host'] = $GLOBALS['app']->Registry->Get('/network/proxy_host');
                $options['proxy_port'] = $GLOBALS['app']->Registry->Get('/network/proxy_port');
            }

            require_once JAWS_PATH . 'gadgets/Weather/include/Google.php';
            $metric = $GLOBALS['app']->Registry->Get('/gadgets/Weather/unit') == 'metric';
            $wService = new Google_Weather($metric,
                                           JAWS_DATA . 'weather',
                                           $GLOBALS['app']->Registry->Get('/gadgets/Weather/update_period'),
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