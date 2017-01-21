<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * PEAR::Services_Weather_Weatherdotcom
 *
 * PHP versions 4 and 5
 *
 * <LICENSE>
 * Copyright (c) 2005-2011, Alexander Wirtz
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * o Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * o Neither the name of the software nor the names of its contributors
 *   may be used to endorse or promote products derived from this software
 *   without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * </LICENSE>
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2011 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     CVS: $Id: Weatherdotcom.php 313789 2011-07-27 16:25:15Z eru $
 * @link        http://pear.php.net/package/Services_Weather
 * @link        http://www.weather.com/services/xmloap.html
 * @example     examples/weather.com-basic.php      weather.com-basic.php
 * @example     examples/weather.com-extensive.php  weather.com-extensive.php
 * @filesource
 */

require_once "Services/Weather/Common.php";

// {{{ class Services_Weather_Weatherdotcom
/**
 * This class acts as an interface to the xml service of weather.com. It
 * searches for given locations and retrieves current weather data as well
 * as forecast for up to 5 days.
 *
 * For using the weather.com xml-service please visit
 *     http://www.weather.com/services/xmloap.html
 * and follow the link to sign up, it's free! You will receive an email
 * where to download the SDK with the needed images and guidelines how to
 * publish live data from weather.com. Unfortunately the guidelines are a
 * bit harsh, that's why there's no actual data-representation in this
 * class, just the raw data. Also weather.com demands active caching, so I'd
 * strongly recommend enabling the caching implemented in this class. It
 * obeys to the times as written down in the guidelines.
 *
 * For working examples, please take a look at
 *     docs/Services_Weather/examples/weather.com-basic.php
 *     docs/Services_Weather/examples/weather.com-extensive.php
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2011 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     Release: 1.4.6
 * @link        http://pear.php.net/package/Services_Weather
 * @link        http://www.weather.com/services/xmloap.html
 * @example     examples/weather.com-basic.php      weather.com-basic.php
 * @example     examples/weather.com-extensive.php  weather.com-extensive.php
 */
class Services_Weather_Weatherdotcom extends Services_Weather_Common {

    // {{{ properties
    /**
     * Partner-ID at weather.com
     *
     * @var     string                      $_partnerID
     * @access  private
     */
    var $_partnerID = "";

    /**
     * License key at weather.com
     *
     * @var     string                      $_licenseKey
     * @access  private
     */
    var $_licenseKey = "";

    /**
     * Switch to toggle pre-fetching of data in one single request
     *
     * @var     bool                        $_preFetch
     * @access  private
     */
     var $_preFetch = false;

    /**
     * Object containing the promotional links-data
     *
     * @var     object stdClass             $_links
     * @access  private
     */
    var $_links;

    /**
     * Object containing the location
     *
     * @var     object stdClass             $_location
     * @access  private
     */
    var $_location;

    /**
     * Object containing the weather
     *
     * @var     object stdClass             $_weather
     * @access  private
     */
    var $_weather;

    /**
     * Object containing the forecast
     *
     * @var     object stdClass             $_forecast
     * @access  private
     */
    var $_forecast;

    /**
     * XML_Unserializer, used for processing the xml
     *
     * @var     object XML_Unserializer     $_unserializer
     * @access  private
     */
    var $_unserializer;
    // }}}

    // {{{ constructor
    /**
     * Constructor
     *
     * Requires XML_Serializer to be installed
     *
     * @param   array                       $options
     * @param   mixed                       $error
     * @throws  PEAR_Error
     * @access  private
     */
    function __construct($options, &$error)
    {
        $perror = null;
        $this->Services_Weather_Common($options, $perror);
        if (Services_Weather::isError($perror)) {
            $error = $perror;
            return;
        }

        // Set options accordingly
        if (isset($options["partnerID"])) {
            $this->setAccountData($options["partnerID"]);
        }
        if (isset($options["licenseKey"])) {
            $this->setAccountData("", $options["licenseKey"]);
        }
        if (isset($options["preFetch"])) {
            $this->enablePreFetch($options["preFetch"]);
        }

        include_once "XML/Unserializer.php";
        $unserializer = new XML_Unserializer(array("tagAsClass" => false,
                                                    "complexType" => "object",
                                                    "keyAttribute" => "type"));
        if (Services_Weather::isError($unserializer)) {
            $error = $unserializer;
            return;
        } else {
            $this->_unserializer = $unserializer;
        }

        // Initialize the properties containing the data from the server
        $this->_links    = null;
        $this->_location = null;
        $this->_weather  = null;
        $this->_forecast = null;

        // Can't acquire an object here, has to be clean on every request
        include_once "HTTP/Request.php";
    }
    // }}}

