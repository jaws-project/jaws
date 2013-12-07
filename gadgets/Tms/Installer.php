<?php
/**
 * Tms Installer
 *
 * @category    GadgetModel
 * @package     Tms
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
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
        'DownloadTheme'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $theme_dir = JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($theme_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $theme_dir));
        }

        //Ok, maybe user has data/themes dir but is not writable, Tms requires that dir to be writable
        if (!Jaws_Utils::is_writable(JAWS_DATA . 'themes')) {
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
        return true;
    }

}