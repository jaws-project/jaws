<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Files extends Jaws_Gadget_HTML
{
    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $tpl = $this->gadget->loadTemplate('Directory.html');
        $tpl->SetBlock('fileForm');
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_parent', _t('DIRECTORY_FILE_PARENT'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        $tpl->ParseBlock('fileForm');
        return $tpl->Get();
    }

    /**
     * Creates a new file/directory
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateFile()
    {
        $request =& Jaws_Request::getInstance();
        $data = $request->get(array('dir_id', 'title', 'description', 'parent'));
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $data['is_dir'] = ($data['is_dir'] == 1)? true : false;
        //_log_var_dump($data);
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $result = $model->InsertFile($data);
        //$result = true;
        if (Jaws_Error::IsError($result)) {
            $msg = _t('DIRECTORY_ERROR_FILE_CREATE');
        } else {
            $msg = _t('DIRECTORY_NOTICE_FILE_CREATED');
        }

        $GLOBALS['app']->Session->PushSimpleResponse($msg, 'Directory');
        Jaws_Header::Referrer();
    }

}