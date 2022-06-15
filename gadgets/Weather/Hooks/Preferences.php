<?php
/**
 * Weather - Preferences hook
 *
 * @category    GadgetHook
 * @package     Weather
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2022 Jaws Development Group
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
            'type' => 'select',
            'title' => $this::t('UNIT'),
            'values' => array(
                'metric'   => $this::t('UNIT_METRIC'),
                'imperial' => $this::t('UNIT_IMPERIAL')
            ),
        );

        $now = time();
        $objDate = Jaws_Date::getInstance();
        $result['date_format'] = array(
            'type' => 'select',
            'title' => $this::t('DATE_FORMAT'),
            'values' => array(
                'DN'      => $objDate->Format($now, 'DN'),
                'd MN'    => $objDate->Format($now, 'd MN'),
                'DN d MN' => $objDate->Format($now, 'DN d MN'),
            ),
        );

        return $result;
    }

}