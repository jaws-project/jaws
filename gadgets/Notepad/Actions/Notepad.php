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
$GLOBALS['app']->Layout->AddHeadLink('gadgets/Notepad/resources/site_style.css');
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

        // Notes
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $notes = $model->GetNotes($user);
        if (!Jaws_Error::IsError($notes)){
            $objDate = $GLOBALS['app']->loadDate();
            foreach ($notes as $note) {
                $tpl->SetBlock('notepad/note');
                $tpl->SetVariable('id', $note['id']);
                $tpl->SetVariable('title', $note['title']);
                $tpl->SetVariable('owner', $note['owner']);
                $tpl->SetVariable('created', $objDate->Format($note['createtime'], 'n/j/Y g:i a'));
                $tpl->SetVariable('url', $this->gadget->urlMap('ViewNote', array('id' => $note['id'])));
                $tpl->ParseBlock('notepad/note');
            }
        }

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
     * Builds UI to display a single note
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewNote()
    {
        $tpl = $this->gadget->loadTemplate('View.html');
        $tpl->SetBlock('view');

        // Response
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
        }

        $id = (int)jaws()->request->fetch('id', 'get');
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $note = $model->GetNote($id);
        if (Jaws_Error::IsError($note) || empty($note)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_RETRIEVING_DATA'),
                'Notepad.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        $this->AjaxMe('site_script.js');
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('note_title', $note['title']);
        $tpl->SetVariable('note_content', $this->gadget->ParseText($note['content'], 'Notepad'));

        // Actions
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('NOTEPAD_WARNING_DELETE_NOTE'));
        $tpl->SetVariable('notepad_url', $this->gadget->urlMap('Notepad'));
        $tpl->SetVariable('url_edit', $this->gadget->urlMap('EditNote', array('id' => $id)));

        $tpl->ParseBlock('view');
        return $tpl->Get();
    }

    /**
     * Builds form to create a new note
     *
     * @access  public
     * @return  string  XHTML form
     */
    function NewNote()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Form.html');
        $tpl->SetBlock('form');

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
        $tpl->SetVariable('errorIncompleteData', _t('NOTEPAD_ERROR_INCOMPLETE_DATA'));
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

        $tpl->ParseBlock('form');
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
     * Builds form to edit a note
     *
     * @access  public
     * @return  string  XHTML form
     */
    function EditNote()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Form.html');
        $tpl->SetBlock('form');

        // Response
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
            $note = $response['data'];
        }

        if (!isset($note) || empty($note)) {
            $id = (int)jaws()->request->fetch('id', 'get');
            $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $note = $model->GetNote($id);
            if (Jaws_Error::IsError($note)) {
                $GLOBALS['app']->Session->PushResponse(
                    _t('NOTEPAD_ERROR_RETRIEVING_DATA'),
                    'Notepad.Response',
                    RESPONSE_ERROR
                );
                Jaws_Header::Referrer();
            }
        }

        $tpl->SetVariable('title', _t('NOTEPAD_EDIT_NOTE'));
        $tpl->SetVariable('errorIncompleteData', _t('NOTEPAD_ERROR_INCOMPLETE_DATA'));
        $tpl->SetVariable('action', 'editnote');
        $tpl->SetVariable('form_action', 'UpdateNote');
        $tpl->SetVariable('id', $note['id']);

        // Title
        $tpl->SetVariable('note_title', $note['title']);
        $tpl->SetVariable('lbl_title', _t('NOTEPAD_NOTE_TITLE'));

        // Editor
        $editor =& $GLOBALS['app']->LoadEditor('Notepad', 'content', $note['content']);
        $editor->setID('');
        $tpl->SetVariable('note_content', $editor->Get());
        $tpl->SetVariable('lbl_content', _t('NOTEPAD_NOTE_CONTENT'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('Notepad'));

        $tpl->ParseBlock('form');
        return $tpl->Get();
    }

    /**
     * Updates note
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateNote()
    {
        $data = jaws()->request->fetch(array('id', 'title', 'content'), 'post');
        if (empty($data['id']) || empty($data['title']) || empty($data['content'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_INCOMPLETE_DATA'),
                'Notepad.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        // Validate note
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $id = (int)$data['id'];
        $note = $model->GetNote($id);
        if (Jaws_Error::IsError($note)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_RETRIEVING_DATA'),
                'Notepad.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        // Validate owner
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        if ($note['user'] != $user) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_NO_PERMISSION'),
                'Notepad.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        $data['title'] = Jaws_XSS::defilter($data['title']);
        $data['content'] = Jaws_XSS::defilter($data['content']);
        $result = $model->Update($id, $data);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_NOTE_UPDATE'),
                'Notepad.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('NOTEPAD_NOTICE_NOTE_UPDATED'),
            'Notepad.Response'
        );
        Jaws_Header::Location($this->gadget->urlMap('Notepad'));
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
        //_log_var_dump($id_set);
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('NOTEPAD_ERROR_NOTE_DELETE'),
                RESPONSE_ERROR
            );
        }

        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $fault = false;
        foreach ($id_set as $id) {
            // Validate note & user
            $note = $model->GetNote($id);
            if (Jaws_Error::IsError($note) || $note['user'] != $user) {
                $fault = true;
                continue;
            }

            // Delete note
            $res = $model->Delete($id);
            if (Jaws_Error::IsError($res)) {
                $fault = true;
            }
        }
        
        if ($fault === true) {
            $msg = (count($id_set) === 1)?
                _t('NOTEPAD_ERROR_NOTE_DELETE') :
                _t('NOTEPAD_WARNING_DELETE_NOTES_FAILED');
            // FIXME: we are creating response twice
            $GLOBALS['app']->Session->PushResponse($msg, 'Notepad.Response', RESPONSE_ERROR);
            return $GLOBALS['app']->Session->GetResponse($msg, RESPONSE_ERROR);
        }

        $msg = (count($id_set) === 1)?
            _t('NOTEPAD_NOTICE_NOTE_DELETED') :
            _t('NOTEPAD_NOTICE_NOTES_DELETED');
        $GLOBALS['app']->Session->PushResponse($msg, 'Notepad.Response');
        return $GLOBALS['app']->Session->GetResponse($msg);
    }

}