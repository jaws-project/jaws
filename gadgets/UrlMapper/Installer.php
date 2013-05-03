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
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'InstallGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UpgradeGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UninstallGadget');
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'HTTPError');

        // Registry keys
        $this->gadget->registry->insert(array(
            'map_enabled' => 'true',
            'map_use_file' => 'true',
            'map_use_rewrite' => 'false',
            'map_map_to_use' => 'both',
            'map_custom_precedence' => 'false',
            'map_restrict_multimap' => 'false',
            'map_extensions' => 'html',
            'map_use_aliases' => 'true',
        ));

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
            $umapModel = $this->gadget->load('Model')->load('AdminModel');
            $gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
            $gadgets = array_filter(explode(',', $gadgets));
            foreach ($gadgets as $gadget) {
                $res = $umapModel->UpdateGadgetMaps($gadget);
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        Jaws_Utils::Delete(JAWS_DATA . 'cache/maps.php');
        return true;
    }

}