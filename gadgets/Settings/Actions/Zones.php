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
        $province = (int)jaws()->request->fetch('province', 'post');
        if (empty($province)) {
            $provinces = jaws()->request->fetch('provinces:array', 'post');
        } else {
            $provinces = array($province);
        }
        $zModel = $this->gadget->model->load('Zones');
        $cities = $zModel->GetCities($provinces);
        if (Jaws_Error::IsError($cities) || $cities === false) {
            return array();
        } else {
            return $cities;
        }
    }

}