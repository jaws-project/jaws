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
class EventsCalendar_Actions_ManageEvents extends Jaws_Gadget_Action
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
        $tpl = $this->gadget->loadTemplate('ManageEvents.html');
        $tpl->SetBlock('events');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_NAME'));
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_start', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('lbl_stop', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));
        $tpl->SetVariable('lbl_owner', _t('EVENTSCALENDAR_EVENT_OWNER'));

        // Menubar
        $action = $this->gadget->loadAction('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('ManageEvents'));

        // Ckeck for response
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('text', $response['text']);
            $tpl->SetVariable('type', $response['type']);
        }

        // Ckeck for search query
        $params = $GLOBALS['app']->Session->PopSimpleResponse('Events.Search');
        $query = isset($params['query'])? $params['query'] : null;
        $start = isset($params['start'])? $params['start'] : null;
        $stop = isset($params['stop'])? $params['stop'] : null;
        $filter = isset($params['filter'])? (int)$params['filter'] : null;
        $shared = ($filter === 1)? true : null;
        $foreign = ($filter === 2)? true : null;

        // Fetch page
        $page = jaws()->request->fetch('page', 'get');
        $page = !empty($page)? (int)$page : 1;
        $limit = (int)$this->gadget->registry->fetch('events_limit');

        // Fetch events
        $model = $this->gadget->loadModel('Events');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $count = $model->GetNumberOfEvents($user, $query, $shared, $foreign, $start, $stop);
        $events = $model->GetEvents($user, $query, $shared, $foreign,
            $start, $stop, $limit, ($page - 1) * $limit);
        if (!Jaws_Error::IsError($events)){
            $objDate = $GLOBALS['app']->loadDate();
            foreach ($events as $event) {
                $tpl->SetBlock('events/event');
                $tpl->SetVariable('id', $event['id']);
                $tpl->SetVariable('subject', $event['subject']);
                $tpl->SetVariable('start', $objDate->Format($event['start_date'], 'n/j/Y'));
                $tpl->SetVariable('stop', $objDate->Format($event['stop_date'], 'n/j/Y'));
                $tpl->SetVariable('url', $this->gadget->urlMap('ViewEvent', array('id' => $event['id'])));
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
        $entry =& Piwi::CreateWidget('Entry', 'query', $query);
        $entry->SetID('');
        $tpl->SetVariable('query', $entry->Get());

        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'start', $start);
        $datePicker->SetId('event_start_date');
        $datePicker->showTimePicker(true);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $tpl->SetVariable('start', $datePicker->Get());
        $tpl->SetVariable('lbl_from', _t('EVENTSCALENDAR_FROM'));

        $datePicker =& Piwi::CreateWidget('DatePicker', 'stop', $stop);
        $datePicker->SetId('event_stop_date');
        $datePicker->showTimePicker(true);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->SetIncludeCSS(false);
        $datePicker->SetIncludeJS(false);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $tpl->SetVariable('stop', $datePicker->Get());
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));

        $combo =& Piwi::CreateWidget('Combo', 'filter');
        $combo->SetID('');
        $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_ALL_EVENTS'), 0);
        $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_SHARED_EVENTS_ONLY'), 1);
        $combo->AddOption(_t('EVENTSCALENDAR_SEARCH_FOREIGN_EVENTS_ONLY'), 2);
        $combo->SetDefault($filter);
        $tpl->SetVariable('filter', $combo->Get());

        $button =& Piwi::CreateWidget('Button', '', _t('EVENTSCALENDAR_SEARCH'), STOCK_SEARCH);
        $button->SetSubmit(true);
        $tpl->SetVariable('btn_search', $button->Get());

        $site_url = $GLOBALS['app']->GetSiteURL('/');
        $events_url = $site_url . $this->gadget->urlMap('ManageEvents');

        if (!is_null($params)) { // search mode
            $button =& Piwi::CreateWidget('Button', 'btn_event_search_reset', 'X');
            $button->SetSubmit(false);
            $button->AddEvent(ON_CLICK, "location.assign('$events_url')");
            $tpl->SetVariable('btn_reset', $button->Get());
        }

        // Actions
        $tpl->SetVariable('lbl_new_event', _t('EVENTSCALENDAR_NEW_EVENT'));
        $tpl->SetVariable('lbl_del_event', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS'));
        $tpl->SetVariable('errorShortQuery', _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $site_url . $this->gadget->urlMap('NewEvent'));
        $tpl->SetVariable('events_url', $events_url);

        // Pagination
        $action = $this->gadget->loadAction('Pager');
        $action->GetPagesNavigation(
            $tpl,
            'events',
            $page,
            $limit,
            $count,
            _t('EVENTSCALENDAR_EVENTS_COUNT', $count),
            'ManageEvents',
            array('page' => $page)
        );

        $tpl->ParseBlock('events');
        return $tpl->Get();
    }

    /**
     * Searches among events
     *
     * @access  public
     * @return  array   Response array
     */
    function Search()
    {
        $post = jaws()->request->fetch(array('query', 'filter', 'start', 'stop', 'page'), 'post');
        $GLOBALS['app']->Session->PushSimpleResponse(
            $post,
            'Events.Search'
        );
        $url = $this->gadget->urlMap('ManageEvents');
        Jaws_Header::Location($url);
    }
}