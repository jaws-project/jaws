<?php
/**
 * Weather AJAX API
 *
 * @category   Ajax
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Gets associated data of the region
     *
     * @access  public
     * @return  mixed   Array of region data ot false
     */
    function GetRegion()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Regions');
        $region = $model->GetRegion($id);
        if (Jaws_Error::IsError($region)) {
            return false;
        }

        return $region;
    }

    /**
     * Inserts a new region
     *
     * @access  public
     * @return  array   Response (success or failure)
     */
    function InsertRegion()
    {
        $this->gadget->CheckPermission('ManageRegions');
        @list($title, $fast_url, $latitude, $longitude, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Regions');
        $model->InsertRegion($title, $fast_url, $latitude, $longitude, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the specified region
     *
     * @access  public
     * @return  array   Response (success or failure)
     */
    function UpdateRegion()
    {
        $this->gadget->CheckPermission('ManageRegions');
        @list($id, $title, $fast_url, $latitude, $longitude, $published) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Regions');
        $model->UpdateRegion($id, $title, $fast_url, $latitude, $longitude, $published);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the specified region
     *
     * @access  public
     * @return  array   Response (success or failure)
     */
    function DeleteRegion()
    {
        $this->gadget->CheckPermission('ManageRegions');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Regions');
        $model->DeleteRegion($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @return  array   Response (success or failure)
     */
    function UpdateProperties()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        @list($unit, $update_period, $date_format, $api_key) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Properties');
        $model->UpdateProperties($unit, $update_period, $date_format, $api_key);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets data for grid
     *
     * @access  public
     * @return  array   List of regions
     */
    function getData()
    {
        @list($offset, $grid) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Regions');
        if (!is_numeric($offset)) {
            $offset = null;
        }
        return $gadget->GetRegions($offset);
    }

}