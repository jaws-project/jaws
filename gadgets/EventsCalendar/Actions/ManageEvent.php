<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2022 Jaws Development Group
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
        $id = (int)$this->gadget->request->fetch('event', 'get');
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
        $this->app->layout->addLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('EventForm.html');
        $tpl->SetBlock('form');

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // Response
        $response = $this->gadget->session->pop('Event');
        if ($response) {
            $tpl->SetVariable('response_text', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
            $event = $response['data'];
        }

        $jDate = Jaws_Date::getInstance();
        if (!isset($event) || empty($event)) {
            if (!empty($id)) {
                $model = $this->gadget->model->load('Event');
                $user = (int)$this->app->session->user->id;
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
                $event['summary'] = '';
                $event['location'] = '';
                $event['link'] = '';
                $event['verbose'] = '';
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
                $event['symbol'] = '';
            }
        }
        $tpl->SetVariable('id', isset($event['id'])? $event['id'] : 0);
        $tpl->SetVariable('summary', $event['summary']);
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('link', $event['link']);
        $tpl->SetVariable('verbose', $event['verbose']);

        if (empty($id)) {
            $tpl->SetVariable('title', $this::t('NEW_EVENT'));
            $tpl->SetVariable('action', 'newevent');
            $tpl->SetVariable('form_action', 'CreateEvent');
        } else {
            $tpl->SetVariable('title', $this::t('EDIT_EVENT'));
            $tpl->SetVariable('action', 'editevent');
            $tpl->SetVariable('form_action', 'UpdateEvent');
        }
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_summary', $this::t('EVENT_SUMMARY'));
        $tpl->SetVariable('lbl_location', $this::t('EVENT_LOCATION'));
        $tpl->SetVariable('lbl_link', Jaws::t('URL'));
        $tpl->SetVariable('lbl_desc', $this::t('EVENT_DESC'));
        $tpl->SetVariable('lbl_to', $this::t('TO'));
        $tpl->SetVariable('errorIncompleteData', $this::t('ERROR_INCOMPLETE_DATA'));

        // Start date
        $tpl->SetVariable('lbl_date', $this::t('DATE'));
        $tpl->SetBlock('form/start_time');
        $this->gadget->action->load('DatePicker')->calendar(
            $tpl,
            array('name' => 'start_date', 'value'=>$event['start_date'])
        );
        $tpl->ParseBlock('form/start_time');

        // Stop date
        $tpl->SetBlock('form/stop_time');
        $this->gadget->action->load('DatePicker')->calendar(
            $tpl,
            array('name' => 'stop_date', 'value'=>$event['stop_date'])
        );
        $tpl->ParseBlock('form/stop_time');

        // Start/Stop time
        $tpl->SetVariable('lbl_time', $this::t('TIME'));
        $tpl->SetVariable('error_time_value', $this::t('ERROR_INVALID_TIME_FORMAT'));
        $tpl->SetVariable('start_time', $event['start_time']);
        $tpl->SetVariable('stop_time', $event['stop_time']);

        // Public
        $combo =& Piwi::CreateWidget('Combo', 'public');
        $combo->SetId('event_type');
        $combo->AddOption(Jaws::t('YESS'), 1);
        $combo->AddOption(Jaws::t('NOO'), 0);
        $combo->SetDefault($event['public']? 1 : 0);
        $tpl->SetVariable('public', $combo->Get());
        $tpl->SetVariable('lbl_public', $this::t('EVENT_PUBLIC'));

        // Type
        $combo =& Piwi::CreateWidget('Combo', 'type');
        $combo->SetId('event_type');
        for ($i = 1; $i <= 5; $i++) {
            $combo->AddOption($this::t('EVENT_TYPE_' . $i), $i);
        }
        $combo->SetDefault($event['type']);
        $tpl->SetVariable('type', $combo->Get());
        $tpl->SetVariable('lbl_type', $this::t('EVENT_TYPE'));

        // Priority
        $combo =& Piwi::CreateWidget('Combo', 'priority');
        $combo->SetId('event_priority');
        for ($i = 0; $i <= 2; $i++) {
            $combo->AddOption($this::t('EVENT_PRIORITY_' . $i), $i);
        }
        $combo->SetDefault($event['priority']);
        $tpl->SetVariable('priority', $combo->Get());
        $tpl->SetVariable('lbl_priority', $this::t('EVENT_PRIORITY'));

        // Reminder (in minutes)
        $combo =& Piwi::CreateWidget('Combo', 'reminder');
        $combo->SetId('event_reminder');
        $intervals = array(0, 1, 5, 10, 15, 30, 60, 120, 180, 240, 300,
            360, 420, 480, 540, 600, 660, 720, 1440, 2880, 10080, 43200);
        foreach ($intervals as $i) {
            $combo->AddOption($this::t('EVENT_REMINDER_' . $i), $i);
        }
        $combo->SetDefault($event['reminder']);
        $tpl->SetVariable('reminder', $combo->Get());
        $tpl->SetVariable('lbl_reminder', $this::t('EVENT_REMINDER'));

        // Recurrence
        $combo =& Piwi::CreateWidget('Combo', 'recurrence');
        $combo->SetId('event_recurrence');
        for ($i = 0; $i <= 4; $i++) {
            $combo->AddOption($this::t("EVENT_RECURRENCE_$i"), $i);
        }
        $combo->SetDefault($event['recurrence']);
        $combo->AddEvent(ON_CHANGE, 'updateRepeatUI(this.value)');
        $tpl->SetVariable('recurrence', $combo->Get());
        $tpl->SetVariable('recurrence_value', $event['recurrence']);
        $tpl->SetVariable('lbl_recurrence', $this::t('EVENT_RECURRENCE'));

        // Day
        $combo =& Piwi::CreateWidget('Combo', 'day');
        $combo->SetId('event_day');
        for ($i = 1; $i <= 31; $i++) {
            $combo->AddOption($i, $i);
        }
        $combo->SetDefault($event['day']);
        $tpl->SetVariable('day', $combo->Get());
        $tpl->SetVariable('lbl_day', $this::t('DAY'));

        // Week Day
        $combo =& Piwi::CreateWidget('Combo', 'wday');
        $combo->SetId('event_wday');
        for ($i = 1; $i <= 7; $i++) {
            $combo->AddOption($jDate->DayString($i-1), $i);
        }
        $combo->SetDefault($event['wday']);
        $tpl->SetVariable('wday', $combo->Get());
        $tpl->SetVariable('lbl_wday', $this::t('WEEK_DAY'));

        // Month
        $combo =& Piwi::CreateWidget('Combo', 'month');
        $combo->SetId('event_month');
        for ($i = 1; $i <= 12; $i++) {
            $combo->AddOption($jDate->MonthString($i), $i);
        }
        $combo->SetDefault($event['month']);
        $tpl->SetVariable('month', $combo->Get());
        $tpl->SetVariable('lbl_month', $this::t('MONTH'));

        // Symbol
        $tpl->SetVariable('lbl_symbol', $this::t('SYMBOL'));
        $tpl->SetVariable('symbol', $event['symbol']);

        // Actions
        $tpl->SetVariable('lbl_ok', Jaws::t('OK'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('url_back', $this->app->getSiteURL('/') .
            $this->gadget->urlMap('ManageEvents', array('user' => $this->app->session->user->id)));

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
        $event = $this->gadget->request->fetch(array('summary', 'location',
            'verbose', 'link', 'public', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday', 'symbol',
            'start_date', 'stop_date', 'start_time', 'stop_time'), 'post');
        if (empty($event['summary']) || empty($event['start_date'])) {
            $this->gadget->session->push(
                $this::t('ERROR_INCOMPLETE_DATA'),
                RESPONSE_ERROR,
                'Event',
                $event
            );
            Jaws_Header::Referrer();
        }

        $event['owner'] = $event['user'] = (int)$this->app->session->user->id;
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
            $this->gadget->session->push(
                $this::t('ERROR_EVENT_CREATE'),
                RESPONSE_ERROR,
                'Event',
                $event
            );
            Jaws_Header::Referrer();
        }

        $this->gadget->session->push(
            $this::t('NOTICE_EVENT_CREATED'),
            RESPONSE_NOTICE,
            'Event'
        );
        return Jaws_Header::Location($this->gadget->urlMap('ManageEvents', array('user' => $event['user'])));
    }

    /**
     * Updates event
     *
     * @access  public
     * @return  void
     */
    function UpdateEvent()
    {
        $data = $this->gadget->request->fetch(array('id', 'summary', 'location',
            'verbose', 'link', 'public', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday', 'symbol',
            'start_date', 'stop_date', 'start_time', 'stop_time'), 'post');
        if (empty($data['summary']) || empty($data['start_date'])) {
            $this->gadget->session->push(
                $this::t('ERROR_INCOMPLETE_DATA'),
                RESPONSE_ERROR,
                'Event',
                $data
            );
            Jaws_Header::Referrer();
        }

        // Validate event
        $model = $this->gadget->model->load('Event');
        $id = (int)$data['id'];
        $user = (int)$this->app->session->user->id;
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event)) {
            $this->gadget->session->push(
                $this::t('ERROR_RETRIEVING_DATA'),
                RESPONSE_ERROR,
                'Event'
            );
            Jaws_Header::Referrer();
        }

        // Verify owner
        if ($event['owner'] != $user) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_PERMISSION'),
                RESPONSE_ERROR,
                'Event'
            );
            Jaws_Header::Referrer();
        }

        $data['user'] = (int)$this->app->session->user->id;
        if (empty($data['stop_date'])) {
            $data['stop_date'] = $data['start_date'];
        }
        if (empty($data['stop_time'])) {
            $data['stop_time'] = $data['start_time'];
        }

        $result = $model->UpdateEvent($id, $data, $event);
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $this::t('ERROR_EVENT_UPDATE'),
                RESPONSE_ERROR,
                'Event',
                $data
            );
            Jaws_Header::Referrer();
        }

        $this->gadget->session->push(
            $this::t('NOTICE_EVENT_UPDATED'),
            RESPONSE_NOTICE,
            'Event'
        );
        return Jaws_Header::Location($this->gadget->urlMap('ManageEvents', array('user' => $data['user'])));
    }

    /**
     * Deletes passed event(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteEvent()
    {
        $id_set = $this->gadget->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $this->gadget->session->response(
                $this::t('ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // Verify events & user
        $model = $this->gadget->model->load('Event');
        $user = (int)$this->app->session->user->id;
        $verifiedEvents = $model->CheckEvents($id_set, $user);
        if (Jaws_Error::IsError($verifiedEvents)) {
            return $this->gadget->session->response(
                $this::t('ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // No events was verified
        if (empty($verifiedEvents)) {
            return $this->gadget->session->response(
                $this::t('ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        // Delete events
        $model = $this->gadget->model->load('Event');
        $res = $model->DeleteEvents($verifiedEvents);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                $this::t('ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        if (count($id_set) !== count($verifiedEvents)) {
            $msg = $this::t('WARNING_DELETE_EVENTS_FAILED');
            // FIXME: we are creating response twice
            $this->gadget->session->push($msg, RESPONSE_WARNING, 'Event');
            return $this->gadget->session->response($msg, RESPONSE_WARNING);
        }

        $msg = (count($id_set) === 1)?
            $this::t('NOTICE_EVENT_DELETED') :
            $this::t('NOTICE_EVENTS_DELETED');
        $this->gadget->session->push($msg, RESPONSE_NOTICE, 'Event');
        return $this->gadget->session->response($msg);
    }

}