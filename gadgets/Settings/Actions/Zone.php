<?php
/**
 * Settings Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Settings
 */
class Settings_Actions_Zone extends Jaws_Gadget_Action
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
        $model = $this->gadget->model->load('Zone');
        $res = $model->GetCities($provinces);
        if (Jaws_Error::IsError($res) || $res === false) {
            return array();
        } else {
            return $res;
        }
    }

}