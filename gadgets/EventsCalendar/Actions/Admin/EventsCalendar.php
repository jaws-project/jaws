<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Admin_EventsCalendar extends EventsCalendar_Actions_Admin_Common
{
    /**
     * Builds the events management UI for public events
     *
     * @access  public
     * @return  string  XHTML form
     */
    function PublicEvents()
    {
        return $this->EventsCalendar('public');
    }

    /**
     * Builds the events management UI for user events
     *
     * @access  public
     * @return  string  XHTML form
     */
    function UserEvents()
    {
        return $this->EventsCalendar('user');
    }

    /**
     * Builds the events management UI
     *
     * @access  public
     * @param   string  $mode   Events type [public | user]
     * @return  string  HTML UI
     */
    function EventsCalendar($mode = 'public')
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Events.html');
        $tpl->SetBlock('ec');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar(($mode == 'public')? 'PublicEvents' : 'UserEvents'));

        // Event Form
        $tpl->SetVariable('form', $this->EventForm());

        // Filters
        $tpl->SetVariable('filters', $this->EventFilters());

        // Constants
        $const = array();
        $const['mode'] = $mode;
        $const['script'] = BASE_SCRIPT;
        $const['user'] = (int)$this->app->session->user->id;
        $const['calendar'] = $this->gadget->registry->fetch('calendar', 'Settings');
        $const['eventsLimit'] = $this->gadget->registry->fetch('events_limit');
        $const['summary'] = $this::t('EVENT_SUMMARY');
        $const['location'] = $this::t('EVENT_LOCATION');
        $const['verbose'] = $this::t('EVENT_DESC');
        $const['type'] = $this::t('EVENT_TYPE');
        $const['priority'] = $this::t('EVENT_PRIORITY');
        $const['shared'] = $this::t('SHARED');
        $const['date'] = $this::t('DATE');
        $const['time'] = $this::t('TIME');
        $const['from'] = $this::t('FROM');
        $const['to'] = $this::t('TO');
        $const['newEvent'] = $this::t('NEW_EVENT');
        $const['viewEvent'] = $this::t('VIEW_EVENT');
        $const['editEvent'] = $this::t('EDIT_EVENT');
        $const['yes'] = Jaws::t('YESS');
        $const['no'] = Jaws::t('NOO');
        $const['edit'] = Jaws::t('EDIT');
        $const['delete'] = Jaws::t('DELETE');
        $const['confirmDelete'] = Jaws::t('CONFIRM_DELETE');
        $const['incompleteFields'] = Jaws::t('ERROR_INCOMPLETE_FIELDS');
        $const['types'] = array();
        for ($i = 1; $i <= 5; $i++) {
            $const['types'][$i] = $this::t('EVENT_TYPE_' . $i);
        }
        $const['priorities'] = array();
        for ($i = 0; $i <= 2; $i++) {
            $const['priorities'][$i] = $this::t('EVENT_PRIORITY_' . $i);
        }
        $this->gadget->export('CONST', $const);

        if ($mode == 'public') {
            $tpl->SetBlock('ec/addBtn');
            $tpl->SetVariable('lbl_add', Jaws::t('ADD'));
            $tpl->ParseBlock('ec/addBtn');
        }

        $tpl->SetVariable('lbl_summary', $this::t('EVENT_SUMMARY'));
        $tpl->SetVariable('lbl_location', $this::t('EVENT_LOCATION'));
        $tpl->SetVariable('lbl_link', Jaws::t('URL'));
        $tpl->SetVariable('lbl_desc', $this::t('EVENT_DESC'));
        $tpl->SetVariable('lbl_to', $this::t('TO'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_addEvent', $this::t('NEW_EVENT'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

        $tpl->ParseBlock('ec');
        return $tpl->Get();
    }

    /**
     * Generate filters for events
     *
     * @access  public
     * @return  string XHTML form
     */
    function EventFilters()
    {
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $tpl = $this->gadget->template->loadAdmin('Events.html');
        $tpl->SetBlock('filters');

        // summary
        $summary =& Piwi::CreateWidget('Entry', 'filter_summary');
        $summary->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_summary', $summary->Get());
        $tpl->SetVariable('lbl_filter_summary', $this::t('EVENT_SUMMARY'));

        // Location
        $location =& Piwi::CreateWidget('Entry', 'filter_location');
        $location->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_location', $location->Get());
        $tpl->SetVariable('lbl_filter_location', $this::t('EVENT_LOCATION'));

        // verbose
        $verbose =& Piwi::CreateWidget('Entry', 'filter_verbose');
        $verbose->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_verbose', $verbose->Get());
        $tpl->SetVariable('lbl_filter_verbose', $this::t('EVENT_DESC'));

        // Shared
        $sharedCombo =& Piwi::CreateWidget('Combo', 'filter_shared');
        $sharedCombo->AddOption(Jaws::t('ALL'), -1, false);
        $sharedCombo->AddOption(Jaws::t('YESS'), 1);
        $sharedCombo->AddOption(Jaws::t('NOO'), 0);
        $sharedCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $sharedCombo->SetDefault(-1);
        $tpl->SetVariable('filter_shared', $sharedCombo->Get());
        $tpl->SetVariable('lbl_filter_shared', $this::t('SHARED'));

        // Type
        $typeCombo =& Piwi::CreateWidget('Combo', 'filter_type');
        $typeCombo->AddOption(Jaws::t('ALL'), 0, false);
        for ($i = 1; $i <= 5; $i++) {
            $typeCombo->AddOption($this::t('EVENT_TYPE_' . $i), $i, false);
        }
        $typeCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $typeCombo->SetDefault(0);
        $tpl->SetVariable('filter_type', $typeCombo->Get());
        $tpl->SetVariable('lbl_filter_type', $this::t('EVENT_TYPE'));

        // Priority
        $priorityCombo =& Piwi::CreateWidget('Combo', 'filter_priority');
        for ($i = 0; $i <= 2; $i++) {
            $priorityCombo->AddOption($this::t('EVENT_PRIORITY_' . $i), $i, false);
        }
        $priorityCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $priorityCombo->SetDefault(0);
        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
        $tpl->SetVariable('lbl_filter_priority', $this::t('EVENT_PRIORITY'));

        // Date
        $tpl->SetVariable('lbl_filter_date', $this::t('DATE'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'filter_start_date', '');
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d');
        $fromDate->showTimePicker(false);
        $fromDate->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_start_date', $fromDate->Get());

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'filter_stop_date', '');
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d');
        $toDate->showTimePicker(false);
        $toDate->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_stop_date', $toDate->Get());

        $tpl->ParseBlock('filters');
        return $tpl->Get();
    }

    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string XHTML form
     */
    function EventForm()
    {
        $tpl = $this->gadget->template->loadAdmin('Events.html');
        $tpl->SetBlock('eventForm');

        $tpl->SetVariable('lbl_summary', $this::t('EVENT_SUMMARY'));
        $tpl->SetVariable('lbl_location', $this::t('EVENT_LOCATION'));
        $tpl->SetVariable('lbl_link', Jaws::t('URL'));
        $tpl->SetVariable('lbl_desc', $this::t('EVENT_DESC'));
        $tpl->SetVariable('lbl_to', $this::t('TO'));

        // Start/Stop date
        $tpl->SetVariable('lbl_date', $this::t('DATE'));
        $tpl->SetVariable('lbl_from', $this::t('FROM'));
        $tpl->SetVariable('lbl_to', $this::t('TO'));

        // Start/Stop time
        $tpl->SetVariable('lbl_time', $this::t('TIME'));

        // Type
        $tpl->SetVariable('lbl_type', $this::t('EVENT_TYPE'));
        for ($i = 1; $i <= 5; $i++) {
            $tpl->SetBlock('eventForm/type');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', $this::t('EVENT_TYPE_' . $i));
            $tpl->ParseBlock('eventForm/type');
        }

        // Public
        $tpl->SetVariable('lbl_public', $this::t('EVENT_PUBLIC'));
        $tpl->SetBlock('eventForm/public');
        $tpl->SetVariable('value', 0);
        $tpl->SetVariable('title', Jaws::t('NOO'));
        $tpl->ParseBlock('eventForm/public');
        $tpl->SetBlock('eventForm/public');
        $tpl->SetVariable('value', 1);
        $tpl->SetVariable('title', Jaws::t('YESS'));
        $tpl->ParseBlock('eventForm/public');

        // Priority
        $tpl->SetVariable('lbl_priority', $this::t('EVENT_PRIORITY'));
        for ($i = 0; $i <= 2; $i++) {
            $tpl->SetBlock('eventForm/priority');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', $this::t('EVENT_PRIORITY_' . $i));
            $tpl->ParseBlock('eventForm/priority');
        }

        // Reminder
        $tpl->SetVariable('lbl_reminder', $this::t('EVENT_REMINDER'));
        $intervals = array(0, 1, 5, 10, 15, 30, 60, 120, 180, 240, 300,
            360, 420, 480, 540, 600, 660, 720, 1440, 2880, 10080, 43200);
        foreach ($intervals as $i) {
            $tpl->SetBlock('eventForm/reminder');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', $this::t('EVENT_REMINDER_' . $i));
            $tpl->ParseBlock('eventForm/reminder');
        }

        // Recurrence
        $tpl->SetVariable('lbl_recurrence', $this::t('EVENT_RECURRENCE'));
        for ($i = 0; $i <= 4; $i++) {
            $tpl->SetBlock('eventForm/recurrence');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', $this::t('EVENT_RECURRENCE_' . $i));
            $tpl->ParseBlock('eventForm/recurrence');
        }

        // Day
        $combo =& Piwi::createWidget('Combo', 'day');
        $combo->SetId('');
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', $this::t('DAY'));

        // Week Day
        $jDate = Jaws_Date::getInstance();
        $combo =& Piwi::createWidget('Combo', 'wday');
        $combo->SetId('');
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jDate->DayString($i-1), $i);
        }
        $combo->setDefault(1);
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', $this::t('WEEK_DAY'));

        // Month
        $combo =& Piwi::createWidget('Combo', 'month');
        $combo->SetId('');
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jDate->MonthString($i), $i);
        }
        $combo->setDefault(1);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', $this::t('MONTH'));

        $tpl->ParseBlock('eventForm');
        return $tpl->Get();
    }

    /**
     * Fetches and prepares the events for data grid
     *
     * @access  public
     * @return  array   Events
     */
    function GetEvents()
    {
        $post = $this->gadget->request->fetch(
            array('user', 'pageIndex', 'pageSize', 'sortDirection', 'sortBy', 'search:array'), 'post');

        // Fetch events
        $model = $this->gadget->model->loadAdmin('Events');

        $params = array();
        $params['user'] = $post['user'];
        $params['search'] = $post['search'];
        $params['limit'] = $post['pageSize'];
        $params['offset'] = $post['pageIndex'];
        if (!empty($post['sortBy'])) {
            $params['sort'] = array(array('field' => $post['sortBy'], 'direction' => $post['sortDirection']));
        }
        $events = $model->GetEvents($params);
        $eventsCount = $model->GetEvents($params, true);
        if (Jaws_Error::IsError($events)) {
            return $this->gadget->session->response($this::t('ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        // prepare data
        $jDate = Jaws_Date::getInstance();
        foreach ($events as &$event) {
            $event['shared'] = $event['shared']? $this::t('SHARED') : '';
            $event['start_time'] = $jDate->Format($event['start_time'], 'yyyy/MM/dd HH:mm');
            $event['stop_time'] = $jDate->Format($event['stop_time'], 'yyyy/MM/dd HH:mm');
        }

        return array(
            'status' => 'success',
            'total' =>  $eventsCount,
            'records' => $events
        );
    }

    /**
     * Fetches the event
     *
     * @access  public
     * @return  array   Event data
     */
    function GetEvent()
    {
        $id = $this->gadget->request->fetch('id:int', 'post');
        $model = $this->gadget->model->load('Event');
        $event = $model->GetEvent($id);
        if (Jaws_Error::IsError($event) || empty($event)) {
            return $this->gadget->session->response($this::t('ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        $jDate = Jaws_Date::getInstance();
        $event['start_date'] = $jDate->Format($event['start_time'], 'yyyy-MM-dd');
        $event['start_time'] = $jDate->Format($event['start_time'], 'HH:mm');
        $event['stop_date']  = $jDate->Format($event['stop_time'], 'yyyy-MM-dd');
        $event['stop_time']  = $jDate->Format($event['stop_time'], 'HH:mm');

        $event['reminder']  = $event['reminder'] / 60;

        return $event;
    }
}