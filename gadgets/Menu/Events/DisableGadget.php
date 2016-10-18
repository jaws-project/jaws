<?php
/**
 * Menu DisableGadget event
 *
 * @category    Gadget
 * @package     Menu
 */
class Menu_Events_DisableGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $model = $this->gadget->model->loadAdmin('Menu');
        $res = $model->PublishGadgetMenus($gadget, false);
        return $res;
    }

}