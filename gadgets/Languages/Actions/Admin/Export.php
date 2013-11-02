<?php
/**
 * Languages Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Languages_Actions_Admin_Export extends Jaws_Gadget_Action
{
    /**
     * Export language
     *
     * @access  public
     * @return  void
     */
    function Export()
    {
        $lang = jaws()->request->fetch('lang', 'get');

        require_once PEAR_PATH. 'File/Archive.php'; 
        $tmpDir = sys_get_temp_dir();
        $tmpFileName = "$lang.tar";
        $tmpArchiveName = $tmpDir. DIRECTORY_SEPARATOR. $tmpFileName;
        $res = File_Archive::extract(
            File_Archive::read(JAWS_DATA. "languages/$lang", $lang),
            File_Archive::toArchive($tmpArchiveName, File_Archive::toFiles())
        );
        if (!PEAR::isError($res)) {
            return Jaws_Utils::Download($tmpArchiveName, $tmpFileName);
        }

        Jaws_Header::Referrer();
    }

}
