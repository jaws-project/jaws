<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/site_style.css');
class EventsCalendar_Actions_Reminder extends Jaws_Gadget_Action
{
    /**
     * Displays events ahead
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Reminder()
    {
        $tpl = $this->gadget->template->load('Reminder.html');
        $tpl->SetBlock('reminder');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_ACTIONS_REMINDER'));
        $this->SetTitle(_t('EVENTSCALENDAR_EVENTS'));

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar());

        $jdate = Jaws_Date::getInstance();

        // Fetch events
        $model = $this->gadget->model->load('Reminder');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $events = $model->GetEvents($user, time());
        if (Jaws_Error::IsError($events)) {
            $events = array();
        }

        // Display events
        foreach ($events as $event) {
            $tpl->SetBlock('reminder/event');

            $tpl->SetVariable('event', $event['subject']);
            $tpl->SetVariable('type', $event['type']);

            if ($event['priority'] > 0) {
                $tpl->SetVariable('priority', ($event['priority'] == 1)? 'low' : 'high');
            } else {
                $tpl->SetVariable('priority', '');
            }

            $datetime = $GLOBALS['app']->UserTime2UTC($event['start_time']);
            $tpl->SetVariable('datetime', $jdate->Format($datetime, 'DN d MN Y - h:i a'));

            $url = $this->gadget->urlMap('ViewEvent', array('id' => $event['id']));
            $tpl->SetVariable('event_url', $url);

            if ($event['owner'] != $user) {
                $tpl->SetBlock('reminder/event/owner');
                $tpl->SetVariable('owner', $event['nickname']);
                $tpl->ParseBlock('reminder/event/owner');
            }
            $tpl->ParseBlock('reminder/event');
        }

        $tpl->ParseBlock('reminder');
        return $tpl->Get();
    }
}