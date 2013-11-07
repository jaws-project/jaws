<?php
/**
 * UrlMapper Installer
 *
 * @category    GadgetModel
 * @package     UrlMapper
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('map_enabled', 'true'),
        array('map_use_file', 'true'),
        array('map_use_rewrite', 'false'),
        array('map_map_to_use', 'both'),
        array('map_custom_precedence', 'false'),
        array('map_restrict_multimap', 'false'),
        array('map_extensions', 'html'),
        array('map_use_aliases', 'true'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageMaps',
        'ManageAliases',
        'ManageErrorMaps',
        'ManageProperties',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Install listener for Add/Upgrade/Removing gadget's maps
        $this->gadget->event->insert('InstallGadget');
        $this->gadget->event->insert('UpgradeGadget');
        $this->gadget->event->insert('UninstallGadget');
        $this->gadget->event->insert('HTTPError');

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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.3.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Update all gadgets maps
            $umapModel = $this->gadget->loadAdminModel('Maps');
            $gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
            $gadgets = array_filter(explode(',', $gadgets));
            foreach ($gadgets as $gadget) {
                $res = $umapModel->UpdateGadgetMaps($gadget);
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }

            $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
            $mapsTable->delete()->where('vars_regexps', null, 'is null')->exec();

            // ACL keys
            $this->gadget->acl->insert('ManageMaps');
            $this->gadget->acl->insert('ManageAliases');
            $this->gadget->acl->insert('ManageErrorMaps');
            $this->gadget->acl->insert('ManageProperties');
            $this->gadget->acl->delete('EditMaps');

            // Remove old event listener
            $this->gadget->event->delete();
            // Install listener for Add/Upgrade/Removing gadget's maps
            $this->gadget->event->insert('InstallGadget');
            $this->gadget->event->insert('UpgradeGadget');
            $this->gadget->event->insert('UninstallGadget');
            $this->gadget->event->insert('HTTPError');
        }

        return true;
    }

}