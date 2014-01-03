<?php
/**
 * Weather_Underground
 *
 * @author       Ali Fazelzadeh <afz@php.net>
 * @author       Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright    2012-2014 Jaws Development Group
 * @license      http://www.gnu.org/copyleft/lesser.html
 */
class Underground_Weather
{
    /**
     * API key for using Underground weather service
     *
     * @var     bool
     * @access  private
     */
    var $_apikey;

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
     * @param   string  $apikey         Underground weather service api key
     * @param   bool    $metric
     * @param   string  $cache_dir
     * @param   int     $expire_time
     * @param   array   $options
     * @return  void
     */
    function Underground_Weather($apikey, $metric = true, $cache_dir = '', $expire_time = 3600, $options = array())
    {
        $this->_apikey      = $apikey;
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
     * Exchange Underground icons with native icons
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
     * Gets weather temperature details
     *
     * @access  private
     * @param   array   $gWeather
     * @return  array   Weather information
     */
    function getWeatherInfo($gWeather)
    {
        return array('low'  => $this->getWeatherTemp($gWeather['low']['fahrenheit']),
                     'high' => $this->getWeatherTemp($gWeather['high']['fahrenheit']),
                     'icon' => $this->getWeatherIcon($gWeather['icon']));

    }

    /**
     * Gets weather data for specific latitude/longitude
     *
     * @access   public
     * @param    float  $latitude   The GEO position latitude
     * @param    float  $longitude  The GEO position longitude
     * @return   mixed  Array of weather data or PEAR error
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

            $req_url = "http://api.wunderground.com/api/{$this->_apikey}/forecast/conditions/q/";
            $req_url.= "{$latitude},{$longitude}.xml";
            $httpRequest = new HTTP_Request($req_url, $this->_params);

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