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
class FileManager_Actions_Dirs extends Jaws_Gadget_HTML
{
    /**
     * Builds the directory management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DirForm()
    {
        $tpl = $this->gadget->loadTemplate('FileManager.html');
        $tpl->SetBlock('dirForm');
        $tpl->SetVariable('lbl_title', _t('FILEMANAGER_DIR_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('FILEMANAGER_DIR_DESC'));
        $tpl->SetVariable('lbl_parent', _t('FILEMANAGER_DIR_PARENT'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        $tpl->ParseBlock('dirForm');
        return $tpl->Get();
    }

    /**
     * Creates a new directory
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateDir()
    {
        $request =& Jaws_Request::getInstance();
        $data = $request->get(array('title', 'description', 'parent', 'published'));
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $data['published'] = ($data['user'] == 1)? true : false;
        $data['is_dir'] = true;
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $result = $model->InsertFile($data);
        if (Jaws_Error::IsError($result)) {
            $msg = _t('FILEMANAGER_ERROR_DIR_CREATE');
        } else {
            $msg = _t('FILEMANAGER_NOTICE_DIR_CREATED');
        }

        $GLOBALS['app']->Session->PushSimpleResponse($msg, 'FileManager');
        Jaws_Header::Referrer();
    }

}