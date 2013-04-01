<?php
/**
 * Weather - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Weather
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WeatherURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls   = array();
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Weather', 'AllRegionsWeather'),
                        'title' => _t('WEATHER_NAME'));

        return $urls;
    }

}