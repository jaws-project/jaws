<?php
/**
 * ControlPanel Installer
 *
 * @category    GadgetModel
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  bool    True on successful installation or Jaws_Error otherwise
     */
    function Install()
    {
        // Registry keys
        $this->gadget->registry->insert(
            'update_last_checking',
            serialize(array('version' => '', 'time' => 0))
        );

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    True on Success
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            // ACLs keys
            $this->gadget->acl->delete('Backup');
        }

        return true;
    }

}