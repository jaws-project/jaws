<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_Admin_Directories extends Jaws_Gadget_Action
{
    /**
     * Builds the directory view/edit form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DirectoryForm()
    {
        $mode = $this->gadget->request->fetch('mode');
        if ($mode === null) $mode = 'view';

        $tpl = $this->gadget->template->loadAdmin('Directory.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        if ($mode === 'edit') {
            $editor =& $GLOBALS['app']->LoadEditor('Directory', 'description');
            $editor->TextArea->SetStyle('width:100%; height:60px;');
            $tpl->SetVariable('description', $editor->get());
        } else {
            $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('create_time', '{create_time}');
            $tpl->SetVariable('update_time', '{update_time}');
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
            $data = $this->gadget->request->fetch(
                array('title', 'description', 'parent', 'public', 'published'),
                'post'
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }

            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $this->gadget->model->load('Files')->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
                }
            }

            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = true;
            $data['published'] = $data['published']? true : false;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            $result = $this->gadget->model->loadAdmin('Files')->InsertFile($data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
            }

            // shout Activities event
            $this->gadget->event->shout('Activities', array('action'=>'Folder'));

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
            $data = $this->gadget->request->fetch(
                array('title', 'description', 'parent', 'public', 'published'),
                'post'
            );

            // Validate data
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            $id = (int)$this->gadget->request->fetch('id', 'post');

            // Validate directory
            $dir = $this->gadget->model->load('Files')->GetFile($id);
            if (Jaws_Error::IsError($dir)) {
                throw new Exception($dir->getMessage());
            }

            // Update directory
            $data['update_time'] = time();
            $data['published'] = $data['published']? true : false;
            $result = $this->gadget->model->loadAdmin('Files')->UpdateFile($id, $data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_UPDATE'));
            }

        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_DIR_UPDATED'), RESPONSE_NOTICE);
    }
}