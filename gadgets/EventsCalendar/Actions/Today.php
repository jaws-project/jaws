<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Today extends Jaws_Gadget_Action
{
    /**
     * Gets Today action params
     *
     * @access  public
     * @return  array   List of Today action params
     */
    function TodayLayoutParams()
    {
        return array(
            array(
                'title' => $this::t('EVENTS'),
                'value' => array(
                    'user' => $this::t('USER_EVENTS'),
                    'public' => $this::t('PUBLIC_EVENTS')
                )
            ),
        );
    }

    /**
     * Displays today events
     *
     * @access  public
     * @param   string  $user   Reminder type [public|user]
     * @return  string XHTML UI
     */
    function Today($user)
    {
        if ($user === 'user' && !$this->app->session->user->logged) {
            return '';
        }

        $this->app->layout->addLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('Today.html');
        $tpl->SetBlock('today');

        $tpl->SetVariable('lbl_hour', $this::t('HOUR'));
        $tpl->SetVariable('lbl_events', $this::t('EVENTS'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // Today
        $jDate = Jaws_Date::getInstance();
        $now = time();
        $today = $jDate->Format($now, 'DN d MN Y');

        $this->SetTitle($today . ' - ' . $this::t('EVENTS'));
        $tpl->SetVariable('today', $today);
        if ($user === 'public') {
            $tpl->SetVariable('title', $this::t('PUBLIC_EVENTS'));
        } else {
            $tpl->SetVariable('title', $this::t('USER_EVENTS'));
        }
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_location', $this::t('EVENT_LOCATION'));
        $tpl->SetVariable('lbl_type', $this::t('EVENT_TYPE'));
        $tpl->SetVariable('lbl_priority', $this::t('EVENT_PRIORITY'));
        $tpl->SetVariable('lbl_time', $this::t('TIME'));

        // Fetch events
        $info = $jDate->GetDateInfo($now);
        $dayStart = $jDate->ToBaseDate($info['year'], $info['mon'], $info['mday'], 0, 0, 0);
        $dayStart = $this->app->UserTime2UTC($dayStart['timestamp']);
        $dayEnd = $jDate->ToBaseDate($info['year'], $info['mon'], $info['mday'], 23, 59, 59);
        $dayEnd = $this->app->UserTime2UTC($dayEnd['timestamp']);
        $model = $this->gadget->model->load('Today');
        if ($user === 'public') {
            $events = $model->GetPublicEvents($dayStart, $dayEnd);
        } else {
            $user = (int)$this->app->session->user->id;
            $events = $model->GetUserEvents($user, $dayStart, $dayEnd);
        }
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Display events
        foreach ($events as $event) {
            $tpl->SetBlock('today/event');

            $tpl->SetVariable('title', $event['title']);
            $tpl->SetVariable('type', $this::t('EVENT_TYPE_' . $event['type']));
            $tpl->SetVariable('location', $event['location']);
            $tpl->SetVariable('priority', $this::t('EVENT_PRIORITY_' . $event['priority']));

            $startHour = $jDate->Format($event['start_time'], 'H:i');
            $stopHour = $jDate->Format($event['stop_time'], 'H:i');
            $tpl->SetVariable('time', $startHour . ' - ' . $stopHour);

            $url = $user?
                $this->gadget->urlMap('ViewEvent', array('user' => $user, 'event' => $event['id'])) :
                $this->gadget->urlMap('ViewEvent', array('event' => $event['id']));
            $tpl->SetVariable('event_url', $url);

            $tpl->ParseBlock('today/event');
        }

        $tpl->ParseBlock('today');
        return $tpl->Get();
    }
}