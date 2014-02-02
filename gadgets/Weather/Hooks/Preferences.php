<?php
/**
 * Weather - Preferences hook
 *
 * @category    GadgetHook
 * @package     Weather
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Hooks_Preferences extends Jaws_Gadget_Hook
{
    /**
     * Get user's preferences of this gadget
     *
     * @access  public
     * @return  array   Formatted array for using in Users Preferences action
     */
    function Execute()
    {
        $result = array();

        $result['unit'] = array(
            'title' => _t('WEATHER_UNIT'),
            'values' => array(
                'metric'   => _t('WEATHER_UNIT_METRIC'),
                'imperial' => _t('WEATHER_UNIT_IMPERIAL')
            ),
        );

        $now = time();
        $objDate = Jaws_Date::getInstance();
        $result['date_format'] = array(
            'title' => _t('WEATHER_DATE_FORMAT'),
            'values' => array(
                'DN'      => $objDate->Format($now, 'DN'),
                'd MN'    => $objDate->Format($now, 'd MN'),
                'DN d MN' => $objDate->Format($now, 'DN d MN'),
            ),
        );

        return $result;
    }

}