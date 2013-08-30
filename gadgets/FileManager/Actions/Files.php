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
class FileManager_Actions_Files extends Jaws_Gadget_HTML
{
    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $tpl = $this->gadget->loadTemplate('FileManager.html');
        $tpl->SetBlock('fileForm');
        $tpl->SetVariable('lbl_title', _t('FILEMANAGER_FILE_TITLE'));
        $tpl->SetVariable('lbl_dir', _t('FILEMANAGER_FILE_DIR'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));

        // display probabley responses
        $message = $GLOBALS['app']->Session->PopSimpleResponse('FileManager');
        if ($message) {
            //$tpl->SetBlock('fileForm/response');
            $tpl->SetVariable('response', $message);
            //$tpl->ParseBlock('fileForm/response');
        }

        $tpl->ParseBlock('fileForm');
        return $tpl->Get();
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateFile()
    {
        $request =& Jaws_Request::getInstance();
        $data = $request->get(array('title', 'dir_id'));
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('FileManager', 'Model', 'Files');
        $result = $model->InsertFile($data);
        if (Jaws_Error::IsError($result)) {
            $msg = _t('FILEMANAGER_ERROR_FILE_CREATE');
        } else {
            $msg = _t('FILEMANAGER_NOTICE_FILE_CREATED');
        }

        $GLOBALS['app']->Session->PushSimpleResponse($msg, 'FileManager');
        Jaws_Header::Referrer();
    }

}