<?php
/**
 * Components Installer
 *
 * @category    GadgetModel
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('versions_remote_access', 'false'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageGadgets',
        'ManagePlugins',
        'ManageRegistry',
        'ManageACLs'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  bool    True
     */
    function Install()
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
        if (version_compare($old, '0.3.0', '<')) {
            // Registry keys
            $this->gadget->registry->insert('versions_remote_access', 'false');

            // ACL keys
            $this->gadget->acl->insert('ManageRegistry');
            $this->gadget->acl->insert('ManageACLs');
        }

        return true;
    }

}