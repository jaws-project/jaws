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
class EventsCalendar_Actions_Event extends Jaws_Gadget_Action
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
        $id = (int)jaws()->request->fetch('id', 'get');
        return $this->EventForm($id);
    }

    /**
     * Builds form for creating a new event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function EventForm($id = null)
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Form.html');
        $tpl->SetBlock('form');

        // Response
        $response = $GLOBALS['app']->Session->PopResponse('Events.Response');
        if ($response) {
            $tpl->SetVariable('resp_text', $response['text']);
            $tpl->SetVariable('resp_type', $response['type']);
            $event = $response['data'];
            if (!isset($event['id'])) {
                $event['id'] = 0;
            }
        }

        $jdate = $GLOBALS['app']->loadDate();
        if (!isset($event) || empty($event)) {
            if (!empty($id)) {
                $model = $this->gadget->loadModel('Event');
                $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
                $event = $model->GetEvent($id, $user);
                if (Jaws_Error::IsError($event) ||
                    empty($event) ||
                    $event['user'] != $user)
                {
                    return;
                }
                $event['start_date'] = empty($event['start_date'])? '' :
                    $jdate->Format($event['start_date'], 'Y-m-d');
                $event['stop_date'] = empty($event['stop_date'])? '' :
                    $jdate->Format($event['stop_date'], 'Y-m-d');
                $event['start_time'] = round($event['start_time'] / 3600);
                $event['stop_time'] = round($event['stop_time'] / 3600);
            } else {
                $event = array();
                $event['id'] = 0;
                $event['subject'] = '';
                $event['location'] = '';
                $event['description'] = '';
                $event['start_date'] = '';
                $event['stop_date'] = '';
                $event['start_time'] = 0;
                $event['stop_time'] = 0;
                $event['month'] = 0;
                $event['day'] = 0;
                $event['wday'] = 0;
                $event['type'] = 1;
                $event['priority'] = 0;
                $event['reminder'] = 0;
            }
        }
        $tpl->SetVariable('id', $event['id']);
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
        $cal_type = $this->gadget->registry->fetch('calendar_type', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('calendar_language', 'Settings');
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

        // Start time
        $combo =& Piwi::CreateWidget('Combo', 'start_time');
        $combo->SetId('event_start_time');
        for ($i = 0; $i <= 23; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($event['start_time']);
        $tpl->SetVariable('start_time', $combo->Get());
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_LENGTH'));

        // Stop time
        $combo =& Piwi::CreateWidget('Combo', 'stop_time');
        $combo->SetId('event_stop_time');
        for ($i = 0; $i <= 23; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($event['stop_time']);
        $tpl->SetVariable('stop_time', $combo->Get());

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

        // Repeat
        $combo =& Piwi::CreateWidget('Combo', 'repeat');
        $combo->SetId('event_repeat');
        //$combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_NO_REPEAT'), 0);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_DAILY'), 1);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_WEEKLY'), 2);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_MONTHLY'), 3);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_YEARLY'), 4);
        $combo->SetDefault(1);
        $combo->AddEvent(ON_CHANGE, 'switchRepeatUI(this.value)');
        $tpl->SetVariable('repeat', $combo->Get());
        $tpl->SetVariable('lbl_repeat', _t('EVENTSCALENDAR_EVENT_REPEAT'));

        // Day
        $combo =& Piwi::CreateWidget('Combo', 'day');
        $combo->SetId('event_day');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_DAY'), 0);
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($event['day']);
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));

        // Week Day
        $combo =& Piwi::CreateWidget('Combo', 'wday');
        $combo->SetId('event_wday');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_WEEK_DAY'), 0);
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jdate->DayString($i-1), $i);
        }
        $combo->SetDefault($event['wday']);
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', _t('EVENTSCALENDAR_WEEK_DAY'));

        // Month
        $combo =& Piwi::CreateWidget('Combo', 'month');
        $combo->SetId('event_month');
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_EVERY_MONTH'), 0);
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jdate->MonthString($i), $i);
        }
        $combo->SetDefault($event['month']);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', _t('EVENTSCALENDAR_MONTH'));

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
            'month', 'day', 'wday'), 'post');
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

        $model = $this->gadget->loadModel('Event');
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

    /**
     * Updates event
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateEvent()
    {
        $data = jaws()->request->fetch(array('id', 'subject', 'location',
            'description', 'type', 'priority', 'reminder',
            'start_date', 'stop_date', 'start_time', 'stop_time',
            'month', 'day', 'wday'), 'post');
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
        $model = $this->gadget->loadModel('Event');
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
        if ($event['user'] != $user) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                'Events.Response',
                RESPONSE_ERROR
            );
            Jaws_Header::Referrer();
        }

        $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $data['subject'] = Jaws_XSS::defilter($data['subject']);
        $data['location'] = Jaws_XSS::defilter($data['location']);
        $data['description'] = Jaws_XSS::defilter($data['description']);
        $result = $model->Update($id, $data);
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
        Jaws_Header::Location($this->gadget->urlMap('ManageEvents'));
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
        $model = $this->gadget->loadModel('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $verified_nodes = $model->CheckEvents($id_set, $user);
        if (Jaws_Error::IsError($verified_nodes)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // No events was verified
        if (empty($verified_nodes)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        // Delete events
        $model = $this->gadget->loadModel('Event');
        $res = $model->Delete($verified_nodes);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        if (count($id_set) !== count($verified_nodes)) {
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