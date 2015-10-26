<?php
/**
 * UrlMapper UpgradeGadget event
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Events_UpgradeGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $gadget)
    {
        $uModel = $this->gadget->model->loadAdmin('Maps');
        $res = $uModel->UpdateGadgetMaps($gadget);
        return $res;
    }

}