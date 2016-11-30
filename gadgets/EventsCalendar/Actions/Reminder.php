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
class EventsCalendar_Actions_Reminder extends Jaws_Gadget_Action
{
    /**
     * Gets Calendar action params
     *
     * @access  public
     * @return  array   List of Calendar action params
     */
    function ReminderLayoutParams()
    {
        return array(
            array(
                'title' => _t('EVENTSCALENDAR_ACTIONS_REMINDER'),
                'value' => array(
                    'user' => _t('EVENTSCALENDAR_USER_EVENTS'),
                    'public' => _t('EVENTSCALENDAR_PUBLIC_EVENTS')
                )
            ),
        );
    }

    /**
     * Displays events ahead
     *
     * @access  public
     * @param   string  $user   Reminder type [public|user]
     * @return  string  XHTML UI
     */
    function Reminder($user)
    {
        if ($user === 'user' && !$GLOBALS['app']->Session->Logged()) {
            return '';
        }

        $GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/index.css');
        $tpl = $this->gadget->template->load('Reminder.html');
        $tpl->SetBlock('reminder');

        $this->SetTitle(_t('EVENTSCALENDAR_EVENTS'));
        if ($user === 'public') {
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_PUBLIC_EVENTS') . ' - ' . _t('EVENTSCALENDAR_ACTIONS_REMINDER'));
        } else {
            $tpl->SetVariable('title', _t('EVENTSCALENDAR_USER_EVENTS') . ' - ' . _t('EVENTSCALENDAR_ACTIONS_REMINDER'));
        }

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar());

        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));

        // Fetch events
        $model = $this->gadget->model->load('Reminder');
        if ($user === 'public') {
            $events = $model->GetPublicEvents(time());
        } else {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $events = $model->GetEvents($user, time());
        }
        if (Jaws_Error::IsError($events)) {
            $events = array();
        }

        // Display events
        $jDate = Jaws_Date::getInstance();
        foreach ($events as $event) {
            $tpl->SetBlock('reminder/event');

            $tpl->SetVariable('subject', $event['subject']);
            $tpl->SetVariable('type', _t('EVENTSCALENDAR_EVENT_TYPE_' . $event['type']));
            $tpl->SetVariable('location', $event['location']);
            $tpl->SetVariable('priority', _t('EVENTSCALENDAR_EVENT_PRIORITY_' . $event['priority']));

            $date = $GLOBALS['app']->UserTime2UTC($event['start_time']);
            $tpl->SetVariable('date', $jDate->Format($date, 'DN d MN Y - h:i a'));

            if ($user === 'public') {
                $owner = '';
            } else {
                $owner = ($event['owner'] == $user) ? '' : $event['nickname'] || '';
            }
            $tpl->SetVariable('owner', $owner);

            $url = $this->gadget->urlMap('ViewEvent', array('id' => $event['id']));
            $tpl->SetVariable('event_url', $url);

            $tpl->ParseBlock('reminder/event');
        }

        $tpl->ParseBlock('reminder');
        return $tpl->Get();
    }
}