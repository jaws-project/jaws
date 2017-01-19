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
     * Get cities
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetCities()
    {
        $zone = jaws()->request->fetch(array('province', 'country'));
        if (empty($zone['province'])) {
            $provinces = jaws()->request->fetch('province:array', 'post');
        } else {
            $provinces = array($zone['province']);
        }

        $cities = $this->gadget->model->load('Zones')->GetCities($provinces, $zone['country']);
        if (Jaws_Error::IsError($cities) || $cities === false) {
            return array();
        } else {
            return $cities;
        }
    }

}