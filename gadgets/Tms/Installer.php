<?php
/**
 * Tms Installer
 *
 * @category    GadgetModel
 * @package     Tms
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Tms_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'UploadTheme',
        'DownloadTheme',
        'DeleteTheme'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(ROOT_DATA_PATH)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_DIRECTORY_UNWRITABLE', ROOT_DATA_PATH));
        }

        $theme_dir = ROOT_DATA_PATH. 'themes'. DIRECTORY_SEPARATOR;
        if (!$this->app->fileManagement::mkdir($theme_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $theme_dir));
        }

        //Ok, maybe user has data/themes dir but is not writable, Tms requires that dir to be writable
        if (!Jaws_Utils::is_writable(ROOT_DATA_PATH . 'themes')) {
            return new Jaws_Error(_t('TMS_ERROR_DESTINATION_THEMES_NOT_WRITABLE'));
        }

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
            $this->gadget->acl->insert('DeleteTheme', '', false);
        }

        return true;
    }

}