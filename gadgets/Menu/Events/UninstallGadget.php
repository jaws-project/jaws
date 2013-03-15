<?php
/**
 * Menu UninstallGadget event
 *
 * @category   Gadget
 * @package    Menu
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Menu_Events_UninstallGadget extends Jaws_Gadget
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget)
    {
        //Blog model
        $mModel = $GLOBALS['app']->loadGadget('Menu', 'AdminModel');
        $res = $mModel->DeleteGadgetMenus($gadget);
        return $res;
    }

}