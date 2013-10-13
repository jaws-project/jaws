<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/resources/site_style.css');
class EventsCalendar_Actions_Events extends Jaws_Gadget_HTML
{
    /**
     * Builds notes management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Events()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Events.html');
        $tpl->SetBlock('events');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_NAME'));
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_start', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));
        $tpl->SetVariable('lbl_owner', _t('EVENTSCALENDAR_EVENT_OWNER'));

        // Ckeck for response
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
        }

        // Fetch url params
        // $get = jaws()->request->fetch(array('filter', 'query', 'page'), 'get');
        // foreach ($get as $k => $v) {
            // if ($v === null) {
                // unset($get[$k]);
            // }
        // }

        // Prepare action arguments
        // $query = isset($get['query'])? $get['query'] : null;
        // $filter = isset($get['filter'])? (int)$get['filter'] : null;
        // $shared = ($filter === 1)? true : null;
        // $foreign = ($filter === 2)? true : null;
        // $page = isset($get['page'])? $get['page'] : 1;
        // $limit = (int)$this->gadget->registry->fetch('events_limit');

        // Fetch events
        /*$model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $count = $model->GetNumberOfNotes($user, $shared, $foreign, $query);
        $notes = $model->GetNotes($user, $shared, $foreign, $query, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($notes)){
            $objDate = $GLOBALS['app']->loadDate();
            foreach ($notes as $note) {
                $tpl->SetBlock('notepad/note');
                $tpl->SetVariable('id', $note['id']);
                $tpl->SetVariable('title', $note['title']);
                $tpl->SetVariable('created', $objDate->Format($note['createtime'], 'n/j/Y g:i a'));
                $tpl->SetVariable('url', $this->gadget->urlMap('OpenNote', array('id' => $note['id'])));
                if ($note['user'] != $user) {
                    $tpl->SetVariable('shared', '');
                    $tpl->SetVariable('nickname', $note['nickname']);
                    $tpl->SetVariable('username', $note['username']);
                } else {
                    $tpl->SetVariable('shared', $note['shared']? _t('EVENTSCALENDAR_SHARED') : '');
                    $tpl->SetVariable('nickname', '');
                    $tpl->SetVariable('username', '');
                }
                $tpl->ParseBlock('notepad/note');
            }
        }*/

        // Search
        // $combo =& Piwi::CreateWidget('Combo', 'filter');
        // $combo->SetID('');
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_ALL_NOTES'), 0);
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_SHARED_NOTES_ONLY'), 1);
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_FOREIGN_NOTES_ONLY'), 2);
        // $combo->SetDefault($filter);
        // $tpl->SetVariable('filter', $combo->Get());

        // $entry =& Piwi::CreateWidget('Entry', 'query', $query);
        // $entry->SetID('');
        // $entry->AddEvent(ON_CHANGE, 'onSearchChange(this.value)');
        // $entry->AddEvent(ON_KUP, 'onSearchChange(this.value)');
        // $tpl->SetVariable('query', $entry->Get());

        // $button =& Piwi::CreateWidget('Button', '', _t('EVENTSCALENDAR_SEARCH'), STOCK_SEARCH);
        // $button->SetSubmit(true);
        // $tpl->SetVariable('btn_search', $button->Get());

        $events_url = $this->gadget->urlMap('Events');
        $button =& Piwi::CreateWidget('Button', 'btn_note_search_reset', 'X');
        $button->SetSubmit(false);
        $button->AddEvent(ON_CLICK, "window.location='$events_url'");
        if (empty($query)) {
            $button->SetStyle('display:none;');
        }
        $tpl->SetVariable('btn_reset', $button->Get());

        // Actions
        $tpl->SetVariable('lbl_new_event', _t('EVENTSCALENDAR_NEW_EVENT'));
        $tpl->SetVariable('lbl_del_event', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS'));
        $tpl->SetVariable('errorShortQuery', _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $this->gadget->urlMap('NewEvent'));
        $tpl->SetVariable('events_url', $events_url);

        // Pagination
        // $action = $GLOBALS['app']->LoadGadget('Notepad', 'HTML', 'Pager');
        // $action->GetPagesNavigation(
            // $tpl,
            // 'notepad',
            // $page,
            // $limit,
            // $count,
            // _t('EVENTSCALENDAR_NOTES_COUNT', $count),
            // 'Notepad',
            // $get
        // );

        $tpl->ParseBlock('events');
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
        $post = jaws()->request->fetch(array('filter', 'query', 'page'), 'post');
        foreach ($post as $k => $v) {
            if ($v === null) {
                unset($post[$k]);
            }
        }
        $url = $this->gadget->urlMap('Notepad', $post);
        Jaws_Header::Location($url);

        /*if (strlen($search['query']) < 2) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'),
                'Notepad.Response',
                RESPONSE_ERROR
            );
        }*/
    }
}