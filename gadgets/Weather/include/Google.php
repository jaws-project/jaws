<?php
/**
 * Google_Weather
 *
 * @author       Ali Fazelzadeh <afz@php.net>
 * @copyright    2011-2013 Jaws Development Group
 * @license      http://www.gnu.org/copyleft/lesser.html
 */
class Google_Weather
{
    /**
     * Whether use metric or not
     *
     * @var     bool
     * @access  private
     */
    var $_metric;

    /**
     * The path of the cache directory
     *
     * @var     string
     * @access  private
     */
    var $_cache_dir;

    /**
     * Time interval to update the cache
     *
     * @var     int
     * @access  private
     */
    var $_expire_time;

    /**
     * Optional parameters
     *
     * @var     array
     * @access  private
     */
    var $_params;

    /**
     * Constructor
     *
     * @access  public
     * @param   bool    $metric
     * @param   string  $cache_dir
     * @param   int     $expire_time
     * @param   array   $options
     * @return  void
     */
    function Google_Weather($metric = true, $cache_dir = '', $expire_time = 3600, $options = array())
    {
        $this->_metric      = $metric;
        $this->_cache_dir   = $cache_dir;
        $this->_expire_time = $expire_time;
        $this->_params      = $options;
    }

    /**
     * Saves data into the file
     *
     * @access  public
     * @param   string  $cache_file
     * @param   string  $data
     * @return  mixed   True on success and PEAR error on failure
     */
    function saveFile($cache_file, $data)
    {
        if (!Jaws_Utils::file_put_contents($cache_file, serialize($data))) {
            return PEAR::raiseError("Fail to save stream with file_put_contents('$cache_file',...).");
        }

        return true;
    }

    /**
     * Loads data from file
     *
     * @access  public
     * @param   string  $cache_file
     * @return  mixed   True on success and PEAR error on failure
     */
    function loadFile($cache_file)
    {
        if (false === $data = @file_get_contents($cache_file)) {
            return PEAR::raiseError("Fail to open '$cache_file', not found"); 
        }

        return unserialize($data);
    }

    /**
     * Exchange Google icons with native icons
     *
     * @access  public
     * @param   string  $gIcon
     * @return  string  $newIcon
     */
    function getWeatherIcon($gIcon)
    {
        $gIcon = empty($gIcon)? 'unknown' : basename($gIcon, '.gif');
        switch ($gIcon)
        {
            case 'sunny':
                $newIcon = 'clear';
                break;

            case 'mostly_sunny':
                $newIcon = 'few-clouds';
                break;

            case 'partly_cloudy':
                $newIcon = 'few-clouds';
                break;

            case 'mostly_cloudy':
                $newIcon = 'more-clouds';
                break;

            case 'chance_of_storm':
                $newIcon = 'sun-clouds-shower';
                break;

            case 'rain':
                $newIcon = 'showers-scattered';
                break;

            case 'chance_of_rain':
                $newIcon = 'sun-clouds-shower';
                break;

            case 'chance_of_snow':
                $newIcon = 'sun-clouds-sudden-shower';
                break;

            case 'cloudy':
                $newIcon = 'overcast';
                break;

            case 'mist':
                $newIcon = 'showers-scattered';
                break;

            case 'storm':
                $newIcon = 'showers';
                break;

            case 'thunderstorm':
                $newIcon = 'rain-thunderstorm';
                break;

            case 'chance_of_tstorm':
                $newIcon = 'sun-thorm';
                break;

            case 'sleet':
                $newIcon = 'sleet';
                break;

            case 'snow':
                $newIcon = 'snow';
                break;

            case 'icy':
                $newIcon = 'showers-scattered-black-ice';
                break;

            case 'dust':
            case 'fog':
            case 'smoke':
            case 'haze':
                $newIcon = 'day-more-fog';
                break;

            case 'flurries':
                $newIcon = 'hail-scattered';
                break;

            default:
                $newIcon = $gIcon;
        }

        return $newIcon;
    }

    /**
     * Gets temperature of the weather
     *
     * @access  public
     * @param   int $gTemp
     * @return  int Temperature
     */
    function getWeatherTemp($gTemp)
    {
        return $this->_metric? round(($gTemp - 32)*5/9) : $gTemp;
    }

    /**
     * Gets weather information
     *
     * @access  public
     * @param   array   $gWeather
     * @return  array   Weather information
     */
    function getWeatherInfo($gWeather)
    {
        return array('low'  => $this->getWeatherTemp($gWeather['low']['data']),
                     'high' => $this->getWeatherTemp($gWeather['high']['data']),
                     'icon' => $this->getWeatherIcon($gWeather['icon']['data']));

    }

    /**
     * Sets the input xml file to be parsed
     *
     * @param    float      $latitude   The GEO position latitude
     * @param    float      $longitude  The GEO position longitude
     * @return   mixed      Array of weather data or PEAR error
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
            require_once PEAR_PATH. 'HTTP/Request.php';
            require_once PEAR_PATH. 'XML/Unserializer.php';

            $latitude  = $latitude  * 1000000;
            $longitude = $longitude * 1000000;
            $httpRequest = new HTTP_Request("http://www.google.com/ig/api?weather=,,,{$latitude},{$longitude}&hl=en",
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
            if (!array_key_exists('current_conditions', $data['weather'])) {
                return PEAR::raiseError('Wrong server data', HTTP_REQUEST_ERROR_RESPONSE);
            }

            if (!empty($this->_cache_dir)) {
                $this->saveFile($cache_file, $data);
            }
        }

        $weather = array();
        $weather['temp'] = $this->getWeatherTemp($data['weather']['current_conditions']['temp_f']['data']);
        $weather['icon'] = $this->getWeatherIcon($data['weather']['current_conditions']['icon']['data']);
        $weather['forecast'][0] = $this->getWeatherInfo($data['weather']['forecast_conditions']['0']);
        $weather['forecast'][1] = $this->getWeatherInfo($data['weather']['forecast_conditions']['1']);
        $weather['forecast'][2] = $this->getWeatherInfo($data['weather']['forecast_conditions']['2']);
        $weather['forecast'][3] = $this->getWeatherInfo($data['weather']['forecast_conditions']['3']);

        return $weather;
    }

}