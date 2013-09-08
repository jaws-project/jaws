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
class Directory_Actions_Directories extends Jaws_Gadget_HTML
{
    /**
     * Builds the directory view/edit form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DirectoryForm()
    {
        $type = jaws()->request->fetch('type', 'post');
        $tpl = $this->gadget->loadTemplate('Directory.html');
        $tpl->SetBlock($type);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_DIR_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_DIR_DESC'));
        $tpl->SetVariable('lbl_share', _t('DIRECTORY_SHARE'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($type === 'view') {
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
        }
        $tpl->ParseBlock($type);
        return $tpl->Get();
    }

    /**
     * Creates a new directory
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateDirectory()
    {
        try {
            $data = jaws()->request->fetch(array('title', 'description', 'parent'), 'post');
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = $data['owner'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = true;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $result = $model->InsertFile($data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_CREATED'), RESPONSE_NOTICE);
    }

    /**
     * Updates directory
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateDirectory()
    {
        try {
            $id = jaws()->request->fetch('id', 'post');
            $data = jaws()->request->fetch(array('title', 'description', 'parent'), 'post');
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            //$data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $result = $model->UpdateFile($id, $data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_UPDATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes directory
     *
     * @access  public
     * @return  mixed   Response array or Jaws_Error on error
     */
    function DeleteDirectory()
    {
        $id = (int)jaws()->request->fetch('id');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->DeleteFile($id);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_DELETED'), RESPONSE_NOTICE);
    }

}