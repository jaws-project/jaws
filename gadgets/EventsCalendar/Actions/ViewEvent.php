<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
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
        // Validate user
        $user = (int)$this->gadget->request->fetch('user:int', 'get');
        if ($user > 0 && $user !== (int)$this->app->session->user->id) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->app->layout->addLink('gadgets/EventsCalendar/Resources/index.css');

        $eventId = (int)$this->gadget->request->fetch('event', 'get');
        $model = $this->gadget->model->load('Event');
        $event = $model->GetEvent($eventId, $user);
        if (Jaws_Error::IsError($event)) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(500);
        }
        if (empty($event) || $event['user'] != $user) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        $jDate = Jaws_Date::getInstance();
        $tpl = $this->gadget->template->load('ViewEvent.html');
        $tpl->SetBlock('event');

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // summary
        $tpl->SetVariable('title', $event['summary']);
        $this->SetTitle($event['summary']);

        // Location
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));

        // Link
        $tpl->SetVariable('link', $event['link']);
        $tpl->SetVariable('lbl_link', Jaws::t('URL'));

        // verbose
        $tpl->SetVariable('desc', $event['verbose']);
        $tpl->SetVariable('lbl_desc', _t('EVENTSCALENDAR_EVENT_DESC'));

        // Start Date/Time
        $start = $event['start_time'];
        $tpl->SetVariable('start_date', $jDate->Format($start, 'Y-m-d'));
        $tpl->SetVariable('start_time', $jDate->Format($start, 'H:i'));

        // Stop Date/Time
        $stop = $event['stop_time'];
        $tpl->SetVariable('stop_date', $jDate->Format($stop, 'Y-m-d'));
        $tpl->SetVariable('stop_time', $jDate->Format($stop, 'H:i'));

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
        $value = '';
        switch ($event['recurrence']) {
            case '0':
            case '1':
                $value = '';
                break;
            case '2':
                $value = ' - ' . $jDate->DayString($event['wday'] - 1);
                break;
            case '3':
                $value = ' - ' . $event['day'] . ' ' . _t('EVENTSCALENDAR_EVENT_RECURRENCE_EVERY_MONTH');
                break;
            case '4':
                $value = ' - ' . $event['day'] . ' ' . $jDate->MonthString($event['month']);
                break;
        }
        $tpl->SetVariable('recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE_'.$event['recurrence']));
        $tpl->SetVariable('rec_value', $value);
        $tpl->SetVariable('lbl_recurrence', _t('EVENTSCALENDAR_EVENT_RECURRENCE'));

        // Public
        $tpl->SetVariable('public', $event['public']? Jaws::t('YES') : Jaws::t('NO'));
        $tpl->SetVariable('lbl_public', _t('EVENTSCALENDAR_EVENT_PUBLIC'));

        // Shared
        $tpl->SetVariable('shared', $event['shared']? Jaws::t('YES') : Jaws::t('NO'));
        $tpl->SetVariable('lbl_shared', _t('EVENTSCALENDAR_SHARED'));

        // Symbol
        $tpl->SetVariable('symbol', $event['symbol']);

        // Actions
        $siteUrl = $this->app->getSiteURL('/');
        $tpl->SetVariable('url_edit', $siteUrl . $this->gadget->urlMap('EditEvent',
                array('user' => $user, 'event' => $eventId)));
        $tpl->SetVariable('lbl_edit', Jaws::t('EDIT'));
        $tpl->SetVariable('url_share', $siteUrl . $this->gadget->urlMap('ShareEvent',
                array('user' => $user, 'event' => $eventId)));
        $tpl->SetVariable('lbl_share', _t('EVENTSCALENDAR_SHARE'));

        $tpl->ParseBlock('event');
        return $tpl->Get();
    }
}