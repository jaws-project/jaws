<?php
/**
 * Weather Gadget
 *
 * @category   Gadget
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default Action
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        return $this->AllRegionsWeather();
    }

    /**
     * Displays the weather for all regions
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AllRegionsWeather()
    {
        $wLayout = $GLOBALS['app']->LoadGadget('Weather', 'LayoutHTML');
        return $wLayout->AllRegionsWeather();
    }

    /**
     * Displays the weather of a region
     *
     * @access  public
     * @return  string  XHTML content
     */
    function RegionWeather()
    {
        $wLayout = $GLOBALS['app']->LoadGadget('Weather', 'LayoutHTML');
        $request =& Jaws_Request::getInstance();
        $region = $request->get('id', 'get');
        $region = Jaws_XSS::defilter($region, true);

        return $wLayout->RegionWeather($region, true);
    }

}