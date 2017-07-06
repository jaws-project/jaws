<?php
/**
 * Languages Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
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
        $lang = $this->gadget->request->fetch('lang', 'get');

        require_once PEAR_PATH. 'File/Archive.php'; 
        $tmpDir = sys_get_temp_dir();
        $tmpFileName = "$lang.tar";
        $tmpArchiveName = $tmpDir. DIRECTORY_SEPARATOR. $tmpFileName;
        $writerObj = File_Archive::toFiles();
        $src = File_Archive::read(JAWS_DATA. "languages/$lang", $lang);
        $dst = File_Archive::toArchive($tmpArchiveName, $writerObj);
        $res = File_Archive::extract($src, $dst);
        if (!PEAR::isError($res)) {
            return Jaws_Utils::Download($tmpArchiveName, $tmpFileName);
        }

        Jaws_Header::Referrer();
    }

}
