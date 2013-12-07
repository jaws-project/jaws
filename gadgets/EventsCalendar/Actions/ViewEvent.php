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
class EventsCalendar_Actions_ViewEvent extends Jaws_Gadget_Action
{
    /**
     * Displays an event
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewEvent()
    {
        $id = (int)jaws()->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event) ||
            empty($event) ||
            $event['user'] != $user)
        {
            return;
        }

        $jdate = Jaws_Date::getInstance();
        $tpl = $this->gadget->template->load('ViewEvent.html');
        $tpl->SetBlock('event');

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('Events'));

        // Subject
        $tpl->SetVariable('title', $event['subject']);
        $this->SetTitle($event['subject']);

        // Location
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));

        // Description
        $tpl->SetVariable('desc', $event['description']);
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));

        // Start Date/Time
        $start = $event['start_time'];
        $tpl->SetVariable('start_date', $jdate->Format($start, 'Y-m-d'));
        $tpl->SetVariable('start_time', $jdate->Format($start, 'H:i'));

        // Stop Date/Time
        $stop = $event['stop_time'];
        $tpl->SetVariable('stop_date', $jdate->Format($stop, 'Y-m-d'));
        $tpl->SetVariable('stop_time', $jdate->Format($stop, 'H:i'));

        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));
        $tpl->SetVariable('lbl_time', _t('EVENTSCALENDAR_TIME'));

        // Type
        $tpl->SetVariable('type', _t('EVENTSCALENDAR_EVENT_TYPE_'.$event['type']));
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));

        // Priority
        $tpl->SetVariable('priority', _t('EVENTSCALENDAR_EVENT_PRIORITY_'.$event['priority']));
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));

        // Reminder
        $tpl->SetVariable('reminder', _t('EVENTSCALENDAR_EVENT_REMINDER_'.$event['reminder']/60));
        $tpl->SetVariable('lbl_reminder', _t('EVENTSCALENDAR_EVENT_REMINDER'));

        // Recurrences
        switch ($event['recurrence']) {
            case '0':
            case '1':
                $value = '';
                break;
            case '2':
                $value = ' - ' . $jdate->DayString($event['wday']);
                break;
            case '3':
                $value = ' - ' . $event['day'] . ' ' . _t('EVENTSCALENDAR_EVENT_RECURRENCE_EVERY_MONTH');
                break;
            case '4':
                $value = ' - ' . $event['day'] . ' ' . $jdate->MonthString($event['month']);
                break;
        }
        $tpl->SetVariable('recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE_'.$event['recurrence']));
        $tpl->SetVariable('rec_value', $value);
        $tpl->SetVariable('lbl_recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE'));

        // Shared
        $tpl->SetVariable('shared', $event['shared']? _t('GLOBAL_YES') : _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));

        // Actions
        $site_url = $GLOBALS['app']->GetSiteURL('/');
        $tpl->SetVariable('url_edit', $site_url . $this->gadget->urlMap('EditEvent', array('id' => $id)));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('url_share', $site_url . $this->gadget->urlMap('ShareEvent', array('id' => $id)));
        $tpl->SetVariable('lbl_share', _t('EVENTSCALENDAR_SHARE'));

        $tpl->ParseBlock('event');
        return $tpl->Get();
    }
}