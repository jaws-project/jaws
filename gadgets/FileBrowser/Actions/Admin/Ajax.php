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
class FileBrowser_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Gets information of the directory content
     *
     * @access   public
     * @internal param  string  $path   Where to read
     * @internal param  string  $file
     * @return   mixed  A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function DBFileInfo()
    {
        @list($path, $file) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Files');
        return $model->DBFileInfo($path, $file);
    }

    /**
     * Gets Count of items in directory
     *
     * @access   public
     * @internal param  string  $path   Where to check
     * @return   array  Count of items in directory
     */
    function GetDirContentsCount()
    {
        @list($path) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Directory');
        return $model->GetDirContentsCount($path);
    }

    /**
     * Creates and returns some data
     *
     * @access   public
     * @internal param  string  $path
     * @return   array
     */
    function GetLocation()
    {
        @list($path) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Files');
        return $gadget->GetLocation($path);
    }

    /**
     * Creates and returns some data
     *
     * @access   public
     * @internal param  string  $dir
     * @internal param  int     $offset
     * @internal param  int     $order
     * @return   array  directory array
     */
    function GetDirectory()
    {
        @list($dir, $offset, $order) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Directory');
        if (!is_numeric($offset)) {
            $offset = null;
        }

        return $gadget->GetDirectory($dir, $offset, $order);
    }

    /**
     * Add/Update file information
     *
     * @access   public
     * @internal param  string  $path           File|Directory path
     * @internal param  string  $file           File|Directory name
     * @internal param  string  $title
     * @internal param  string  $description
     * @internal param  string  $fast_url
     * @internal param  string  $oldname
     * @return   mixed  A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function UpdateDBFileInfo()
    {
        $this->gadget->CheckPermission('ManageFiles');
        @list($path, $file, $title, $description, $fast_url, $oldname) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Files');
        $res = true;
        $file = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $file);
        $oldname = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $oldname);
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
     * @access   public
     * @internal param  string  $path           File|Directory path
     * @internal param  string  $dir            File|Directory name
     * @internal param  string  $title
     * @internal param  string  $description
     * @internal param  string  $fast_url
     * @internal param  string  $oldname
     * @return   array  A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function UpdateDBDirInfo()
    {
        $this->gadget->CheckPermission('ManageDirectories');
        @list($path, $dir, $title, $description, $fast_url, $oldname) = jaws()->request->fetchAll('post');

        $fModel = $this->gadget->model->loadAdmin('Files');
        $dModel = $this->gadget->model->loadAdmin('Directory');
        $res = true;
        $dir = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $dir);
        $oldname = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $oldname);
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
     * @access   public
     * @internal param  string  $path   File path
     * @internal param  string  $file   File name
     * @return   array  Response array (notice or error)
     */
    function DeleteFile2()
    {
        $this->gadget->CheckPermission('ManageFiles');
        @list($path, $file) = jaws()->request->fetchAll('post');
        $fModel = $this->gadget->model->loadAdmin('Files');
        $file = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $file);
        if ($fModel->Delete($path, $file)) {
            $fModel->DeleteDBFileInfo($path, $file);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete directory information
     *
     * @access   public
     * @internal param  string  $path   Directory path
     * @internal param  string  $dir    Directory name
     * @return   array  Response array (notice or error)
     */
    function DeleteDir2()
    {
        $this->gadget->CheckPermission('ManageDirectories');
        @list($path, $dir) = jaws()->request->fetchAll('post');
        $fModel = $this->gadget->model->loadAdmin('Files');
        $dir = preg_replace('/[^[:alnum:]_\.-\s]*/', '', $dir);
        if ($fModel->Delete($path, $dir)) {
            $fModel->DeleteDBFileInfo($path, $dir);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}