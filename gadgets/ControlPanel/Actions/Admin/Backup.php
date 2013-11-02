<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_Backup extends Jaws_Gadget_Action
{
    /**
     * Returns downloadable backup file
     *
     * @access  public
     * @return void
     */
    function Backup()
    {
        $this->gadget->CheckPermission('Backup');
        $tmpDir = sys_get_temp_dir();
        $domain = preg_replace("/^(www.)|(:{$_SERVER['SERVER_PORT']})$|[^a-z0-9-.]/", '', strtolower($_SERVER['HTTP_HOST']));
        $nameArchive = $domain . '-' . date('Y-m-d') . '.tar.gz';
        $pathArchive = $tmpDir . DIRECTORY_SEPARATOR . $nameArchive;

        //Dump database data
        $dbFileName = 'dbdump.xml';
        $dbFilePath = $tmpDir . DIRECTORY_SEPARATOR . $dbFileName;
        $GLOBALS['db']->Dump($dbFilePath);

        $files = array();
        require_once PEAR_PATH. 'File/Archive.php'; 
        $files[] = File_Archive::read(JAWS_DATA);
        $files[] = File_Archive::read($dbFilePath , $dbFileName);
        File_Archive::extract($files, File_Archive::toArchive($pathArchive, File_Archive::toFiles()));
        Jaws_Utils::Delete($dbFilePath);

        // browser must download file from server instead of cache
        header("Expires: 0");
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        // force download dialog
        header("Content-Type: application/force-download");
        // set data type, size and filename
        header("Content-Disposition: attachment; filename=\"$nameArchive\"");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: '.@filesize($pathArchive));
        @readfile($pathArchive);
        Jaws_Utils::Delete($pathArchive);
    }

}