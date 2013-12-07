<?php
/**
 * Weather admin model
 *
 * @category   GadgetModel
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @param   string  $unit           Unit for displaying temperature
     * @param   int     $update_period  Time interval between updates
     * @param   string  $date_format    Date string format
     * @param   string  $api_key        API key
     * @return  mixed   True if update is successful or Jaws_Error on any error
     */
    function UpdateProperties($unit, $update_period, $date_format, $api_key)
    {
        $res = array();
        $res[] = $this->gadget->registry->update('unit', $unit);
        $res[] = $this->gadget->registry->update('update_period', $update_period);
        $res[] = $this->gadget->registry->update('date_format', $date_format);
        $res[] = $this->gadget->registry->update('api_key', $api_key);

        foreach ($res as $r) {
            if (Jaws_Error::IsError($r) || !$r) {
                $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('WEATHER_ERROR_PROPERTIES_NOT_UPDATED'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}