<?php
/**
 * TMS (Theme Management System) Gadget
 *
 * @category   GadgetModel
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class TmsAdminModel extends Jaws_Gadget_Model
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
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
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateGadget($old, $new)
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
        $this->DelRegistry('pluggable');
        $this->DelRegistry('share_mode');

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

    /**
     * Creates a .zip file of the theme in themes/ directory
     *
     * @access  public
     * @param   string  $theme      Name of the theme
     * @param   string  $srcDir     Source directory
     * @param   string  $destDir    Target directory
     * @param   bool    $copy_example_to_repository  If copy example.png too or not
     * @return  bool    Returns true if:
     *                    - Theme exists
     *                    - Theme exists and could be packed
     *                  Returns false if:
     *                    - Theme doesn't exist
     *                    - Theme doesn't exists and couldn't be packed
     */
    function packTheme($theme, $srcDir, $destDir, $copy_example_to_repository = true)
    {
        $themeSrc = $srcDir. '/'. $theme;
        if (!is_dir($themeSrc)) {
            return new Jaws_Error(_t('TMS_ERROR_THEME_DOES_NOT_EXISTS', $theme), _t('TMS_NAME'));
        }

        if (!Jaws_Utils::is_writable($destDir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', $destDir),
                                  _t('TMS_NAME'));
        }

        $themeDest = $destDir. '/'. $theme. '.zip';
        //If file exists.. delete it
        if (file_exists($themeDest)) {
            @unlink($themeDest);
        }

        require_once 'File/Archive.php';
        $res = File_Archive::extract(File_Archive::read($themeSrc, $theme),
                                     File_Archive::toArchive($themeDest,
                                                             File_Archive::toFiles()
                                                            )
                                    );
        if (PEAR::isError($res)) {
            return new Jaws_Error(_t('TMS_ERROR_COULD_NOT_PACK_THEME'), _t('TMS_NAME'));
        }
        Jaws_Utils::chmod($themeDest);

        if ($copy_example_to_repository) {
            //Copy image to repository/images
            if (file_exists($srcDir . '/example.png')) {
                @copy($srcDir. '/example.png', JAWS_DATA. "themes/repository/images/$theme.png");
                Jaws_Utils::chmod(JAWS_DATA . 'themes/repository/images/' . $theme . '.png');
            }
        }

        return $themeDest;
    }

}