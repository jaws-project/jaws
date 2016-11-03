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
//        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Mailbox/Resources/font-awesome/css/font-awesome.min.css');
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddHeadLink('libraries/w2ui/w2ui.css');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/w2ui/w2ui.js');
        $tpl = $this->gadget->template->loadAdmin('Events.html');
        $tpl->SetBlock('ec');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar(($mode == 'public')? 'PublicEvents' : 'UserEvents'));

        // Event Form
        $tpl->SetVariable('form', $this->EventForm());

        // Constants
        $const = array();
        $const['script'] = BASE_SCRIPT;
        $const['mode'] = $mode;
        $const['subject'] = _t('EVENTSCALENDAR_EVENT_SUBJECT');
        $const['date'] = _t('EVENTSCALENDAR_DATE');
        $const['time'] = _t('EVENTSCALENDAR_TIME');
        $const['shared'] = _t('EVENTSCALENDAR_SHARED');
        $tpl->SetVariable('CONST', json_encode($const));

        $tpl->ParseBlock('ec');
        return $tpl->Get();
    }

    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function EventForm($id = null)
    {
        $tpl = $this->gadget->template->loadAdmin('EventForm.html');
        $tpl->SetBlock('form');

        $jDate = Jaws_Date::getInstance();
//        $tpl->SetVariable('id', $event['id']);
//        $tpl->SetVariable('subject', $event['subject']);
//        $tpl->SetVariable('location', $event['location']);
//        $tpl->SetVariable('description', $event['description']);

        if (empty($id)) {
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_NEW_EVENT'));
            $tpl->SetVariable('action', 'newevent');
            $tpl->SetVariable('form_action', 'CreateEvent');
        } else {
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_EDIT_EVENT'));
            $tpl->SetVariable('action', 'editevent');
            $tpl->SetVariable('form_action', 'UpdateEvent');
        }
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));
        $tpl->SetVariable('errorIncompleteData', _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'));

        // description
        $text =& $GLOBALS['app']->LoadEditor('EventsCalendar', 'description');
        $text->setId('');
        $text->setClass('form-control');
        $text->SetWidth('100%');
        $text->TextArea->SetStyle('width:100%;');
        $text->TextArea->SetRows(15);
        $tpl->SetVariable('description', $text->Get());

        // Start date
//        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
//        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
//        $datePicker =& Piwi::createWidget('DatePicker', 'start_date');
//        $datePicker->SetId('');
//        $datePicker->showTimePicker(true);
//        $datePicker->setCalType($cal_type);
//        $datePicker->setLanguageCode($cal_lang);
//        $datePicker->setDateFormat('%Y-%m-%d');
//        $datePicker->SetIncludeCSS(false);
//        $datePicker->SetIncludeJS(false);
//        $tpl->SetVariable('start_date', $datePicker->Get());
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));
        $tpl->SetVariable('lbl_from', _t('EVENTSCALENDAR_FROM'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));

        // Stop date
//        $datePicker =& Piwi::createWidget('DatePicker', 'stop_date');
//        $datePicker->SetId('');
//        $datePicker->showTimePicker(true);
//        $datePicker->setDateFormat('%Y-%m-%d');
//        $datePicker->SetIncludeCSS(false);
//        $datePicker->SetIncludeJS(false);
//        $datePicker->setCalType($cal_type);
//        $datePicker->setLanguageCode($cal_lang);
//        $tpl->SetVariable('stop_date', $datePicker->Get());

        // Start/Stop time
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_TIME'));
        $tpl->SetVariable('error_time_value', _t('EVENTSCALENDAR_ERROR_INVALID_TIME_FORMAT'));
