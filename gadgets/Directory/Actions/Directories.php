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
        $mode = jaws()->request->fetch('mode', 'post');
        $tpl = $this->gadget->loadTemplate('Directory.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'view') {
            $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_owner', _t('DIRECTORY_FILE_OWNER'));
            $tpl->SetVariable('lbl_shared', _t('DIRECTORY_SHARED_FOR'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('username', '{username}');
            $tpl->SetVariable('users', '{users}');
            $tpl->SetVariable('createtime', '{createtime}');
            $tpl->SetVariable('updatetime', '{updatetime}');
            $tpl->SetVariable('created', '{created}');
            $tpl->SetVariable('modified', '{modified}');
        }
        $tpl->ParseBlock($mode);
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
            $result = $model->Insert($data);
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
            $data = jaws()->request->fetch(array('title', 'description', 'parent'), 'post');

            // Validate data
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            $id = (int)jaws()->request->fetch('id', 'post');
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');

            // Validate directory
            $dir = $model->GetFile($id);
            if (Jaws_Error::IsError($dir)) {
                throw new Exception($dir->getMessage());
            }

            // Validate user
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($dir['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_UPDATE'));
            }

            // Update directory
            $data['updatetime'] = time();
            $result = $model->Update($id, $data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_UPDATE'));
            }

            // Update shortcuts
            if ($dir['shared']) {
                $shortcut = array('updatetime' => $data['updatetime']);
                $model->UpdateShortcuts($id, $shortcut);
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes directory recursively
     *
     * @access  public
     * @param   int     $id     Directory ID to be deleted - optional
     * @return  mixed   Response array or Jaws_Error on error
     */
    function DeleteDirectory($id = null)
    {
        if ($id === null) {
            $id = (int)jaws()->request->fetch('id');
        }
        try {
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

            // Validate file
            $dir = $model->GetFile($id);
            if (Jaws_Error::IsError($dir)) {
                throw new Exception($dir->getMessage());
            }

            // Delete files and sub directories
            $files = $model->GetFiles($id, $user);
            if (Jaws_Error::IsError($files)) {
                throw new Exception($files->getMessage());
            }
            foreach ($files as $file) {
                if ($file['is_dir']) {
                    $res = $this->DeleteDirectory($file['id']);
                } else {
                    $res = $this->DeleteFile($file['id']);
                }
                //_log_var_dump($res);
                // FIXME: what should we do if one delete call fails?
            }

            // Validate user
            if ($dir['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_DELETE'));
            }

            // Delete directory from database
            $res = $model->Delete($id);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_DELETED'), RESPONSE_NOTICE);
    }
}