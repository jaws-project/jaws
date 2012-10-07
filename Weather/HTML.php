<?php
/**
 * Weather Gadget
 *
 * @category   Gadget
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WeatherHTML extends Jaws_GadgetHTML
{
    /**
     * Default Action
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
     */
    function DefaultAction()
    {
        return $this->AllRegionsWeather();
    }

    /**
     * Displays the weather of all regions
     *
     * @access  public
     * @return  string   HTML content
     */
    function AllRegionsWeather()
    {
        $wLayout = $GLOBALS['app']->LoadGadget('Weather', 'LayoutHTML');
        return $wLayout->AllRegionsWeather();
    }

    /**
     * Displays the weather of a region
     *
     * @access public
     * @return string   HTML content
     */
    function RegionWeather()
    {
        $wLayout = $GLOBALS['app']->LoadGadget('Weather', 'LayoutHTML');
        $request =& Jaws_Request::getInstance();
        $region = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $region = $xss->defilter($region, true);

        return $wLayout->RegionWeather($region, true);
    }

}