<?php
/**
 * Weather AJAX API
 *
 * @category   Ajax
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_AdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Gets associated data of the region
     *
     * @access  public
     * @param   int     $id ID of the GEO posiotion
     * @return  mixed   Array of region data ot false
     */
    function GetRegion($id)
    {
        $region = $this->_Model->GetRegion($id);
        if (Jaws_Error::IsError($region)) {
            return false;
        }

        return $region;
    }

    /**
     * Inserts a new region
     *
     * @access  public
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of the GEO posiotion
     * @param   float   $longitude  Longitude of the GEO posiotion
     * @param   bool    $published  Publish status
     * @return  array   Response (success or failure)
     */
    function InsertRegion($title, $fast_url, $latitude, $longitude, $published)
    {
        $this->CheckSession('Weather', 'ManageRegions');
        $this->_Model->InsertRegion($title, $fast_url, $latitude, $longitude, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the specified region
     *
     * @access  public
     * @param   int     $id         ID of the GEO posiotion
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of the GEO posiotion
     * @param   float   $longitude  Longitude of the GEO posiotion
     * @param   bool    $published  Publish status
     * @return  array   Response (success or failure)
     */
    function UpdateRegion($id, $title, $fast_url, $latitude, $longitude, $published)
    {
        $this->CheckSession('Weather', 'ManageRegions');
        $this->_Model->UpdateRegion($id, $title, $fast_url, $latitude, $longitude, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the specified region
     *
     * @access  public
     * @param   int     $id  Region ID
     * @return  array   Response (success or failure)
     */
    function DeleteRegion($id)
    {
        $this->CheckSession('Weather', 'ManageRegions');
        $this->_Model->DeleteRegion($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @param   string  $unit           Unit for displaying temperature
     * @param   int     $update_period  Time interval between updates
     * @param   string  $date_format    Date string format
     * @param   string  $api_key        API key
     * @return  array   Response (success or failure)
     */
    function UpdateProperties($unit, $update_period, $date_format, $api_key)
    {
        $this->CheckSession('Weather', 'UpdateProperties');
        $this->_Model->UpdateProperties($unit, $update_period, $date_format, $api_key);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets data for grid
     *
     * @access  public
     * @param   int     $offset Data offset
     * @param   string  $grid   Name of the grid
     * @return  array   List of regions
     */
    function GetData($offset, $grid)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Weather', 'AdminHTML');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetRegions($offset);
    }

}