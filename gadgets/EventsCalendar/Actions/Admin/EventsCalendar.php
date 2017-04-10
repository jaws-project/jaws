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
        $const['shared'] = _t('EVENTSCALENDAR_SHARED');
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
            array('user', 'pageIndex', 'pageSize', 'sortDirection', 'sortBy', 'filterBy', 'searchBy'), 'post');

        // Fetch events
        $model = $this->gadget->model->loadAdmin('Events');

        $params = array();
        $params['user'] = $post['user'];
        $params['search'] = '';
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