<?php
/**
 * FileBrowser AJAX API
 *
 * @category   Ajax
 * @package    FileBrowser
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2010-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Gets information of the directory content
     *
     * @access  public
     * @param   string  $path   Where to read
     * @param   string  $file   
     * @return  mixed   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function DBFileInfo($path, $file)
    {
        $model = $GLOBALS['app']->loadGadget('FileBrowser', 'Model', 'File');
        return $model->DBFileInfo($path, $file);
    }

    /**
     * Gets Count of items in directory
     *
     * @access  public
     * @param   string  $path   Where to check
     * @return  array   Count of items in directory
     */
    function GetDirContentsCount($path)
    {
        $model = $GLOBALS['app']->loadGadget('FileBrowser', 'Model', 'Directory');
        return $model->GetDirContentsCount($path);
    }

    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $path
     * @return  array   
     */
    function GetLocation($path)
    {
        $gadget = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminHTML', 'File');
        return $gadget->GetLocation($path);
    }

    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $dir
     * @param   int     $offset
     * @param   int     $order
     * @return  array   directory array
     */
    function GetDirectory($dir, $offset, $order)
    {
        $gadget = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminHTML', 'Directory');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        return $gadget->GetDirectory($dir, $offset, $order);
    }

    /**
     * Add/Update file information
     *
     * @access  public
     * @param   string  $path           File|Directory path
     * @param   string  $file           File|Directory name
     * @param   string  $title
     * @param   string  $description
     * @param   string  $fast_url
     * @param   string  $oldname
     * @return  mixed   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function UpdateDBFileInfo($path, $file, $title, $description, $fast_url, $oldname)
    {
        $model = $GLOBALS['app']->loadGadget('FileBrowser', 'AdminModel', 'File');
        $this->gadget->CheckPermission('ManageFiles');
        $res = true;
        $file = preg_replace('/[^[:alnum:]_\.-]*/', '', $file);
        $oldname = preg_replace('/[^[:alnum:]_\.-]*/', '', $oldname);
        if ($oldname != $file) {
            $res = $model->Rename($path, $oldname, $file);
        }

        if ($res) {
            $model->UpdateDBFileInfo($path, $file, $title, $description, $fast_url, $oldname);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Add/Update directory information
     *
     * @access  public
     * @param   string  $path           File|Directory path
     * @param   string  $dir            File|Directory name
     * @param   string  $title
     * @param   string  $description
     * @param   string  $fast_url
     * @param   string  $oldname
     * @return  array   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function UpdateDBDirInfo($path, $dir, $title, $description, $fast_url, $oldname)
    {
        $fModel = $GLOBALS['app']->loadGadget('FileBrowser', 'AdminModel', 'File');
        $dModel = $GLOBALS['app']->loadGadget('FileBrowser', 'AdminModel', 'Directory');

        $this->gadget->CheckPermission('ManageDirectories');
        $res = true;
        $dir = preg_replace('/[^[:alnum:]_\.-]*/', '', $dir);
        $oldname = preg_replace('/[^[:alnum:]_\.-]*/', '', $oldname);
        if (empty($oldname)) {
            $res = $dModel->MakeDir($path, $dir);
        } elseif ($oldname != $dir) {
            $res = $fModel->Rename($path, $oldname, $dir);
        }

        if ($res) {
            $fModel->UpdateDBFileInfo($path, $dir, $title, $description, $fast_url, $oldname);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete file information
     *
     * @access  public
     * @param   string  $path   File path
     * @param   string  $file   File name
     * @return  array   Response array (notice or error)
     */
    function DeleteFile($path, $file)
    {
        $fModel = $GLOBALS['app']->loadGadget('FileBrowser', 'AdminModel', 'File');
        $this->gadget->CheckPermission('ManageFiles');
        $file = preg_replace('/[^[:alnum:]_\.-]*/', '', $file);
        if ($fModel->Delete($path, $file)) {
            $fModel->DeleteDBFileInfo($path, $file);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete directory information
     *
     * @access  public
     * @param   string  $path   Directory path
     * @param   string  $dir    Directory name
     * @return  array   Response array (notice or error)
     */
    function DeleteDir($path, $dir)
    {
        $fModel = $GLOBALS['app']->loadGadget('FileBrowser', 'AdminModel', 'File');
        $this->gadget->CheckPermission('ManageDirectories');
        $dir = preg_replace('/[^[:alnum:]_\.-]*/', '', $dir);
        if ($fModel->Delete($path, $dir)) {
            $fModel->DeleteDBFileInfo($path, $dir);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}