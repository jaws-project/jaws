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
     * Builds notes management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Notepad()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Notepad.html');
        $tpl->SetBlock('notepad');

        $tpl->SetVariable('title', _t('NOTEPAD_NAME'));
        $tpl->SetVariable('lbl_title', _t('NOTEPAD_NOTE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('NOTEPAD_NOTE_CREATED'));
        $tpl->SetVariable('lbl_owner', _t('NOTEPAD_NOTE_OWNER'));

        // Ckeck for response
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
        }

        // Check for search
        $response = $GLOBALS['app']->Session->PopResponse('Notepad.Data');
        $query = $response['data'];
        $tpl->SetVariable('query', $query);

        // Notes
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $notes = $model->GetNotes($user, null, null, $query);
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

        // Search
        $button =& Piwi::CreateWidget('Button', '', 'Search', STOCK_SEARCH);
        $button->SetSubmit(false);
        $button->AddEvent(ON_CLICK, 'searchNotes(this.form)');
        $tpl->SetVariable('btn_search', $button->Get());

        // Actions
        $tpl->SetVariable('lbl_new_note', _t('NOTEPAD_NEW_NOTE'));
        $tpl->SetVariable('lbl_del_note', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('NOTEPAD_WARNING_DELETE_NOTES'));
        $tpl->SetVariable('errorShortQuery', _t('NOTEPAD_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $this->gadget->urlMap('NewNote'));
        $tpl->SetVariable('notepad_url', $this->gadget->urlMap('Notepad'));

        $tpl->ParseBlock('notepad');
        return $tpl->Get();
    }

    /**
     * Searches through notes including shared noes from other users
     *
     * @access  public
     * @return  array   Response array
     */
    function Search()
    {
        $query = jaws()->request->fetch('query', 'post');
        if (strlen($query) < 2) {
            $GLOBALS['app']->Session->PushResponse(
                _t('NOTEPAD_ERROR_SHORT_QUERY'),
                'Notepad.Response',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                '',
                'Notepad.Data',
                RESPONSE_NOTICE,
                $query
            );
        }
        Jaws_Header::Referrer();
    }
}