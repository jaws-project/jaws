<?php
/**
 * Settings Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Settings
 */
class Settings_Actions_Translates extends Jaws_Gadget_Action
{
    /**
     * deletes expired cache
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function getTranslates()
    {
        $result = array();
        $modules = $this->gadget->request->fetch('modules');
        $modules = array_filter(explode(',', $modules));
        foreach ($modules as $module) {
            @list($type, $module) = explode(':', $module);
            $type = (int)$type;
            $module = $type ==0 ? '' : strtoupper($module);

            $result[$type][$module] = Jaws_Translate::getInstance()->getTranslation($module, $type);
        }

        return $result;
    }

}