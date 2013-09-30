<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notepad_Actions_Notepad extends Jaws_Gadget_HTML
{
    /**
     * Builds file management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Notepad()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Notepad/resources/site_style.css');
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Notepad.html');
        $tpl->SetBlock('notepad');

        // Response
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
        }

        $tpl->SetVariable('title', _t('NOTEPAD_NAME'));
        $tpl->SetVariable('lbl_title', _t('NOTEPAD_NOTE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('NOTEPAD_NOTE_CREATED'));
        $tpl->SetVariable('lbl_owner', _t('NOTEPAD_NOTE_OWNER'));

        // Note template
        $tpl->SetBlock('notepad/note');
        $tpl->SetVariable('id', '{id}');
        $tpl->SetVariable('url', '{url}');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('created', '{created}');
        $tpl->SetVariable('owner', '{owner}');
        $tpl->ParseBlock('notepad/note');

        // Actions
        $tpl->SetVariable('lbl_new_note', _t('NOTEPAD_NEW_NOTE'));
        $tpl->SetVariable('lbl_del_note', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('url_new', $this->gadget->urlMap('NewNote'));

        $tpl->ParseBlock('notepad');
        return $tpl->Get();
    }

    /**
     * Fetches list of notes
     *
     * @access  public
     * @return  array   List of notes or an empty array
     */
    function GetNotes()
    {
        //$data = jaws()->request->fetch(array('id', 'shared', 'foreign'));
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $notes = $model->GetNotes($user);
        if (Jaws_Error::IsError($notes)){
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        foreach ($notes as &$note) {
            $note['created'] = $objDate->Format($note['createtime'], 'n/j/Y g:i a');
            $note['modified'] = $objDate->Format($note['updatetime'], 'n/j/Y g:i a');
            $note['url'] = $this->gadget->urlMap('ViewNote', array('id' => $note['id']));
        }

        return $notes;
    }

    /**
     * Fetches data of a file/directory
     *
     * @access  public
     * @return  array   File data or an empty array
     */
    function ViewNote()
    {
        $id = jaws()->request->fetch('id');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $access = $model->CheckAccess($id, $user);
        if ($access !== true) {
            return array();
        }
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
        $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');

        // Shared for
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Share');
        $users = $model->GetFileUsers($id);
        if (!Jaws_Error::IsError($users)) {
            $uid_set = array();
            foreach ($users as $user) {
                $uid_set[] = $user['username'];
            }
            $file['users'] = implode(', ', $uid_set);
        }

        return $file;
    }

    /**
     * Builds the required form for creating a new note
     *
     * @access  public
     * @return  string  XHTML form
     */
    function NewNote()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Notepad/resources/site_style.css');
        $tpl = $this->gadget->loadTemplate('Form.html');
        $tpl->SetBlock('note');

        // Response
        $note = array();
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('note_title', $response['data']['title']);
            $note['content'] = $response['data']['content'];
        } else {
            $note['content'] = '';
        }

        $tpl->SetVariable('title', _t('NOTEPAD_NEW_NOTE'));
        $tpl->SetVariable('action', 'newnote');
        $tpl->SetVariable('form_action', 'CreateNote');
        $tpl->SetVariable('lbl_title', _t('NOTEPAD_NOTE_TITLE'));
        $tpl->SetVariable('lbl_content', _t('NOTEPAD_NOTE_CONTENT'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('Notepad'));

        // Editor
        $editor =& $GLOBALS['app']->LoadEditor('Notepad', 'content', $note['content']);
        $editor->setID('');
        $tpl->SetVariable('note_content', $editor->Get());

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('note');
        return $tpl->Get();
    }

    /**
     * Creates a new note
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateNote()
    {
        $data = jaws()->request->fetch(array('title', 'content'), 'post');
        if (empty($data['title']) || empty($data['content'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_INCOMPLETE_DATA'),
                'Notepad.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $data['title'] = Jaws_XSS::defilter($data['title']);
        $data['content'] = Jaws_XSS::defilter($data['content']);
        $result = $model->Insert($data);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_NOTE_CREATE'),
                'Notepad.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('NOTEPAD_NOTICE_NOTE_CREATED'),
            'Notepad.Response'
        );
        Jaws_Header::Location($this->gadget->urlMap('Notepad'));
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
     * Deletes passed file(s)/directorie(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteNote()
    {
        $id_set = jaws()->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('DIRECTORY_ERROR_DELETE'),
                RESPONSE_ERROR
            );
        }

        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $fault = false;
        foreach ($id_set as $id) {
            // Validate file & user
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file) || $file['user'] != $user) {
                $fault = true;
                continue;
            }

            // Delete file/directory
            $res = $model->Delete($file);
            if (Jaws_Error::IsError($res)) {
                $fault = true;
            }
        }
        
        if ($fault === true) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('DIRECTORY_WARNING_DELETE'),
                RESPONSE_WARNING
            );
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                _t('DIRECTORY_NOTICE_ITEMS_DELETED'),
                RESPONSE_NOTICE
            );
        }
    }

}