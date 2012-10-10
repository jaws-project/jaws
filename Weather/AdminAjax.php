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
class WeatherAdminAjax extends Jaws_Ajax
{
    /**
     * Gets associated data of the region
     *
     * @access  public
     * @param   int     $id ID of the geo posiotion
     * @return  array   Array with the associated data of region
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
     * Creates a new region
     *
     * @access  public
     * @param   string  $title      Title of the geo posiotion
     * @param   string  $fast_url   Fast_URL
     * @param   float   $latitude   Latitude of the geo posiotion
     * @param   float   $longitude  Longitude of the geo posiotion
     * @return  bool    True on success and Jaws_Error on failure
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
     * @param   int     $id         ID of the geo posiotion
     * @param   string  $title      Title of the geo posiotion
     * @param   string  $fast_url   Fast_URL
     * @param   float   $latitude   Latitude of the geo posiotion
     * @param   float   $longitude  Longitude of the geo posiotion
     * @param   bool    $published  Whether be published or not
     * @return  bool    True on success and Jaws_Error on failure
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
     * @param   int     $id     ID of the region
     * @return  bool    True on success and Jaws_Error on failure
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
     * @return  bool    True if update is successful or Jaws_Error on any error
     */
    function UpdateProperties($unit, $update_period, $date_format)
    {
        $this->CheckSession('Weather', 'UpdateProperties');
        $this->_Model->UpdateProperties($unit, $update_period, $date_format);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets Data for grid
     *
     * @access  public
     * @param   int     $offset Data offset
     * @param   string  $grid   Name of the grid
     * @return  array   Regions
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