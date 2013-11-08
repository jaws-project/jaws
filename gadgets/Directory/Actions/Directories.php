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
class Directory_Actions_Directories extends Jaws_Gadget_Action
{
    /**
     * Builds the directory view/edit form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DirectoryForm()
    {
        $mode = jaws()->request->fetch('mode');
        if ($mode === null) $mode = 'view';
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

            $model = $this->gadget->model->load('Files');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $model->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
                }
                if ($parent['user'] != $user) {
                    throw new Exception(_t('DIRECTORY_ERROR_NO_PERMISSION'));
                }
            }

            $data['user'] = $data['owner'] = $user;
            $data['is_dir'] = true;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            $result = $model->Insert($data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_CREATED'), RESPONSE_NOTICE);
        // return $GLOBALS['app']->Session->PushResponse(
                // _t('DIRECTORY_NOTICE_DIR_CREATED')
            // );
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
            $model = $this->gadget->model->load('Files');

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
}