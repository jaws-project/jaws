<?php
/**
 * Tags UninstallGadget event
 *
 * @category    Gadget
 * @package     Tags
 */
class Tags_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $model = $this->gadget->model->loadAdmin('Tags');
        return $model->DeleteGadgetTags($gadget);
    }

}