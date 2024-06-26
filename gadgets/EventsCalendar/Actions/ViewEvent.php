<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2024 Jaws Development Group
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
        $this->title = $event['summary'];

        // Location
        $tpl->SetVariable('location', $event['location']);
        $tpl->SetVariable('lbl_location', $this::t('EVENT_LOCATION'));

        // Link
        $tpl->SetVariable('link', $event['link']);
        $tpl->SetVariable('lbl_link', Jaws::t('URL'));

        // verbose
        $tpl->SetVariable('desc', $event['verbose']);
        $tpl->SetVariable('lbl_desc', $this::t('EVENT_DESC'));

        // Start Date/Time
        $start = $event['start_time'];
        $tpl->SetVariable('start_date', $jDate->Format($start, 'yyyy-MM-dd'));
        $tpl->SetVariable('start_time', $jDate->Format($start, 'HH:mm'));

        // Stop Date/Time
        $stop = $event['stop_time'];
        $tpl->SetVariable('stop_date', $jDate->Format($stop, 'yyyy-MM-dd'));
        $tpl->SetVariable('stop_time', $jDate->Format($stop, 'HH:mm'));

        $tpl->SetVariable('lbl_date', $this::t('DATE'));
        $tpl->SetVariable('lbl_time', $this::t('TIME'));

        // Type
        $tpl->SetVariable('type', $this::t('EVENT_TYPE_'.$event['type']));
        $tpl->SetVariable('lbl_type', $this::t('EVENT_TYPE'));

        // Priority
        $tpl->SetVariable('priority', $this::t('EVENT_PRIORITY_'.$event['priority']));
        $tpl->SetVariable('lbl_priority', $this::t('EVENT_PRIORITY'));

        // Reminder
        $tpl->SetVariable('reminder', $this::t('EVENT_REMINDER_'.$event['reminder']/60));
        $tpl->SetVariable('lbl_reminder', $this::t('EVENT_REMINDER'));

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
                $value = ' - ' . $event['day'] . ' ' . $this::t('EVENT_RECURRENCE_EVERY_MONTH');
                break;
            case '4':
                $value = ' - ' . $event['day'] . ' ' . $jDate->MonthString($event['month']);
                break;
        }
        $tpl->SetVariable('recurrence', $this::t('EVENT_RECURRENCE_'.$event['recurrence']));
        $tpl->SetVariable('rec_value', $value);
        $tpl->SetVariable('lbl_recurrence', $this::t('EVENT_RECURRENCE'));

        // Public
        $tpl->SetVariable('public', $event['public']? Jaws::t('YESS') : Jaws::t('NOO'));
        $tpl->SetVariable('lbl_public', $this::t('EVENT_PUBLIC'));

        // Shared
        $tpl->SetVariable('shared', $event['shared']? Jaws::t('YESS') : Jaws::t('NOO'));
        $tpl->SetVariable('lbl_shared', $this::t('SHARED'));

        // Symbol
        $tpl->SetVariable('symbol', $event['symbol']);

        // Actions
        $siteUrl = $this->app->getSiteURL('/');
        $tpl->SetVariable('url_edit', $siteUrl . $this->gadget->urlMap('EditEvent',
                array('user' => $user, 'event' => $eventId)));
        $tpl->SetVariable('lbl_edit', Jaws::t('EDIT'));
        $tpl->SetVariable('url_share', $siteUrl . $this->gadget->urlMap('ShareEvent',
                array('user' => $user, 'event' => $eventId)));
        $tpl->SetVariable('lbl_share', $this::t('SHARE'));

        $tpl->ParseBlock('event');
        return $tpl->Get();
    }
}