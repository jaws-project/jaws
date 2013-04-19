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
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA), _t('TMS_NAME'));
        }

        $theme_dir = JAWS_DATA. 'themes'. DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($theme_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $theme_dir), _t('TMS_NAME'));
        }

        //Ok, maybe user has data/themes dir but is not writable, Tms requires that dir to be writable
        if (!Jaws_Utils::is_writable(JAWS_DATA . 'themes')) {
            return new Jaws_Error(_t('TMS_ERROR_DESTINATION_THEMES_NOT_WRITABLE'), _t('TMS_NAME'));
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
        $tables = array('tms_themes',
                        'tms_authors',
                        'tms_repositories');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('TMS_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->registry->del('pluggable');
        $this->gadget->registry->del('share_mode');

        // ACL keys.
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Tms/UploadTheme',   'false'); 
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Tms/DownloadTheme', 'false'); 
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Tms/ManageRepositories');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Tms/ManageSharing');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Tms/ManageSettings');

        // Directories
        Jaws_Utils::Delete(JAWS_DATA. 'themes/repository');

        return true;
    }

}