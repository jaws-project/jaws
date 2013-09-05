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
        $tpl->SetVariable('lbl_desc', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
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
        try {
            $request =& Jaws_Request::getInstance();
            $data = $request->get(array('title', 'description', 'parent', 'url'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = false;

            // File upload
            $path = $GLOBALS['app']->getDataURL('directory/' . $data['user']);
            if (!file_exists($path)) {
                Jaws_Utils::mkdir($path, 2);
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res === false) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
            } else {
                $data['filename'] = $res['file'][0]['host_filename'];
                $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
                $res = $model->InsertFile($data);
                if (Jaws_Error::IsError($res)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
                }
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'Directory', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(_t('DIRECTORY_NOTICE_FILE_CREATED'), 'Directory');
        Jaws_Header::Referrer();
    }

    /**
     * Updates file
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateFile()
    {
        try {
            $request =& Jaws_Request::getInstance();
            $id = (int)$request->get('id');
            $data = $request->get(array('title', 'description', 'parent', 'url'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = false;

            // File upload
            $path = $GLOBALS['app']->getDataURL('directory/' . $data['user']);
            if (!file_exists($path)) {
                Jaws_Utils::mkdir($path, 2);
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['filename'] = $res['file'][0]['host_filename'];
            }
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $res = $model->UpdateFile($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'Directory', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(_t('DIRECTORY_NOTICE_FILE_UPDATED'), 'Directory');
        Jaws_Header::Referrer();
    }

}