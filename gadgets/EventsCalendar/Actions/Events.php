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
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/site_style.css');
class EventsCalendar_Actions_Events extends Jaws_Gadget_Action
{
    /**
     * Builds events management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ManageEvents()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Events.html');
        $tpl->SetBlock('events');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_NAME'));
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_start', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('lbl_stop', _t('GLOBAL_STOP_TIME'));
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
        $model = $this->gadget->loadModel('Events');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        //$count = $model->GetNumberOfEvents($user, $shared, $foreign, $query);
        //$events = $model->GetEvents($user, $shared, $foreign, $query, $limit, ($page - 1) * $limit);
        $events = $model->GetEvents($user);
        if (!Jaws_Error::IsError($events)){
            $objDate = $GLOBALS['app']->loadDate();
            foreach ($events as $event) {
                $tpl->SetBlock('events/event');
                $tpl->SetVariable('id', $event['id']);
                $tpl->SetVariable('subject', $event['subject']);
                $tpl->SetVariable('start', $objDate->Format($event['start_date'], 'n/j/Y'));
                $tpl->SetVariable('stop', $objDate->Format($event['stop_date'], 'n/j/Y'));
                $tpl->SetVariable('url', $this->gadget->urlMap('EditEvent', array('id' => $event['id'])));
                if ($event['user'] != $user) {
                    $tpl->SetVariable('shared', '');
                    $tpl->SetVariable('nickname', $event['nickname']);
                    $tpl->SetVariable('username', $event['username']);
                } else {
                    $tpl->SetVariable('shared', $event['shared']? _t('EVENTSCALENDAR_SHARED') : '');
                    $tpl->SetVariable('nickname', '');
                    $tpl->SetVariable('username', '');
                }
                $tpl->ParseBlock('events/event');
            }
        }

        // Search
        // $combo =& Piwi::CreateWidget('Combo', 'filter');
        // $combo->SetID('');
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_ALL_EVENTS'), 0);
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_SHARED_EVENTS_ONLY'), 1);
        // $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_FOREIGN_EVENTS_ONLY'), 2);
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

        $site_url = $GLOBALS['app']->GetSiteURL('/');
        $events_url = $site_url . $this->gadget->urlMap('ManageEvents');
        $button =& Piwi::CreateWidget('Button', 'btn_events_search_reset', 'X');
        $button->SetSubmit(false);
        $button->AddEvent(ON_CLICK, "location.assign('$events_url')");
        if (empty($query)) {
            $button->SetStyle('display:none;');
        }
        $tpl->SetVariable('btn_reset', $button->Get());

        // Actions
        $tpl->SetVariable('lbl_new_event', _t('EVENTSCALENDAR_NEW_EVENT'));
        $tpl->SetVariable('lbl_del_event', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS'));
        $tpl->SetVariable('errorShortQuery', _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $site_url . $this->gadget->urlMap('NewEvent'));
        $tpl->SetVariable('events_url', $events_url);

        // Pagination
        // $action = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Action', 'Pager');
        // $action->GetPagesNavigation(
            // $tpl,
            // 'events',
            // $page,
            // $limit,
            // $count,
            // _t('EVENTSCALENDAR_EVENTS_COUNT', $count),
            // 'EventsCalendar',
            // $get
        // );

        $tpl->ParseBlock('events');
        return $tpl->Get();
    }

    /**
     * Searches through events including shared events from other users
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
        $url = $this->gadget->urlMap('EventsCalendar', $post);
        Jaws_Header::Location($url);

        /*if (strlen($search['query']) < 2) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'),
                'Events.Response',
                RESPONSE_ERROR
            );
        }*/
    }
}