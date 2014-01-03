<?php
/**
 * Sitemap Installer
 *
 * @category    GadgetModel
 * @package     Sitemap
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('sitemap_default_priority', '0.5'),
        array('sitemap_default_frequency', Sitemap_Info::SITEMAP_CHANGE_FREQ_WEEKLY),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'PingSite',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'sitemap' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

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
        $result = $GLOBALS['db']->dropTable('sitemap');
        if (Jaws_Error::IsError($result)) {
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
            return new Jaws_Error($errMsg);
        }

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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('0.9.1.xml', '', '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('schema.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Delete old Sitemap layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->DeleteGadgetElements($this->gadget->name);
            }
        }

        return true;
    }

}