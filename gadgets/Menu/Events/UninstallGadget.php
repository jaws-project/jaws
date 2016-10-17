<?php
/**
 * Menu UninstallGadget event
 *
 * @category    Gadget
 * @package     Menu
 */
class Menu_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $model = $this->gadget->model->loadAdmin('Menu');
        $res = $model->DeleteGadgetMenus($gadget);
        return $res;
    }

}