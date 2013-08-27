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
        $HTML = $GLOBALS['app']->LoadGadget('Weather', 'HTML', 'RegionWeather');
        return $HTML->AllRegionsWeather();
    }
}