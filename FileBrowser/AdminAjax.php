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
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function FileBrowser_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

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
        return $this->_Model->DBFileInfo($path, $file);
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
        return $this->_Model->GetDirContentsCount($path);;
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
        $gadget = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminHTML');
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
        $gadget = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminHTML');
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
        $this->gadget->CheckPermission('ManageFiles');
        $res = true;
        if ($oldname != $file) {
            $res = $this->_Model->Rename($path, $oldname, $file);
        }

        if ($res) {
            $this->_Model->UpdateDBFileInfo($path, $file, $title, $description, $fast_url, $oldname);
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
        $this->gadget->CheckPermission('ManageDirectories');
        $res = true;
        if (empty($oldname)) {
            $res = $this->_Model->MakeDir($path, $dir);
        } elseif ($oldname != $dir) {
            $res = $this->_Model->Rename($path, $oldname, $dir);
        }

        if ($res) {
            $this->_Model->UpdateDBFileInfo($path, $dir, $title, $description, $fast_url, $oldname);
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
        $this->gadget->CheckPermission('ManageFiles');
        if ($this->_Model->Delete($path, $file)) {
            $this->_Model->DeleteDBFileInfo($path, $file);
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
        $this->gadget->CheckPermission('ManageDirectories');
        if ($this->_Model->Delete($path, $dir)) {
            $this->_Model->DeleteDBFileInfo($path, $dir);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}