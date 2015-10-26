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
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Model_Admin_Regions extends Jaws_Gadget_Model
{
    /**
     * Inserts a new region
     *
     * @access  public
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of GEO posiotion
     * @param   float   $longitude  Longitude of GEO posiotion
     * @param   bool    $published  Visibility status of GEO posiotion
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRegion($title, $fast_url, $latitude, $longitude, $published)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'weather');

        $data['title']     = $title;
        $data['fast_url']  = $fast_url;
        $data['latitude']  = (float) $latitude;
        $data['longitude'] = (float) $longitude;
        $data['published'] = $published;

        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $result = $weatherTable->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_ADDED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the region
     *
     * @access  public
     * @param   int     $id         ID of the GEO posiotion
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of the GEO posiotion
     * @param   float   $longitude  Longitude of the GEO posiotion
     * @param   bool    $published  Visibility status of the GEO posiotion
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRegion($id, $title, $fast_url, $latitude, $longitude, $published)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'weather', false);

        $data['id']        = (int)$id;
        $data['title']     = $title;
        $data['fast_url']  = $fast_url;
        $data['latitude']  = (float) $latitude;
        $data['longitude'] = (float) $longitude;
        $data['published'] = $published;

        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $result = $weatherTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the specified region
     *
     * @access  public
     * @param   int     $id  Region ID
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteRegion($id)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $result = $weatherTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}