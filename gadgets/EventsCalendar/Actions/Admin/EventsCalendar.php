<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
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
        $const['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $const['calendar'] = $this->gadget->registry->fetch('calendar', 'Settings');
        $const['eventsLimit'] = $this->gadget->registry->fetch('events_limit');
        $const['subject'] = _t('EVENTSCALENDAR_EVENT_SUBJECT');
        $const['location'] = _t('EVENTSCALENDAR_EVENT_LOCATION');
        $const['description'] = _t('EVENTSCALENDAR_EVENT_DESC');
        $const['type'] = _t('EVENTSCALENDAR_EVENT_TYPE');
        $const['priority'] = _t('EVENTSCALENDAR_EVENT_PRIORITY');
        $const['shared'] = _t('EVENTSCALENDAR_SHARED');
        $const['date'] = _t('EVENTSCALENDAR_DATE');
        $const['time'] = _t('EVENTSCALENDAR_TIME');
        $const['from'] = _t('EVENTSCALENDAR_FROM');
        $const['to'] = _t('EVENTSCALENDAR_TO');
        $const['newEvent'] = _t('EVENTSCALENDAR_NEW_EVENT');
        $const['viewEvent'] = _t('EVENTSCALENDAR_VIEW_EVENT');
        $const['editEvent'] = _t('EVENTSCALENDAR_EDIT_EVENT');
        $const['yes'] = _t('GLOBAL_YES');
        $const['no'] = _t('GLOBAL_NO');
        $const['edit'] = _t('GLOBAL_EDIT');
        $const['delete'] = _t('GLOBAL_DELETE');
        $const['confirmDelete'] = _t('GLOBAL_CONFIRM_DELETE');
        $const['incompleteFields'] = _t('GLOBAL_ERROR_INCOMPLETE_FIELDS');
        $const['types'] = array();
        for ($i = 1; $i <= 5; $i++) {
            $const['types'][$i] = _t('EVENTSCALENDAR_EVENT_TYPE_' . $i);
        }
        $const['priorities'] = array();
        for ($i = 0; $i <= 2; $i++) {
            $const['priorities'][$i] = _t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i);
        }
        $this->gadget->define('CONST', $const);

        if ($mode == 'public') {
            $tpl->SetBlock('ec/addBtn');
            $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));
            $tpl->ParseBlock('ec/addBtn');
        }

        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_addEvent', _t('EVENTSCALENDAR_NEW_EVENT'));

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));

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
            $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $tpl = $this->gadget->template->loadAdmin('Events.html');
        $tpl->SetBlock('filters');

        // Subject
        $subject =& Piwi::CreateWidget('Entry', 'filter_subject');
        $subject->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_subject', $subject->Get());
        $tpl->SetVariable('lbl_filter_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));

        // Location
        $location =& Piwi::CreateWidget('Entry', 'filter_location');
        $location->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_location', $location->Get());
        $tpl->SetVariable('lbl_filter_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));

        // Description
        $description =& Piwi::CreateWidget('Entry', 'filter_description');
        $description->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $tpl->SetVariable('filter_description', $description->Get());
        $tpl->SetVariable('lbl_filter_description', _t('EVENTSCALENDAR_EVENT_DESC'));

        // Shared
        $sharedCombo =& Piwi::CreateWidget('Combo', 'filter_shared');
        $sharedCombo->AddOption(_t('GLOBAL_ALL'), -1, false);
        $sharedCombo->AddOption(_t('GLOBAL_YES'), 1);
        $sharedCombo->AddOption(_t('GLOBAL_NO'), 0);
        $sharedCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $sharedCombo->SetDefault(-1);
        $tpl->SetVariable('filter_shared', $sharedCombo->Get());
        $tpl->SetVariable('lbl_filter_shared', _t('EVENTSCALENDAR_SHARED'));

        // Type
        $typeCombo =& Piwi::CreateWidget('Combo', 'filter_type');
        $typeCombo->AddOption(_t('GLOBAL_ALL'), 0, false);
        for ($i = 1; $i <= 5; $i++) {
            $typeCombo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_' . $i), $i, false);
        }
        $typeCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $typeCombo->SetDefault(0);
        $tpl->SetVariable('filter_type', $typeCombo->Get());
        $tpl->SetVariable('lbl_filter_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        // Priority
        $priorityCombo =& Piwi::CreateWidget('Combo', 'filter_priority');
        for ($i = 0; $i <= 2; $i++) {
            $priorityCombo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i), $i, false);
        }
        $priorityCombo->AddEvent(ON_CHANGE, "javascript:searchEvents();");
        $priorityCombo->SetDefault(0);
        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
        $tpl->SetVariable('lbl_filter_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));

        // Date
        $tpl->SetVariable('lbl_filter_date', _t('EVENTSCALENDAR_DATE'));

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

        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));

        // Start/Stop date
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));
        $tpl->SetVariable('lbl_from', _t('EVENTSCALENDAR_FROM'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));

        // Start/Stop time
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_TIME'));

        // Type
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));
        for ($i = 1; $i <= 5; $i++) {
            $tpl->SetBlock('eventForm/type');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_EVENT_TYPE_' . $i));
            $tpl->ParseBlock('eventForm/type');
        }

        // Public
        $tpl->SetVariable('lbl_public', _t('EVENTSCALENDAR_EVENT_PUBLIC'));
        $tpl->SetBlock('eventForm/public');
        $tpl->SetVariable('value', 0);
        $tpl->SetVariable('title', _t('GLOBAL_NO'));
        $tpl->ParseBlock('eventForm/public');
        $tpl->SetBlock('eventForm/public');
        $tpl->SetVariable('value', 1);
        $tpl->SetVariable('title', _t('GLOBAL_YES'));
        $tpl->ParseBlock('eventForm/public');

        // Priority
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));
        for ($i = 0; $i <= 2; $i++) {
            $tpl->SetBlock('eventForm/priority');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i));
            $tpl->ParseBlock('eventForm/priority');
        }

        // Reminder
        $tpl->SetVariable('lbl_reminder', _t('EVENTSCALENDAR_EVENT_REMINDER'));
        $intervals = array(0, 1, 5, 10, 15, 30, 60, 120, 180, 240, 300,
            360, 420, 480, 540, 600, 660, 720, 1440, 2880, 10080, 43200);
        foreach ($intervals as $i) {
            $tpl->SetBlock('eventForm/reminder');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_EVENT_REMINDER_' . $i));
            $tpl->ParseBlock('eventForm/reminder');
        }

        // Recurrence
        $tpl->SetVariable('lbl_recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE'));
        for ($i = 0; $i <= 4; $i++) {
            $tpl->SetBlock('eventForm/recurrence');
            $tpl->SetVariable('value', $i);
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_EVENT_RECURRENCE_' . $i));
            $tpl->ParseBlock('eventForm/recurrence');
        }

        // Day
        $combo =& Piwi::createWidget('Combo', 'day');
        $combo->SetId('');
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));

        // Week Day
        $jDate = Jaws_Date::getInstance();
        $combo =& Piwi::createWidget('Combo', 'wday');
        $combo->SetId('');
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jDate->DayString($i-1), $i);
        }
        $combo->setDefault(1);
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', _t('EVENTSCALENDAR_WEEK_DAY'));

        // Month
        $combo =& Piwi::createWidget('Combo', 'month');
        $combo->SetId('');
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jDate->MonthString($i), $i);
        }
        $combo->setDefault(1);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', _t('EVENTSCALENDAR_MONTH'));

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
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        // prepare data
        $jDate = Jaws_Date::getInstance();
        foreach ($events as &$event) {
            $event['shared'] = $event['shared']? _t('EVENTSCALENDAR_SHARED') : '';
            $event['start_time'] = $jDate->Format($event['start_time'], 'Y/m/d H:i');
            $event['stop_time'] = $jDate->Format($event['stop_time'], 'Y/m/d H:i');
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
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        $jDate = Jaws_Date::getInstance();
        $event['start_date'] = $jDate->Format($event['start_time'], 'Y-m-d');
        $event['start_time'] = $jDate->Format($event['start_time'], 'H:i');
        $event['stop_date']  = $jDate->Format($event['stop_time'], 'Y-m-d');
        $event['stop_time']  = $jDate->Format($event['stop_time'], 'H:i');

        $event['reminder']  = $event['reminder'] / 60;

        return $event;
    }
}