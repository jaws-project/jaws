<?php
/**
 * FileManager AJAX API
 *
 * @category    Ajax
 * @package     FileManager
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileManager_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Fetches list of files/dirs
     *
     * @access  public
     * @return  array   Array of files/dirs
     */
    function GetFiles($parent = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $res = $model->GetFiles($parent);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Fetches data of file/dir
     *
     * @access  public
     * @return  array   Array of file/dir data
     */
    function GetFile($id)
    {
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $res = $model->GetFile($id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Fetches path of a file/dir
     *
     * @access  public
     * @param   int     $id     File or Directory ID
     * @param   array   $path   Directory hierarchy
     * @return  void
     */
    function GetPath($id)
    {
        $path = array();
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $model->GetPath($id, $path);
        return $path;
    }

    /**
     * Fetches directory management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GetDirForm()
    {
        $gadget = $GLOBALS['app']->LoadGadget('FileManager', 'HTML', 'Dirs');
        return $gadget->DirForm();
    }

    /**
     * Fetches file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GetFileForm()
    {
        $gadget = $GLOBALS['app']->LoadGadget('FileManager', 'HTML', 'Files');
        return $gadget->FileForm();
    }

    /**
     * Deletes file/dir
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DeleteFile($id)
    {
        $gadget = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $res = $gadget->DeleteFile($id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return _t('FILEMANAGER_NOTICE_DIR_DELETED');
    }

}