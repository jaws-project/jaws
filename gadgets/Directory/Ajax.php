<?php
/**
 * Directory AJAX API
 *
 * @category    Ajax
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Fetches list of files/directories
     *
     * @access  public
     * @return  array   Array of files/directories
     */
    function GetFiles($parent = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->GetFiles($parent);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Fetches data of file/directory
     *
     * @access  public
     * @return  array   Array of file/directory data
     */
    function GetFile($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->GetFile($id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @param   int     $id     File or Directory ID
     * @param   array   $path   Directory hierarchy
     * @return  void
     */
    function GetPath($id)
    {
        $path = array();
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $model->GetPath($id, $path);
        return $path;
    }

    /**
     * Fetches directory management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GetDirectoryForm()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Directory', 'HTML', 'Directories');
        return $gadget->DirectoryForm();
    }

    /**
     * Fetches file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GetFileForm()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Directory', 'HTML', 'Files');
        return $gadget->FileForm();
    }

    /**
     * Deletes directory
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DeleteDirectory($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->DeleteFile($id);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_DIR_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Deletes file
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DeleteFile($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->DeleteFile($id);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_FILE_DELETED'),
            RESPONSE_NOTICE
        );
    }

}