<?php
/**
 * ControlPanel Core Gadget
 *
 * @category   GadgetModel
 * @package    ControlPanel
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanelAdminModel extends Jaws_Model
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  bool    True on successful installation or Jaws_Error otherwise
     */
    function InstallGadget()
    {
        //registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/ControlPanel/pluggable', 'false');
        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    True on Success
     */
    function UpdateGadget($old, $new)
    {
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/ControlPanel/Backup', 'false');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/ControlPanel/DatabaseBackups');

        return true;
    }

}