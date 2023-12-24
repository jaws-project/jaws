<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$this->app->layout->addLink('gadgets/Notepad/Resources/site_style.css');
class Notepad_Actions_Notepad extends Jaws_Gadget_Action
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
        $tpl = $this->gadget->template->load('Notepad.html');
        $tpl->SetBlock('notepad');

        $tpl->SetVariable('title', $this->gadget->title);
        $tpl->SetVariable('lbl_title', $this::t('NOTE_TITLE'));
        $tpl->SetVariable('lbl_created', $this::t('NOTE_CREATED'));
        $tpl->SetVariable('lbl_shared', $this::t('SHARED'));
        $tpl->SetVariable('lbl_owner', $this::t('NOTE_OWNER'));

        // Ckeck for response
        $response = $this->gadget->session->pop('Response');
        if ($response) {
            $tpl->SetVariable('response_text', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        // Fetch url params
        $get = $this->gadget->request->fetch(array('filter', 'query', 'page'), 'get');
        foreach ($get as $k => $v) {
            if ($v === null) {
                unset($get[$k]);
            }
        }

        // Prepare action arguments
        $query = isset($get['query'])? $get['query'] : null;
        $filter = isset($get['filter'])? (int)$get['filter'] : null;
        $shared = ($filter === 1)? true : null;
        $foreign = ($filter === 2)? true : null;
        $page = isset($get['page'])? $get['page'] : 1;
        $limit = (int)$this->gadget->registry->fetch('notes_limit');

        // Fetch notes
        $model = $this->gadget->model->load('Notepad');
        $user = (int)$this->app->session->user->id;
        $count = $model->GetNumberOfNotes($user, $shared, $foreign, $query);
        $notes = $model->GetNotes($user, $shared, $foreign, $query, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($notes)){
            $objDate = Jaws_Date::getInstance();
            foreach ($notes as $note) {
                $tpl->SetBlock('notepad/note');
                $tpl->SetVariable('id', $note['id']);
                $tpl->SetVariable('title', $note['title']);
                $tpl->SetVariable('created', $objDate->Format($note['createtime'], 'MM/dd/yyyy hh:mm aa'));
                $tpl->SetVariable('url', $this->gadget->urlMap('OpenNote', array('id' => $note['id'])));
                if ($note['user'] != $user) {
                    $tpl->SetVariable('shared', '');
                    $tpl->SetVariable('nickname', $note['nickname']);
                    $tpl->SetVariable('username', $note['username']);
                } else {
                    $tpl->SetVariable('shared', $note['shared']? $this::t('SHARED') : '');
                    $tpl->SetVariable('nickname', '');
                    $tpl->SetVariable('username', '');
                }
                $tpl->ParseBlock('notepad/note');
            }
        }

        // Search
        $combo =& Piwi::CreateWidget('Combo', 'filter');
        $combo->SetID('');
        $combo->AddOption($this::t('SEARCH_ALL_NOTES'), 0);
        $combo->AddOption($this::t('SEARCH_SHARED_NOTES_ONLY'), 1);
        $combo->AddOption($this::t('SEARCH_FOREIGN_NOTES_ONLY'), 2);
        $combo->SetDefault($filter);
        $tpl->SetVariable('filter', $combo->Get());

        $entry =& Piwi::CreateWidget('Entry', 'query', $query);
        $entry->SetID('');
        $entry->AddEvent(ON_CHANGE, 'onSearchChange(this.value)');
        $entry->AddEvent(ON_KUP, 'onSearchChange(this.value)');
        $tpl->SetVariable('query', $entry->Get());

        $button =& Piwi::CreateWidget('Button', '', $this::t('SEARCH'), STOCK_SEARCH);
        $button->SetSubmit(true);
        $tpl->SetVariable('btn_search', $button->Get());

        $notepad_url = $this->gadget->urlMap('Notepad');
        $button =& Piwi::CreateWidget('Button', 'btn_note_search_reset', 'X');
        $button->SetSubmit(false);
        $button->AddEvent(ON_CLICK, "window.location='$notepad_url'");
        if (empty($query)) {
            $button->SetStyle('display:none;');
        }
        $tpl->SetVariable('btn_reset', $button->Get());

        // Actions
        $tpl->SetVariable('lbl_new_note', $this::t('NEW_NOTE'));
        $tpl->SetVariable('lbl_del_note', Jaws::t('DELETE'));
        $tpl->SetVariable('confirmDelete', $this::t('WARNING_DELETE_NOTES'));
        $tpl->SetVariable('errorShortQuery', $this::t('ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $this->gadget->urlMap('NewNote'));
        $tpl->SetVariable('notepad_url', $notepad_url);

        // Pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $limit,
            $count,
            'Notepad',
            $get,
            $this::t('NOTES_COUNT', $count)
        );

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
        $post = $this->gadget->request->fetch(array('filter', 'query', 'page'), 'post');
        foreach ($post as $k => $v) {
            if ($v === null) {
                unset($post[$k]);
            }
        }
        $url = $this->gadget->urlMap('Notepad', $post);
        return Jaws_Header::Location($url);

        /*if (strlen($search['query']) < 2) {
            $this->gadget->session->push(
                $this::t('ERROR_SHORT_QUERY'),
                RESPONSE_ERROR,
                'Response'
            );
        }*/
    }
}