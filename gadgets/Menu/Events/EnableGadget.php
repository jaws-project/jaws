<?php
/**
 * Menu EnableGadget event
 *
 * @category    Gadget
 * @package     Menu
 */
class Menu_Events_EnableGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $model = $this->gadget->model->loadAdmin('Menu');
        $res = $model->PublishGadgetMenus($gadget, true);
        return $res;
    }

}