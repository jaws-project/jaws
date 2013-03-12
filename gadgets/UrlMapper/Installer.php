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
        $GLOBALS['app']->Event->AddListener($this->gadget->name, 'End_InstallGadget', 'AddGadgetMaps');
        $GLOBALS['app']->Event->AddListener($this->gadget->name, 'End_UpgradeGadget', 'UpdateGadgetMaps');
        $GLOBALS['app']->Event->AddListener($this->gadget->name, 'Begin_UninstallGadget', 'RemoveGadgetMaps');

        // Registry keys
        $this->gadget->AddRegistry(array(
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
        if (version_compare($old, '0.2.0', '<')) {
            $result = $this->installSchema('0.2.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.3.0', '<')) {
            $result = $this->installSchema('0.3.0.xml', '', '0.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $GLOBALS['db']->dropTable('custom_maps');
            if (Jaws_Error::IsError($result)) {
                //not important
            }

            // Install listener for Add/Update/Removing gadget's maps
            $GLOBALS['app']->Event->AddListener($this->gadget->name, 'End_InstallGadget', 'AddGadgetMaps');
            $GLOBALS['app']->Event->AddListener($this->gadget->name, 'End_UpgradeGadget', 'UpdateGadgetMaps');
            $GLOBALS['app']->Event->AddListener($this->gadget->name, 'Begin_UninstallGadget', 'RemoveGadgetMaps');
        }

        if (version_compare($old, '0.3.1', '<')) {
            $result = $this->installSchema('0.3.1.xml', '', '0.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $umapModel = $this->gadget->load('Model')->loadModel('AdminModel');
        if (version_compare($old, '0.3.2', '<')) {
            $sql = 'DELETE FROM [[url_maps]]';
            $result = $GLOBALS['db']->query($sql);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('0.3.2.xml', '', '0.3.1.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Add all gadgets maps
            $gadgets  = $GLOBALS['app']->Registry->Get('gadgets_enabled_items');
            $cgadgets = $GLOBALS['app']->Registry->Get('gadgets_core_items');
            $gadgets  = explode(',', $gadgets);
            $cgadgets = explode(',', $cgadgets);
            $final = array_merge($gadgets, $cgadgets);
            foreach ($final as $gadget) {
                if (!empty($gadget)) {
                    $res = $umapModel->AddGadgetMaps($gadget);
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                }
            }
        }

        if (version_compare($old, '0.4.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.3.2.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Update all gadgets maps
            $gadgets  = $GLOBALS['app']->Registry->Get('gadgets_enabled_items');
            $cgadgets = $GLOBALS['app']->Registry->Get('gadgets_core_items');
            $gadgets  = explode(',', $gadgets);
            $cgadgets = explode(',', $cgadgets);
            $final = array_merge($gadgets, $cgadgets);
            foreach ($final as $gadget) {
                if (!empty($gadget)) {
                    $res = $umapModel->UpdateGadgetMaps($gadget);
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                }
            }
        }

        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        Jaws_Utils::Delete(JAWS_DATA . 'cache/maps.php');
        return true;
    }

}