<?php
/**
 * Files UninstallGadget event
 *
 * @category    Gadget
 * @package     Files
 */
class Files_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $model = $this->gadget->model->loadAdmin('Files');
        return $model->deleteGadgetFiles($gadget);
    }

}