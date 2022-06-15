<?php
/**
 * Comments UninstallGadget event
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Events_UninstallGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $mModel = $this->gadget->model->loadAdmin('Comments');
        $res = $mModel->DeleteGadgetComments($gadget);
        return $res;
    }

}