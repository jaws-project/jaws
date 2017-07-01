<?php
/**
 * UrlMapper Installer
 *
 * @category    GadgetModel
 * @package     UrlMapper
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
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
        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.1.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}