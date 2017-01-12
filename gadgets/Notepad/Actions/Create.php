<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->addLink('gadgets/Notepad/Resources/site_style.css');
class Notepad_Actions_Create extends Jaws_Gadget_Action
{
    /**
     * Builds form to create a new note
     *
     * @access  public
     * @return  string  XHTML form
     */
    function NewNote()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('Form.html');
        $tpl->SetBlock('form');

        // Response
        $note = array();
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('response_text', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
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
        $editor->setID('content');
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

        $model = $this->gadget->model->load('Notepad');
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
}