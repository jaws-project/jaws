<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Model_Admin_Directory extends Jaws_Gadget_Model
{
    /**
     * Creates a directory
     *
     * @access  public
     * @param   string  $path       Where to create it
     * @param   string  $dir_name   Which name
     * @return  bool    Returns true if the directory was created, if not, returns false
     */
    function MakeDir($path, $dir_name)
    {
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        $dir = $fModel->GetFileBrowserRootDir(). $path. '/'. $dir_name;

        require_once PEAR_PATH. 'File/Util.php';
        $realpath = File_Util::realpath($dir);
        $blackList = explode(',', $this->gadget->registry->fetch('black_list'));
        $blackList = array_map('strtolower', $blackList);

        if (!File_Util::pathInRoot($realpath, $fModel->GetFileBrowserRootDir()) ||
            in_array(strtolower(basename($realpath)), $blackList) ||
            !Jaws_Utils::mkdir($realpath))
        {
            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_CREATE_DIRECTORY', $realpath), RESPONSE_ERROR);
            return false;
        }

        return true;
    }

}