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
class EventsCalendar_Actions_Create extends Jaws_Gadget_HTML
{
    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function NewEvent()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Form.html');
        $tpl->SetBlock('form');

        // Response
        $event = array();
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('resp_text', $response['text']);
            $tpl->SetVariable('resp_type', $response['type']);
            $data = $response['data'];
            $tpl->SetVariable('subject', $data['subject']);
            $tpl->SetVariable('location', $data['location']);
            $tpl->SetVariable('description', $data['description']);
            $start_date = $data['start_date'];
            $stop_date = $data['stop_date'];
            $start_time = $data['start_time'];
            $stop_time = $data['stop_time'];
            $type = $data['type'];
            $priority = $data['priority'];
            $reminder = $data['reminder'];
            $repeat = $data['repeat'];
        } else {
            $start_date = '';
            $stop_date = '';
            $start_time = '';
            $stop_time = '';
            $type = 0;
            $priority = 0;
            $reminder = 0;
            $repeat = 0;
        }

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_NEW_EVENT'));
        $tpl->SetVariable('errorIncompleteData', _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'));
        $tpl->SetVariable('action', 'newevent');
        $tpl->SetVariable('form_action', 'CreateEvent');
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));
        $tpl->SetVariable('lbl_to', _t('EVENTSCALENDAR_TO'));

        // Start date
        $cal_type = $this->gadget->registry->fetch('calendar_type', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('calendar_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'start_date', $start_date);
        $datePicker->SetId('event_start_date');
        $datePicker->showTimePicker(true);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $tpl->SetVariable('start_date', $datePicker->Get());
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));

        // Stop date
        $datePicker =& Piwi::CreateWidget('DatePicker', 'stop_date', $stop_date);
        $datePicker->SetId('event_stop_date');
        $datePicker->showTimePicker(true);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->SetIncludeCSS(false);
        $datePicker->SetIncludeJS(false);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $tpl->SetVariable('stop_date', $datePicker->Get());

        // Start time
        $combo =& Piwi::CreateWidget('Combo', 'start_time');
        $combo->SetId('event_start_time');
        for ($i = 0; $i <= 23; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($type);
        $tpl->SetVariable('start_time', $combo->Get());
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_LENGTH'));

        // Stop time
        $combo =& Piwi::CreateWidget('Combo', 'stop_time');
        $combo->SetId('event_stop_time');
        for ($i = 0; $i <= 23; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($type);
        $tpl->SetVariable('stop_time', $combo->Get());

        // Type
        $combo =& Piwi::CreateWidget('Combo', 'type');
        $combo->SetId('event_type');
        for ($i = 1; $i <= 5; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_' . $i), $i);
        }
        $combo->SetDefault($type);
        $tpl->SetVariable('type', $combo->Get());
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        // Priority
        $combo =& Piwi::CreateWidget('Combo', 'priority');
        $combo->SetId('event_priority');
        for ($i = 0; $i <= 2; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i), $i);
        }
        $combo->SetDefault($priority);
        $tpl->SetVariable('priority', $combo->Get());
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));

        // Reminder (in minutes)
        $combo =& Piwi::CreateWidget('Combo', 'reminder');
        $combo->SetId('event_reminder');
        $intervals = array(0, 1, 5, 10, 15, 30, 60, 120, 180, 240, 300, 
            360, 420, 480, 540, 600, 660, 720, 1440, 2880, 10080, 43200);
        foreach ($intervals as $i) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REMINDER_' . $i), $i);
        }
        $combo->SetDefault($reminder);
        $tpl->SetVariable('reminder', $combo->Get());
        $tpl->SetVariable('lbl_reminder', _t('EVENTSCALENDAR_EVENT_REMINDER'));

        // Repeat
        $tpl->SetVariable('lbl_repeat', _t('EVENTSCALENDAR_EVENT_REPEAT'));

        $jdate = $GLOBALS['app']->loadDate();

        // Month
        $combo =& Piwi::CreateWidget('Combo', 'month');
        $combo->SetId('event_month');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_MONTH'), -1);
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jdate->MonthString($i), $i);
        }
        $combo->SetDefault(-1);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', _t('EVENTSCALENDAR_MONTH'));

        // Day
        $combo =& Piwi::CreateWidget('Combo', 'month_day');
        $combo->SetId('event_day');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_DAY'), -1);
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault(-1);
        $tpl->SetVariable('month_day', $combo->Get());
        $tpl->SetVariable('lbl_month_day', _t('EVENTSCALENDAR_DAY'));

        // Week Day
        $combo =& Piwi::CreateWidget('Combo', 'week_day');
        $combo->SetId('event_wday');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_WEEK_DAY'), -1);
        for ($i = 0; $i <= 6; $i++) {
            $combo->AddOption($jdate->DayString($i), $i);
        }
        $combo->SetDefault(-1);
        $tpl->SetVariable('week_day', $combo->Get());
        $tpl->SetVariable('lbl_week_day', _t('EVENTSCALENDAR_WEEK_DAY'));

        // Hour
        $combo =& Piwi::CreateWidget('Combo', 'hour');
        $combo->SetId('event_hour');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_HOUR'), -1);
        for ($i = 0; $i <= 23; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault(12);
        $tpl->SetVariable('hour', $combo->Get());
        $tpl->SetVariable('lbl_hour', _t('EVENTSCALENDAR_HOUR'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $GLOBALS['app']->GetSiteURL('/') . $this->gadget->urlMap('Events'));

        $tpl->ParseBlock('form');
        return $tpl->Get();
    }

    /**
     * Creates a new event
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateEvent()
    {
        $data = jaws()->request->fetch(array('subject', 'location',
            'description', 'type', 'priority', 'reminder',
            'start_date', 'stop_date', 'start_time', 'stop_time',
            'month', 'month_day', 'week_day', 'hour'), 'post');
        if (empty($data['subject']) || empty($data['start_date'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'),
                'Events.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $jdate = $GLOBALS['app']->loadDate();
        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $data['subject'] = Jaws_XSS::defilter($data['subject']);
        $data['location'] = Jaws_XSS::defilter($data['location']);
        $data['description'] = Jaws_XSS::defilter($data['description']);

        $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Events');
        $result = $model->Insert($data);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_CREATE'),
                'Events.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('EVENTSCALENDAR_NOTICE_EVENT_CREATED'),
            'Events.Response'
        );
        Jaws_Header::Location($this->gadget->urlMap('ManageEvents'));
    }
}