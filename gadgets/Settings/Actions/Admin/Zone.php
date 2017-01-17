<?php
/**
 * Settings Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Settings
 */
class Settings_Actions_Admin_Zone extends Settings_Actions_Admin_Default
{
    /**
     * Get cities
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetCities()
    {
        $province = jaws()->request->fetch('province', 'post');
        $model = $this->gadget->model->load('Zone');
        $res = $model->GetCities(array($province));
        if (Jaws_Error::IsError($res) || $res === false) {
            return array();
        } else {
            return $res;
        }
    }

}