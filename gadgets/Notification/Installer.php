<?php
/**
 * Notification Installer
 *
 * @category    GadgetModel
 * @package     Notification
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Notification_Installer extends Jaws_Gadget_Installer
{
    /**
     * Default ACL value of the gadget frontend
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = true;

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $this->gadget->event->insert('Notify');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error otherwise
     */
    function Uninstall()
    {
        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        return true;
    }

}