    // {{{ setAccountData()
    /**
     * Sets the neccessary account-information for weather.com, you'll
     * receive them after registering for the XML-stream
     *
     * @param   string                      $partnerID
     * @param   string                      $licenseKey
     * @access  public
     */
    function setAccountData($partnerID, $licenseKey)
    {
        if (strlen($partnerID) && ctype_digit($partnerID)) {
            $this->_partnerID  = $partnerID;
        }
        if (strlen($licenseKey) && ctype_alnum($licenseKey)) {
            $this->_licenseKey = $licenseKey;
        }
    }
    // }}}

    // {{{ enablePreFetch()
    /**
     * Enables pre-fetching of data in one single request
     *
     * @param   bool                        $preFetch
     * @access  public
     */
    function enablePreFetch($preFetch)
    {
        if ($preFetch == true) {
            $this->_preFetch = true;
        }
    }
    // }}}

    // {{{ _checkLocationID()
    /**
     * Checks the id for valid values and thus prevents silly requests to
     * weather.com server
     *
     * @param   string                      $id
     * @return  PEAR_Error|bool
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_NO_LOCATION
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_INVALID_LOCATION
     * @access  private
     */
    function _checkLocationID($id)
    {
        if (is_array($id) || is_object($id) || !strlen($id)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_NO_LOCATION, __FILE__, __LINE__);
        } elseif (!ctype_alnum($id) || (strlen($id) > 8)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_INVALID_LOCATION, __FILE__, __LINE__);
        }

        return true;
    }
    // }}}

    // {{{ _parseWeatherData()
    /**
     * Fetches the data based on the requested type and caches it
     *
     * @param   string                      $id
     * @param   string                      $reqType
     * @return  PEAR_Error|bool
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @throws  PEAR_Error
     * @access  private
     */
    function _parseWeatherData($id, $reqType)
    {
        if ($this->_preFetch) {
            $reqType = "all";
        }

        $url = "http://xoap.weather.com/weather/local/".$id."?link=xoap&prod=xoap&par=".$this->_partnerID."&key=".$this->_licenseKey;

        switch ($reqType) {
            case "links":
                $url .= "";
                break;
            case "weather":
                $url .= "&cc=*&unit=s";
                break;
            case "forecast":
                $url .= "&dayf=5&unit=s";
                break;
            case "all":
                $url .= "&cc=*&dayf=5&unit=s";
                break;
        }

        // Get data from URL...
        $request = new HTTP_Request($url, $this->_httpOptions);
        $status = $request->sendRequest();
        if (Services_Weather::isError($status) || (int) $request->getResponseCode() <> 200) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
        }
        $data = $request->getResponseBody();

        // ...and unserialize
        $status = $this->_unserializer->unserialize($data);

        if (Services_Weather::isError($status)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
        } else {
            $root = $this->_unserializer->getRootName();
            $data = $this->_unserializer->getUnserializedData();

            if (Services_Weather::isError($root) || $root == "HTML") {
                // Something wrong here, maybe not XML retrieved...
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            } elseif ($root == "error") {
                // We got an error back from weather.com
                $errno  = key(get_object_vars($data));
                return Services_Weather::raiseError($errno, __FILE__, __LINE__);
            } else {
                // Valid data, lets get started
                // Loop through the different sub-parts of the data for processing
                foreach (get_object_vars($data) as $key => $val) {
                    switch ($key) {
                        case "head":
                            continue 2;
                        case "prmo":
                            $varname  = "links";
                            break;
                        case "loc":
                            $varname  = "location";
                            break;
                        case "cc":
                            $varname  = "weather";
                            break;
                        case "dayf":
                            $varname  = "forecast";
                            break;
                    }
                    // Save data in object
                    $this->{"_".$varname} = $val;
                    if ($this->_cacheEnabled) {
                        // ...and cache if possible
                        $this->_saveCache($id, $val, "", $varname);
                    }
                }
            }
        }

        return true;
    }
    // }}}

    // {{{ searchLocation()
    /**
     * Searches IDs for given location, returns array of possible locations
     * or single ID
     *
     * @param   string                      $location
     * @param   bool                        $useFirst       If set, first ID of result-array is returned
     * @return  PEAR_Error|array|string
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION
     * @access  public
     */
    function searchLocation($location, $useFirst = false)
    {
        $location = trim($location);
        $locLow   = strtolower($location);
        
        // Check on cached data: MD5-hash of location has to be correct and the userdata has to be the same as the given location 
        if ($this->_cacheEnabled && $locLow == $this->_getUserCache(md5($locLow), "search")) {
            $search = $this->_getCache(md5($locLow), "search");
        } else {
            // Get search data from server and unserialize
            $request = new HTTP_Request("http://xoap.weather.com/search/search?where=".urlencode($location), $this->_httpOptions);
            $status = $request->sendRequest();
            if (Services_Weather::isError($status) || (int) $request->getResponseCode() <> 200) {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            }
            $data = $request->getResponseBody();
    
            // ...and unserialize
            $status = $this->_unserializer->unserialize($data, false, array("overrideOptions" => true, "complexType" => "array", "keyAttribute" => "id"));
    
            if (Services_Weather::isError($status)) {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            }
    
            $root = $this->_unserializer->getRootName();
            $search = $this->_unserializer->getUnserializedData();
    
            if (Services_Weather::isError($search) || $root == "HTML") {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            } elseif (!is_array($search) || !sizeof($search)) {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION, __FILE__, __LINE__);
            }

            if ($this->_cacheEnabled) {
                // ...and cache if possible
                $this->_saveCache(md5($locLow), $search, $locLow, "search");
            }
        }

        if (!$useFirst && (sizeof($search) > 1)) {
            $searchReturn = $search;
        } elseif ($useFirst || (sizeof($search) == 1)) {
            $searchReturn = key($search);
        } else {
            $searchReturn = array();
        }

        return $searchReturn;
    }
    // }}}

    // {{{ searchLocationByCountry()
    /**
     * Returns only false, as weather.com offers no country listing via
     * its XML services
     *
     * @param   string                      $country
     * @return  bool
     * @access  public
     * @deprecated
     */
    function searchLocationByCountry($country = "")
    {
        return false;
    }
    // }}}

    // {{{ getLinks()
    /**
     * Returns the data for the promotional links belonging to the ID
     *
     * @param   string                      $id
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getLinks($id = "")
    {
        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }

        $linksReturn = array();

        if (is_object($this->_links)) {
            $linksReturn["cache"] = "MEM";
        } elseif ($this->_cacheEnabled && ($links = $this->_getCache($id, "links"))) {
            // Get data from cache
            $this->_links = $links;
            $linksReturn["cache"] = "HIT";
        } else {
            // Same as in the function above...
            $status = $this->_parseWeatherData($id, "links");

            if (Services_Weather::isError($status)) {
                return $status;
            }
            $linksReturn["cache"] = "MISS";
        }

        $linksReturn["promo"] = array();
        for ($i = 0; $i < sizeof($this->_links->link); $i++) {
            $linksReturn["promo"][$i] = array();
            $linksReturn["promo"][$i]["title"] = $this->_links->link[$i]->t;
            // B0rked response (returned is &par=xoap, should be &prod=xoap), fix it
            $linksReturn["promo"][$i]["link"]  = str_replace("par=", "prod=", $this->_links->link[$i]->l);
            $linksReturn["promo"][$i]["link"] .= "&par=".$this->_partnerID;
        }

        return $linksReturn;
    }
    // }}}

    // {{{ getLocation()
    /**
     * Returns the data for the location belonging to the ID
     *
     * @param   string                      $id
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getLocation($id = "")
    {
        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }

        $locationReturn = array();

        if (is_object($this->_location)) {
            $locationReturn["cache"] = "MEM";
        } elseif ($this->_cacheEnabled && ($location = $this->_getCache($id, "location"))) {
            // Get data from cache
            $this->_location = $location;
            $locationReturn["cache"] = "HIT";
        } else {
            // Same as in the function above...
            $status = $this->_parseWeatherData($id, "location");

            if (Services_Weather::isError($status)) {
                return $status;
            }
            $locationReturn["cache"] = "MISS";
        }

        $locationReturn["name"]      = $this->_location->dnam;
        $locationReturn["time"]      = date($this->_timeFormat, strtotime($this->_location->tm));
        $locationReturn["latitude"]  = $this->_location->lat;
        $locationReturn["longitude"] = $this->_location->lon;
        $locationReturn["sunrise"]   = date($this->_timeFormat, strtotime($this->_location->sunr));
        $locationReturn["sunset"]    = date($this->_timeFormat, strtotime($this->_location->suns));
        $locationReturn["timezone"]  = $this->_location->zone;

        return $locationReturn;
    }
    // }}}

    // {{{ getWeather()
    /**
     * Returns the weather-data for the supplied location
     *
     * @param   string                      $id
     * @param   string                      $unitsFormat
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getWeather($id = "", $unitsFormat = "")
    {
        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }

        // Get other data
        $units    = $this->getUnitsFormat($unitsFormat);

        $weatherReturn = array();

        if (is_object($this->_weather)) {
            $weatherReturn["cache"] = "MEM";
        } elseif ($this->_cacheEnabled && ($weather = $this->_getCache($id, "weather"))) {
            // Same procedure...
            $this->_weather = $weather;
            $weatherReturn["cache"] = "HIT";
        } else {
            // ...as last function
            $status = $this->_parseWeatherData($id, "weather");

            if (Services_Weather::isError($status)) {
                return $status;
            }
            $weatherReturn["cache"] = "MISS";
        }

        // Make sure the location object has been loaded
        if (!is_object($this->_location)) {
            $this->getLocation($id);
        }

        // Some explanation for the next two lines:
        // weather.com isn't always supplying the timezone in the update string, but
        // uses "Local Time" as reference, which is imho utterly stupid, because it's
        // inconsistent. Well, what I do here is check for this string and if I can
        // find it, I calculate the difference between the timezone at the location
        // and this computers timezone. This amount of seconds is then subtracted from
        // the time the update-string has delivered.
        $update   = str_replace("Local Time", "", $this->_weather->lsup);
        $adjustTZ = ($update == $this->_weather->lsup) ? 0 : $this->_location->zone * 3600 - date("Z");
        $weatherReturn["update"]            = gmdate(trim($this->_dateFormat." ".$this->_timeFormat), strtotime($update) - $adjustTZ);
        $weatherReturn["updateRaw"]         = $this->_weather->lsup;
        $weatherReturn["station"]           = $this->_weather->obst;
        $weatherReturn["temperature"]       = round($this->convertTemperature($this->_weather->tmp, "f", $units["temp"]), 2);
        $weatherReturn["feltTemperature"]   = round($this->convertTemperature($this->_weather->flik, "f", $units["temp"], 2));
        $weatherReturn["condition"]         = $this->_weather->t;
        $weatherReturn["conditionIcon"]     = $this->_weather->icon;
        $weatherReturn["pressure"]          = round($this->convertPressure($this->_weather->bar->r, "in", $units["pres"]), 2);
        $weatherReturn["pressureTrend"]     = $this->_weather->bar->d;
        $weatherReturn["wind"]              = round($this->convertSpeed($this->_weather->wind->s, "mph", $units["wind"]), 2);
        $weatherReturn["windGust"]          = round($this->convertSpeed($this->_weather->wind->gust, "mph", $units["wind"]), 2);
        $weatherReturn["windDegrees"]       = $this->_weather->wind->d;
        $weatherReturn["windDirection"]     = $this->_weather->wind->t;
        $weatherReturn["humidity"]          = round($this->_weather->hmid, 1);
        if (is_numeric($this->_weather->vis)) {
            $weatherReturn["visibility"]    = round($this->convertDistance($this->_weather->vis, "sm", $units["vis"]), 2);
        } else {
            $weatherReturn["visibility"]    = $this->_weather->vis;
        }
        $weatherReturn["uvIndex"]           = $this->_weather->uv->i;
        $weatherReturn["uvText"]            = $this->_weather->uv->t;
        $weatherReturn["dewPoint"]          = round($this->convertTemperature($this->_weather->dewp, "f", $units["temp"]), 2);
        $weatherReturn["moon"]              = $this->_weather->moon->t;
        $weatherReturn["moonIcon"]          = $this->_weather->moon->icon;

        return $weatherReturn;
    }
    // }}}

    // {{{ getForecast()
    /**
     * Get the forecast for the next days
     *
     * @param   string                      $id
     * @param   int                         $days           Values between 1 and 5
     * @param   string                      $unitsFormat
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getForecast($id = "", $days = 5, $unitsFormat = "")
    {
        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }
        if (!is_int($days) || ($days < 1) || ($days > 5)) {
            $days = 5;
        }

        // Get other data
        $units    = $this->getUnitsFormat($unitsFormat);

        $forecastReturn = array();

        if (is_object($this->_forecast)) {
            $forecastReturn["cache"] = "MEM";
        } elseif ($this->_cacheEnabled && ($forecast = $this->_getCache($id, "forecast"))) {
            // Encore...
            $this->_forecast = $forecast;
            $forecastReturn["cache"] = "HIT";
        } else {
            // ...
            $status = $this->_parseWeatherData($id, "forecast");

            if (Services_Weather::isError($status)) {
                return $status;
            }
            $forecastReturn["cache"] = "MISS";
        }

        // Make sure the location object has been loaded
        if (!is_object($this->_location)) {
            $this->getLocation($id);
        }

        // Some explanation for the next two lines: (same as above)
        // weather.com isn't always supplying the timezone in the update string, but
        // uses "Local Time" as reference, which is imho utterly stupid, because it's
        // inconsistent. Well, what I do here is check for this string and if I can
        // find it, I calculate the difference between the timezone at the location
        // and this computers timezone. This amount of seconds is then subtracted from
        // the time the update-string has delivered.
        $update   = str_replace("Local Time", "", $this->_forecast->lsup);
        $adjustTZ = ($update == $this->_forecast->lsup) ? 0 : $this->_location->zone * 3600 - date("Z");
        $forecastReturn["update"]    = gmdate($this->_dateFormat." ".$this->_timeFormat, strtotime($update) - $adjustTZ);
        $forecastReturn["updateRaw"] = $this->_forecast->lsup;
        $forecastReturn["days"]      = array();

        for ($i = 0; $i < $days; $i++) {
            $day = array(
                "temperatureHigh" => round($this->convertTemperature($this->_forecast->day[$i]->hi, "f", $units["temp"]), 2),
                "temperatureLow"  => round($this->convertTemperature($this->_forecast->day[$i]->low, "f", $units["temp"]), 2),
                "sunrise"         => date($this->_timeFormat, strtotime($this->_forecast->day[$i]->sunr)),
                "sunset"          => date($this->_timeFormat, strtotime($this->_forecast->day[$i]->suns)),
                "day" => array(
                    "condition"     => $this->_forecast->day[$i]->part[0]->t,
                    "conditionIcon" => $this->_forecast->day[$i]->part[0]->icon,
                    "wind"          => round($this->convertSpeed($this->_forecast->day[$i]->part[0]->wind->s, "mph", $units["wind"]), 2),
                    "windGust"      => round($this->convertSpeed($this->_forecast->day[$i]->part[0]->wind->gust, "mph", $units["wind"]), 2),
                    "windDegrees"   => $this->_forecast->day[$i]->part[0]->wind->d,
                    "windDirection" => $this->_forecast->day[$i]->part[0]->wind->t,
                    "precipitation" => $this->_forecast->day[$i]->part[0]->ppcp,
                    "humidity"      => round($this->_forecast->day[$i]->part[0]->hmid, 1)
                ),
                "night" => array (
                    "condition"     => $this->_forecast->day[$i]->part[1]->t,
                    "conditionIcon" => $this->_forecast->day[$i]->part[1]->icon,
                    "wind"          => round($this->convertSpeed($this->_forecast->day[$i]->part[1]->wind->s, "mph", $units["wind"]), 2),
                    "windGust"      => round($this->convertSpeed($this->_forecast->day[$i]->part[1]->wind->gust, "mph", $units["wind"]), 2),
                    "windDegrees"   => $this->_forecast->day[$i]->part[1]->wind->d,
                    "windDirection" => $this->_forecast->day[$i]->part[1]->wind->t,
                    "precipitation" => $this->_forecast->day[$i]->part[1]->ppcp,
                    "humidity"      => round($this->_forecast->day[$i]->part[1]->hmid, 1)
                )
            );

            $forecastReturn["days"][] = $day;
        }

        return $forecastReturn;
    }
    // }}}
}
// }}}
?>
