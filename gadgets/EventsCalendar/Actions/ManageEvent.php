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
class EventsCalendar_Actions_ManageEvent extends Jaws_Gadget_Action
{
    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function NewEvent()
    {
        return $this->EventForm();
    }

    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function EditEvent()
    {
        $id = (int)jaws()->request->fetch('event', 'get');
        return $this->EventForm($id);
    }

    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @param   int     $id     Event ID
     * @return string XHTML form
     */
    function EventForm($id = 0)
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('EventForm.html');
        $tpl->SetBlock('form');

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('Events'));

        // Response
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('resp_text', $response['text']);
            $tpl->SetVariable('resp_type', $response['type']);
            $event = $response['data'];
        }

        $jDate = Jaws_Date::getInstance();
        if (!isset($event) || empty($event)) {
            if (!empty($id)) {
                $model = $this->gadget->model->load('Event');
                $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
                $event = $model->GetEvent($id, $user);
                if (Jaws_Error::IsError($event) ||
                    empty($event) ||
                    $event['owner'] != $user)
                {
                    return '';
                }
                $start = $event['start_time'];
                $event['start_date'] = $jDate->Format($start, 'Y-m-d');
                $event['start_time'] = $jDate->Format($start, 'H:i');
                $stop = $event['stop_time'];
                $event['stop_date'] = $jDate->Format($stop, 'Y-m-d');
                $event['stop_time'] = $jDate->Format($stop, 'H:i');
                $event['reminder'] /= 60;
            } else {
                $event = array();
                $event['id'] = 0;
                $event['subject'] = '';
                $event['location'] = '';
                $event['description'] = '';
                $event['start_date'] = '';
                $event['stop_date'] = '';
                $event['start_time'] = '';
                $event['stop_time'] = '';
                $event['recurrence'] = 0;
                $event['month'] = 0;
                $event['day'] = 0;
                $event['wday'] = 0;
                $event['public'] = 0;
                $event['type'] = 1;
                $event['priority'] = 0;
                $event['reminder'] = 0;
            }
        }
        $tpl->SetVariable('id', isset($event['id'])? $event['id'] : 0);
        $tpl->SetVariable('subject', $event['subject']);
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('description', $event['description']);

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

        // Start date
        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'start_date', $event['start_date']);
        $datePicker->SetId('event_start_date');
        $datePicker->showTimePicker(true);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $tpl->SetVariable('start_date', $datePicker->Get());
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));

        // Stop date
        $datePicker =& Piwi::CreateWidget('DatePicker', 'stop_date', $event['stop_date']);
        $datePicker->SetId('event_stop_date');
        $datePicker->showTimePicker(true);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->SetIncludeCSS(false);
        $datePicker->SetIncludeJS(false);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $tpl->SetVariable('stop_date', $datePicker->Get());

        // Start/Stop time
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_TIME'));
        $tpl->SetVariable('error_time_value', _t('EVENTSCALENDAR_ERROR_INVALID_TIME_FORMAT'));
        $tpl->SetVariable('start_time', $event['start_time']);
        $tpl->SetVariable('stop_time', $event['stop_time']);

        // Public
        $combo =& Piwi::CreateWidget('Combo', 'public');
        $combo->SetId('event_type');
        $combo->AddOption(_t('GLOBAL_YES'), 1);
        $combo->AddOption(_t('GLOBAL_NO'), 0);
        $combo->SetDefault($event['public']? 1 : 0);
        $tpl->SetVariable('public', $combo->Get());
        $tpl->SetVariable('lbl_public', _t('EVENTSCALENDAR_EVENT_PUBLIC'));

        // Type
        $combo =& Piwi::CreateWidget('Combo', 'type');
        $combo->SetId('event_type');
        for ($i = 1; $i <= 5; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_TYPE_' . $i), $i);
        }
        $combo->SetDefault($event['type']);
        $tpl->SetVariable('type', $combo->Get());
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        // Priority
        $combo =& Piwi::CreateWidget('Combo', 'priority');
        $combo->SetId('event_priority');
        for ($i = 0; $i <= 2; $i++) {
            $combo->AddOption(_t('EVENTSCALENDAR_EVENT_PRIORITY_' . $i), $i);
        }
        $combo->SetDefault($event['priority']);
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
        $combo->SetDefault($event['reminder']);
        $tpl->SetVariable('reminder', $combo->Get());
        $tpl->SetVariable('lbl_reminder', _t('EVENTSCALENDAR_EVENT_REMINDER'));

        // Recurrence
        $combo =& Piwi::CreateWidget('Combo', 'recurrence');
        $combo->SetId('event_recurrence');
        for ($i = 0; $i <= 4; $i++) {
            $combo->AddOption(_t("EVENTSCALENDAR_EVENT_RECURRENCE_$i"), $i);
        }
        $combo->SetDefault($event['recurrence']);
        $combo->AddEvent(ON_CHANGE, 'updateRepeatUI(this.value)');
        $tpl->SetVariable('recurrence', $combo->Get());
        $tpl->SetVariable('recurrence_value', $event['recurrence']);
        $tpl->SetVariable('lbl_recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE'));

        // Day
        $combo =& Piwi::CreateWidget('Combo', 'day');
        $combo->SetId('event_day');
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($event['day']);
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));

        // Week Day
        $combo =& Piwi::CreateWidget('Combo', 'wday');
        $combo->SetId('event_wday');
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jDate->DayString($i-1), $i);
        }
        $combo->SetDefault($event['wday']);
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', _t('EVENTSCALENDAR_WEEK_DAY'));

        // Month
        $combo =& Piwi::CreateWidget('Combo', 'month');
        $combo->SetId('event_month');
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jDate->MonthString($i), $i);
        }
        $combo->SetDefault($event['month']);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', _t('EVENTSCALENDAR_MONTH'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $GLOBALS['app']->GetSiteURL('/') .
            $this->gadget->urlMap('ManageEvents', array('user' => $GLOBALS['app']->Session->GetAttribute('user'))));

        $tpl->ParseBlock('form');
        return $tpl->Get();
    }

    /**
     * Creates a new event
     *
     * @access  public
     * @return  void
     */
    function CreateEvent()
    {
        $event = jaws()->request->fetch(array('subject', 'location',
            'description', 'public', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday',
            'start_date', 'stop_date', 'start_time', 'stop_time'), 'post');
        if (empty($event['subject']) || empty($event['start_date'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'),
                'Events.Response',
                RESPONSE_ERROR,
                $event
            );
            Jaws_Header::Referrer();
        }

        $event['owner'] = $event['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        if (empty($event['stop_date'])) {
            $event['stop_date'] = $event['start_date'];
        }
        if (empty($event['start_time'])) {
            $event['start_time'] = '00:00';
        }
        if (empty($event['stop_time'])) {
            $event['stop_time'] = $event['start_time'];
        }

        $model = $this->gadget->model->load('Event');
        $result = $model->InsertEvent($event);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_CREATE'),
                'Events.Response',
                RESPONSE_ERROR,
                $event
            );
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('EVENTSCALENDAR_NOTICE_EVENT_CREATED'),
            'Events.Response'
        );
        Jaws_Header::Location($this->gadget->urlMap('ManageEvents', array('user' => $event['user'])));
    }

    /**
     * Updates event
     *
     * @access  public
     * @return  void
     */
    function UpdateEvent()
    {
        $data = jaws()->request->fetch(array('id', 'subject', 'location',
            'description', 'public', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday',
            'start_date', 'stop_date', 'start_time', 'stop_time'), 'post');
        if (empty($data['subject']) || empty($data['start_date'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'),
                'Events.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        // Validate event
        $model = $this->gadget->model->load('Event');
        $id = (int)$data['id'];
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_RETRIEVING_DATA'),
                'Events.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        // Verify owner
        if ($event['owner'] != $user) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                'Events.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        if (empty($data['stop_date'])) {
            $data['stop_date'] = $data['start_date'];
        }
        if (empty($data['stop_time'])) {
            $data['stop_time'] = $data['start_time'];
        }

        $result = $model->UpdateEvent($id, $data, $event);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_UPDATE'),
                'Events.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('EVENTSCALENDAR_NOTICE_EVENT_UPDATED'),
            'Events.Response'
        );
        Jaws_Header::Location($this->gadget->urlMap('ManageEvents', array('user' => $data['user'])));
    }

    /**
     * Deletes passed event(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteEvent()
    {
        $id_set = jaws()->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // Verify events & user
        $model = $this->gadget->model->load('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $verifiedEvents = $model->CheckEvents($id_set, $user);
        if (Jaws_Error::IsError($verifiedEvents)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // No events was verified
        if (empty($verifiedEvents)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        // Delete events
        $model = $this->gadget->model->load('Event');
        $res = $model->DeleteEvents($verifiedEvents);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        if (count($id_set) !== count($verifiedEvents)) {
            $msg = _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS_FAILED');
            // FIXME: we are creating response twice
            $GLOBALS['app']->Session->PushResponse($msg, 'Events.Response', RESPONSE_WARNING);
            return $GLOBALS['app']->Session->GetResponse($msg, RESPONSE_WARNING);
        }

        $msg = (count($id_set) === 1)?
            _t('EVENTSCALENDAR_NOTICE_EVENT_DELETED') :
            _t('EVENTSCALENDAR_NOTICE_EVENTS_DELETED');
        $GLOBALS['app']->Session->PushResponse($msg, 'Events.Response');
        return $GLOBALS['app']->Session->GetResponse($msg);
    }
}