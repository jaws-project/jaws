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
        $tpl = $this->gadget->loadTemplate('Reminder.html');
        $tpl->SetBlock('reminder');

        $tpl->SetVariable('title', _t('EVENTSCALENDAR_ACTIONS_REMINDER'));
        $this->SetTitle(_t('EVENTSCALENDAR_EVENTS'));

        // Menubar
        $action = $this->gadget->loadAction('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar());

        $time = $GLOBALS['app']->UTC2UserTime();
        $jdate = $GLOBALS['app']->loadDate();
        $info = $jdate->GetDateInfo(time());
        //_log_var_dump($info);
        // FIXME: we don't have daysInMonth
        $daysInMonth = 30;

        // Fetch events
        $model = $this->gadget->model->load('Reminder');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $events = $model->GetEvents($user, $time, null);
        if (Jaws_Error::IsError($events)) {
            $events = array();
        }

        // Display events
        foreach ($events as $event) {
            $tpl->SetBlock('reminder/event');
            $tpl->SetVariable('event', $event['subject']);
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