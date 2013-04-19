<?php
/**
 * ServerTime Installer
 *
 * @category    GadgetModel
 * @package     ServerTime
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        // Registry keys
        $this->gadget->registry->add('date_format',  'DN d MN Y');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Uninstall()
    {
        // Registry keys
        $this->gadget->registry->del('date_format');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        // Registry keys
        $this->gadget->registry->del('display_format');
        $this->gadget->registry->add('date_format',  'DN d MN Y');
        return true;
    }

}