<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
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
        if (!$GLOBALS['app']->Session->Logged()) {
            $userGadget = Jaws_Gadget::getInstance('Users');
            return Jaws_Header::Location(
                $userGadget->urlMap(
                    'LoginBox',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                ), 401
            );
        }

        // Validate user
        $user = (int)$this->gadget->request->fetch('user:int', 'get');
        if ($user > 0 && $user !== (int)$GLOBALS['app']->Session->GetAttribute('user')) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->addLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ManageEvents.html');
        $tpl->SetBlock('events');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_EVENTS_MANAGE'));
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_TIME'));
        $tpl->SetVariable('lbl_public', _t('EVENTSCALENDAR_EVENT_PUBLIC'));
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));
        $tpl->SetVariable('lbl_owner', _t('EVENTSCALENDAR_EVENT_OWNER'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // Check for response
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('response_text', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        // Check for search query
        $params = array();
        $params['user'] = $user;
        $params['search'] = $GLOBALS['app']->Session->PopSimpleResponse('Events.Search', false);

        // Fetch page
        $page = (int)$this->gadget->request->fetch('page:int', 'get');
        $page = ($page === 0)? 1: $page;
        $params['limit'] = (int)$this->gadget->registry->fetch('events_limit');
        $params['offset'] = ($page - 1) * $params['limit'];

        // Fetch events
        $model = $this->gadget->model->load('Events');
        $count = $model->GetEvents($params, true);
        $events = $model->GetEvents($params);
        if (Jaws_Error::IsError($events)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(500);
        }

        $jDate = Jaws_Date::getInstance();
        foreach ($events as $event) {
            $tpl->SetBlock('events/event');
            $tpl->SetVariable('id', $event['id']);
            $tpl->SetVariable('subject', $event['subject']);
            $tpl->SetVariable('public', $event['public']? _t('EVENTSCALENDAR_EVENT_PUBLIC') : '');

            $startDate = $jDate->Format($event['start_time'], 'Y/m/d');
            $stopDate = $jDate->Format($event['stop_time'], 'Y/m/d');
            $date = ($startDate == $stopDate)? $startDate :
                $startDate . _t('EVENTSCALENDAR_TO') . $stopDate;
            $tpl->SetVariable('date', $date);

            $start_time = $jDate->Format($event['start_time'], 'H:i');
            $time = ($event['start_time'] == $event['stop_time'])?
                $start_time : $start_time . _t('EVENTSCALENDAR_TO') .
                $jDate->Format($event['stop_time'], 'H:i');
            $tpl->SetVariable('time', $time);

            $url = $this->gadget->urlMap('ViewEvent', array('user' => $user, 'event' => $event['id']));
            $tpl->SetVariable('url', $url);

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

        // Search
        $combo =& Piwi::CreateWidget('Combo', 'public');
        $combo->SetID('');
        $combo->AddOption('', '');
        $combo->AddOption(_t('GLOBAL_YES'), 1);
        $combo->AddOption(_t('GLOBAL_NO'), 0);
        $combo->SetDefault(@$params['search']['public']);
        $tpl->SetVariable('public', $combo->Get());
        $tpl->SetVariable('lbl_public', _t('EVENTSCALENDAR_EVENT_PUBLIC'));

        $combo =& Piwi::CreateWidget('Combo', 'type');
        $combo->SetID('');
        $combo->AddOption('', '');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_1'), 1);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_2'), 2);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_3'), 3);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_4'), 4);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_5'), 5);
        $combo->SetDefault(@$params['search']['type']);
        $tpl->SetVariable('type', $combo->Get());
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        $combo =& Piwi::CreateWidget('Combo', 'priority');
        $combo->SetID('');
        $combo->AddOption('', '');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_0'), 0);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_1'), 1);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_2'), 2);
        $combo->SetDefault(@$params['search']['priority']);
        $tpl->SetVariable('priority', $combo->Get());
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));

        $combo =& Piwi::CreateWidget('Combo', 'shared');
        $combo->SetID('');
        $combo->AddOption('', '');
        $combo->AddOption(_t('GLOBAL_YES'), 1);
        $combo->AddOption(_t('GLOBAL_NO'), 0);
        $combo->SetDefault(@$params['search']['shared']);
        $tpl->SetVariable('shared', $combo->Get());
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));

        $entry =& Piwi::CreateWidget('Entry', 'term', @$params['search']['term']);
        $entry->SetID('');
        $tpl->SetVariable('term', $entry->Get());
        $tpl->SetVariable('lbl_term', _t('EVENTSCALENDAR_TERM'));

        // stat time
        $tpl->SetBlock('events/start_time');
        $this->gadget->action->load('DatePicker')->calendar(
            $tpl,
            array('name' => 'start', 'value'=>@$params['search']['start'])
        );
        $tpl->ParseBlock('events/start_time');

        // stop time
        $tpl->SetBlock('events/stop_time');
        $this->gadget->action->load('DatePicker')->calendar(
            $tpl,
            array('name' => 'stop', 'value'=>@$params['search']['stop'])
        );
        $tpl->ParseBlock('events/stop_time');

        $button =& Piwi::CreateWidget('Button', '', _t('EVENTSCALENDAR_SEARCH'), STOCK_SEARCH);
        $button->SetSubmit(true);
        $tpl->SetVariable('btn_search', $button->Get());

        if (!is_null(@$params['search'])) { // search mode
            $button =& Piwi::CreateWidget('Button', '', _t('EVENTSCALENDAR_RESET'), STOCK_REFRESH);
            $button->SetSubmit(false);
            $button->AddEvent(ON_CLICK, 'resetSearch(this.form)');
            $tpl->SetVariable('btn_reset', $button->Get());
        }

        // Actions
        $siteUrl = $GLOBALS['app']->GetSiteURL('/');
        $eventsUrl = $siteUrl . $this->gadget->urlMap('ManageEvents', array('user' => $user));
        $tpl->SetVariable('lbl_new_event', _t('EVENTSCALENDAR_NEW_EVENT'));
        $tpl->SetVariable('lbl_del_event', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('confirmDelete', _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS'));
        $tpl->SetVariable('errorShortQuery', _t('EVENTSCALENDAR_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('url_new', $siteUrl . $this->gadget->urlMap('EditEvent',
            array('user' => $user, 'event' => 0)));
        $tpl->SetVariable('events_url', $eventsUrl);

        // Pagination
        $this->gadget->action->load('Navigation')->pagination(
            $tpl,
            $page,
            $params['limit'],
            $count,
            'ManageEvents',
            array('user' => $user),
            _t('EVENTSCALENDAR_EVENTS_COUNT', $count)
        );

        $tpl->ParseBlock('events');
        return $tpl->Get();
    }

    /**
     * Searches among events
     *
     * @access  public
     * @return  void
     */
    function Search()
    {
        $fields = array('term', 'public', 'shared', 'type', 'priority', 'start', 'stop');
        $post = $this->gadget->request->fetch($fields, 'post');
        foreach ($post as $key => $value) {
            if ($value === '' || $value === null) {
                unset($post[$key]);
            }
        }
        if (empty($post)) {
            $post = null;
        }
        $GLOBALS['app']->Session->PushSimpleResponse(
            $post,
            'Events.Search'
        );
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $url = $this->gadget->urlMap('ManageEvents', array('user' => $user));
        return Jaws_Header::Location($url);
    }
}
