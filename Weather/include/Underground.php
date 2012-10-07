<?php
/**
 * Weather_Underground
 *
 * @author       Ali Fazelzadeh <afz@php.net>
 * @author       Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright    2012 Jaws Development Group
 * @license      http://www.gnu.org/copyleft/lesser.html
 */
class Underground_Weather
{
    /*
     *
     */
    var $_metric;

    /*
     *
     */
    var $_cache_dir;
    var $_expire_time;
    var $_params;

    /**
     * Constructor
     *
     */
    function Weather_Underground($metric = true, $cache_dir = '', $expire_time = 3600, $options = array())
    {
        $this->_metric      = $metric;
        $this->_cache_dir   = $cache_dir;
        $this->_expire_time = $expire_time;
        $this->_params      = $options;
    }

    /**
     *
     */
    function saveFile($cache_file, $data)
    {
        if (!Jaws_Utils::file_put_contents($cache_file, serialize($data))) {
            return PEAR::raiseError("Fail to save stream with file_put_contents('$cache_file',...).");
        }

        return true;
    }

    /**
     *
     */
    function loadFile($cache_file)
    {
        if (false === $data = @file_get_contents($cache_file)) {
            return PEAR::raiseError("Fail to open '$cache_file', not found"); 
        }

        return unserialize($data);
    }

    /**
     * exchange Weather Underground icons with native icons
     */
    function getWeatherIcon($gIcon)
    {
        $gIcon = empty($gIcon)? 'unknown' : basename($gIcon, '.gif');
        switch ($gIcon)
        {
            case 'clear':
                $newIcon = 'clear';
                break;

            case 'mostlysunny':
                $newIcon = 'few-clouds';
                break;

            case 'partlycloudy':
                $newIcon = 'few-clouds';
                break;

            case 'mostlycloudy':
                $newIcon = 'more-clouds';
                break;

            case 'rain':
                $newIcon = 'showers-scattered';
                break;

            case 'tstorms':
                $newIcon = 'rain-thunderstorm';
                break;


            case 'chancerain':
                $newIcon = 'sun-clouds-shower';
                break;

            case 'chancesnow':
                $newIcon = 'sun-clouds-sudden-shower';
                break;

            case 'cloudy':
                $newIcon = 'overcast';
                break;

            case 'chancetstorms':
                $newIcon = 'sun-thorm';
                break;

            case 'sleet':
                $newIcon = 'sleet';
                break;

            case 'snow':
                $newIcon = 'snow';
                break;

            case 'fog':
            case 'hazy':
                $newIcon = 'fog-day';
                break;



            case 'nt_clear':
                $newIcon = 'clear-night';
                break;

            case 'nt_mostlysunny':
                $newIcon = 'few-clouds-night';
                break;

            case 'nt_partlycloudy':
                $newIcon = 'few-clouds-night';
                break;

            case 'nt_fog':
            case 'nt_hazy':
                $newIcon = 'fog-night';
                break;

            case 'nt_mostlycloudy':
                $newIcon = 'more-clouds-night';
                break;





            case 'storm':
                $newIcon = 'showers';
                break;


            case 'mist':
                $newIcon = 'showers-scattered';
                break;


//            case 'icy':
//                $newIcon = 'showers-scattered-black-ice';
//                break;


//            case 'flurries':
//                $newIcon = 'hail-scattered';
//                break;

//            case 'chancetstorms':
//                $newIcon = 'sun-clouds-shower';
//                break;

            default:
                $newIcon = $gIcon;
        }

        return $newIcon;
    }

    /**
     *
     */
    function getWeatherTemp($gTemp)
    {
        return $this->_metric? round(($gTemp - 32)*5/9) : $gTemp;
    }

    /**
     *
     */
    function getWeatherInfo($gWeather)
    {
        return array('low'  => $this->getWeatherTemp($gWeather['low']['fahrenheit']),
                     'high' => $this->getWeatherTemp($gWeather['high']['fahrenheit']),
                     'icon' => $this->getWeatherIcon($gWeather['icon']));

    }

    /**
     * Sets the input xml file to be parsed
     *
     * @param    string      Filename (full path)
     * @return   resource    handle of the given file
     * @access   public
     */
    function getWeather($latitude, $longitude)
    {
        $cache_file = $this->_cache_dir . '/weather_' . md5($latitude. '_'. $longitude);
        $timedif = time() - (file_exists($cache_file)? @filemtime($cache_file) : 0);
        if (!empty($this->_cache_dir) && ($timedif < $this->_expire_time)) {
            //cache file is fresh
            $data = $this->loadFile($cache_file);
        } else {
            require_once 'HTTP/Request.php';
            require_once "XML/Unserializer.php";

//            $latitude  = $latitude  * 1000000;
//            $longitude = $longitude * 1000000;
            $apiKey = '0000000000000000';
            $httpRequest = new HTTP_Request("http://api.wunderground.com/api/$apiKey/forecast/conditions/q/{$latitude},{$longitude}.xml",
                                            $this->_params);
            $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
            $resRequest  = $httpRequest->sendRequest();
            if (PEAR::isError($resRequest)) {
                return $resRequest;
            } elseif ($httpRequest->getResponseCode() <> 200) {
                return PEAR::raiseError('HTTP response error', HTTP_REQUEST_ERROR_RESPONSE);
            }

            $data = trim($httpRequest->getResponseBody());
            $unserializer = new XML_Unserializer(array("parseAttributes" => true));
            if (PEAR::isError($unserializer)) {
                return PEAR::raiseError($unserializer->getMessage(), HTTP_REQUEST_ERROR_RESPONSE);
            }

            if (PEAR::isError($unserializer->unserialize($data))) {
                return PEAR::raiseError('Wrong server data', HTTP_REQUEST_ERROR_RESPONSE);
            }

            $data = $unserializer->getUnserializedData();
            if (!array_key_exists('current_observation', $data)) {
                return PEAR::raiseError('Wrong server data', HTTP_REQUEST_ERROR_RESPONSE);
            }

            if (!empty($this->_cache_dir)) {
                $this->saveFile($cache_file, $data);
            }
        }

        $weather = array();
        $weather['temp'] = $this->getWeatherTemp($data['current_observation']['temp_f']);
        $weather['icon'] = $this->getWeatherIcon($data['current_observation']['icon']);
        $weather['forecast'][0] = $this->getWeatherInfo($data['forecast']['simpleforecast']['forecastdays']['forecastday']['0']);
        $weather['forecast'][1] = $this->getWeatherInfo($data['forecast']['simpleforecast']['forecastdays']['forecastday']['1']);
        $weather['forecast'][2] = $this->getWeatherInfo($data['forecast']['simpleforecast']['forecastdays']['forecastday']['2']);
        $weather['forecast'][3] = $this->getWeatherInfo($data['forecast']['simpleforecast']['forecastdays']['forecastday']['3']);

        return $weather;
    }

}