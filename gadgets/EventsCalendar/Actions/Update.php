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
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/resources/site_style.css');
class EventsCalendar_Actions_Update extends Jaws_Gadget_HTML
{
    /**
     * Builds form to edit an event
     *
     * @access  public
     * @return  string  XHTML form
     */
    function EditEvent()
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
        }

        if (!isset($event) || empty($event)) {
            $id = (int)jaws()->request->fetch('id', 'get');
            $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Events');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $event = $model->GetEvent($id, $user);
            if (Jaws_Error::IsError($event) ||
                empty($event) ||
                $event['user'] != $user)
            {
                return;
            }
            $date = $GLOBALS['app']->loadDate();
            $event['start_time'] = empty($event['start_time'])? '' :
                $date->Format($event['start_time'], 'Y-m-d H:i');
            $event['stop_time'] = empty($event['stop_time'])? '' :
                $date->Format($event['stop_time'], 'Y-m-d H:i');
        }

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_EDIT_EVENT'));
        $tpl->SetVariable('errorIncompleteData', _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'));
        $tpl->SetVariable('action', 'editevent');
        $tpl->SetVariable('form_action', 'UpdateEvent');
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('Events'));

        $tpl->SetVariable('id', $event['id']);
        $tpl->SetVariable('subject', $event['subject']);
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('description', $event['description']);

        // Start time
        $cal_type = $this->gadget->registry->fetch('calendar_type', 'Settings');
        $dp =& Piwi::CreateWidget('DatePicker', 'start_time', $event['start_time']);
        $dp->SetId('event_start_time');
        $dp->showTimePicker(true);
        $dp->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $dp->setCalType($cal_type);
        $dp->setDateFormat('%Y-%m-%d %H:00');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $dp->Get());

        // Stop time
        $dp =& Piwi::CreateWidget('DatePicker', 'stop_time', $event['stop_time']);
        $dp->SetId('event_stop_time');
        $dp->showTimePicker(true);
        $dp->setDateFormat('%Y-%m-%d %H:00');
        $dp->SetIncludeCSS(false);
        $dp->SetIncludeJS(false);
        $dp->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $dp->setCalType($cal_type);
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $dp->Get());

        // Type
        $combo =& Piwi::CreateWidget('Combo', 'type');
        $combo->SetId('event_type');
        for ($i = 0; $i <= 5; $i++) {
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
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_NO_REPEAT'), 0);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_DAILY'), 1);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_WEEKLY'), 2);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_MONTHLY'), 3);
        $combo->AddOption(_t('EVENTSCALENDAR_EVENT_REPEAT_YEARLY'), 4);
        //$combo->SetDefault($event['repeated']);
        $tpl->SetVariable('repeat', $combo->Get());
        $tpl->SetVariable('lbl_repeat', _t('EVENTSCALENDAR_EVENT_REPEAT'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('Events'));

        $tpl->ParseBlock('form');
        return $tpl->Get();
    }

    /**
     * Updates event
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateEvent()
    {
        $data = jaws()->request->fetch(array('id', 'subject', 'location', 'description',
            'start_time', 'stop_time', 'type', 'priority', 'reminder', 'repeat'), 'post');
        if (empty($data['subject']) || empty($data['start_time'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'),
                'Events.Response',
                RESPONSE_ERROR,
                $data
            );
            Jaws_Header::Referrer();
        }

        // Validate event
        $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Events');
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
        Jaws_Header::Location($this->gadget->urlMap('Events'));
    }
}