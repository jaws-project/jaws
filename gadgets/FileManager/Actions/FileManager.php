<?php
/**
 * FileManager Gadget
 *
 * @category    Gadget
 * @package     FileManager
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileManager_Actions_FileManager extends Jaws_Gadget_HTML
{
    /**
     * Builds file management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function FileManager()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('FileManager.html');
        $tpl->SetBlock('fileManager');

        $tpl->SetVariable('title', _t('FILEMANAGER_NAME'));
        $tpl->SetVariable('lbl_new', _t('GLOBAL_NEW'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));

        // display probabley responses
        $message = $GLOBALS['app']->Session->PopSimpleResponse('FileManager');
        if ($message) {
            $tpl->SetVariable('response', $message);
        }

        $tpl->ParseBlock('fileManager');
        return $tpl->Get();
    }

    /**
     * Fetches list of files
     *
     * @access  public
     * @param   int     $parent     Restrict result to a specified node
     * @param   bool    $published  If true then only published files are returned
     * @return  array   Array of files or Jaws_Error on error
     */
    function GetFiles($parent = null, $published = null)
    {
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $res = $model->GetFiles($parent, $published);
        if (Jaws_Error::IsError($res)){
            return false;
        }
        return $res;
    }

    /**
     * Fetches data of a file/dir
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Array of file data or Jaws_Error on error
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
     * Deletes file/dir
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Array of file data or Jaws_Error on error
     */
    function DeleteFile($id)
    {
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $res = $model->DeleteFile($id);
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return _t('FILEMANAGER_NOTICE_DIR_DELETED');
    }
}