//        $tpl->SetVariable('start_time', $event['start_time']);
//        $tpl->SetVariable('stop_time', $event['stop_time']);

        // Type
        $combo =& Piwi::createWidget('Combo', 'type');
        $combo->SetId('');
        for ($i = 1; $i <= 5; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_' . $i), $i);
        }
        $tpl->SetVariable('type', $combo->Get());
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        // Priority
        $combo =& Piwi::createWidget('Combo', 'priority');
        $combo->SetId('');
        for ($i = 0; $i <= 2; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i), $i);
        }
        $tpl->SetVariable('priority', $combo->Get());
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));

        // Reminder (in minutes)
        $combo =& Piwi::createWidget('Combo', 'reminder');
        $combo->SetId('');
        $intervals = array(0, 1, 5, 10, 15, 30, 60, 120, 180, 240, 300,
            360, 420, 480, 540, 600, 660, 720, 1440, 2880, 10080, 43200);
        foreach ($intervals as $i) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REMINDER_' . $i), $i);
        }
        $tpl->SetVariable('reminder', $combo->Get());
        $tpl->SetVariable('lbl_reminder', _t('EVENTSCALENDAR_EVENT_REMINDER'));

        // Recurrence
        $combo =& Piwi::createWidget('Combo', 'recurrence');
        $combo->SetId('');
        for ($i = 0; $i <= 4; $i++) {
            $combo->AddOption(_t("EVENTSCALENDAR_EVENT_RECURRENCE_$i"), $i);
        }
        $combo->AddEvent(ON_CHANGE, 'switchRepeatUI(this.value)');
        $tpl->SetVariable('recurrence', $combo->Get());
//        $tpl->SetVariable('recurrence_value', $event['recurrence']);
        $tpl->SetVariable('lbl_recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE'));

        // Day
        $combo =& Piwi::createWidget('Combo', 'day');
        $combo->SetId('');
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));

        // Week Day
        $combo =& Piwi::createWidget('Combo', 'wday');
        $combo->SetId('');
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jDate->DayString($i-1), $i);
        }
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', _t('EVENTSCALENDAR_WEEK_DAY'));

        // Month
        $combo =& Piwi::createWidget('Combo', 'month');
        $combo->SetId('');
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jDate->MonthString($i), $i);
        }
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', _t('EVENTSCALENDAR_MONTH'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $GLOBALS['app']->GetSiteURL('/') .
            $this->gadget->urlMap('ManageEvents'));

        $tpl->ParseBlock('form');
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
        $post = $this->gadget->request->fetchAll('post');
//        _log_var_dump($post);
        $post['user'] = 0;
//        $page = !empty($page)? (int)$page : 1;
//        $limit = (int)$this->gadget->registry->fetch('events_limit');

        // Fetch events
        $model = $this->gadget->model->load('Events');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
//        $count = $model->GetNumberOfEvents($user, $query, $shared, $foreign, $start, $stop);
//        $events = $model->GetEvents($user, $query, $shared, $foreign,
//            $start, $stop, $limit, ($page - 1) * $limit);
        $count = $model->GetNumberOfEvents($post['user']);
        $events = $model->GetEvents($post['user']);
//        _log_var_dump($events);
        if (Jaws_Error::IsError($events)){
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        $jDate = Jaws_Date::getInstance();
        foreach ($events as &$event) {
            $start_date = $jDate->Format($event['start_time'], 'Y/m/d');
            $stop_date = $jDate->Format($event['stop_time'], 'Y/m/d');
            $event['date'] = ($event['start_time'] == $event['stop_time'])?
                $start_date : $start_date . _t('EVENTSCALENDAR_TO') . $stop_date;

            $start_time = $jDate->Format($event['start_time'], 'H:i');
            $event['time'] = ($event['start_time'] == $event['stop_time'])?
                $start_time : $start_time . _t('EVENTSCALENDAR_TO') .
                $jDate->Format($event['stop_time'], 'H:i');

            $event['shared'] = $event['shared']? _t('EVENTSCALENDAR_SHARED') : '';

//            $url = $this->gadget->urlMap('ViewEvent', array('id' => $event['id']));
        }
        return $events;
    }

    /**
     * Displays an event
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function GetEvent()
    {
        $id = $this->gadget->request->fetch('event_id:int', 'post');
        $model = $this->gadget->model->load('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event) || empty($event)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

//        $jDate = Jaws_Date::getInstance();
//        $event['start_date'] = $jDate->Format($event['start_date'], 'H:i');
//        $event['stop_date'] = $jDate->Format($event['stop_date'], 'H:i');
//        $event['start_time'] = $jDate->Format($event['start_time'], 'Y-m-d');
//        $event['stop_time'] = $jDate->Format($event['stop_time'], 'Y-m-d');

        return $event;
    }
}