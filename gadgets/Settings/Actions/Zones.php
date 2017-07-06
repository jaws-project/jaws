<?php
/**
 * Settings Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Settings
 */
class Settings_Actions_Zones extends Jaws_Gadget_Action
{
    /**
     * Get provinces
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetProvinces()
    {
        $country = $this->gadget->request->fetch('country');
        $provinces = $this->gadget->model->load('Zones')->GetProvinces($country);
        return Jaws_Error::IsError($provinces)? array() : $provinces;
    }

    /**
     * Get cities
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetCities()
    {
        $zone = $this->gadget->request->fetch(array('province', 'country'));
        if (is_null($zone['province'])) {
            $provinces = $this->gadget->request->fetch('province:array', 'post');
        } elseif (empty($zone['province'])) {
            return array();
        } else {
            $provinces = array($zone['province']);
        }

        $cities = $this->gadget->model->load('Zones')->GetCities($provinces, $zone['country']);
        return Jaws_Error::IsError($cities)? array() : $cities;
    }

}