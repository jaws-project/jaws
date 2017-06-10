<?php
/**
 * Categories UninstallGadget event
 *
 * @category    Gadget
 * @package     Categories
 */
class Categories_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        return $this->gadget->model->loadAdmin('Categories')->DeleteGadgetCategories($gadget);
    }

}