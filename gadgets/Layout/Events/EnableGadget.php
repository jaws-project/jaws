<?php
/**
 * Layout EnableGadget event
 *
 * @category   Gadget
 * @package    Layout
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Events_EnableGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $lModel = $this->gadget->model->loadAdmin('Layout');
        $res = $lModel->PublishGadgetElements($gadget, true);
        return $res;
    }

}