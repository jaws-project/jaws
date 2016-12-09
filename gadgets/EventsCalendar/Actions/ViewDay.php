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
class EventsCalendar_Actions_ViewDay extends Jaws_Gadget_Action
{
    /**
     * Builds day view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewDay()
    {
        // Validate user
        $user = (int)jaws()->request->fetch('user:int', 'get');
        if ($user > 0 && $user !== (int)$GLOBALS['app']->Session->GetAttribute('user')) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/index.css');

        $get = jaws()->request->fetch(array('year', 'month', 'day'), 'get');
        $year = (int)$get['year'];
        $month = (int)$get['month'];
        $day = (int)$get['day'];

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ViewDay.html');
        $tpl->SetBlock('day');

        $tpl->SetVariable('lbl_hour', _t('EVENTSCALENDAR_HOUR'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('Events', $user));

        $jDate = Jaws_Date::getInstance();

        // Next day
        $date = $jDate->ToBaseDate($year, $month, $day + 1);
        $tpl->SetVariable('next', $jDate->Format($date['timestamp'], 'DN d MN Y'));
        $info = $jDate->GetDateInfo($year, $month, $day + 1);
        $url = $user?
            $this->gadget->urlMap('ViewDay', array(
                'user' => $user,
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            )) :
            $this->gadget->urlMap('ViewDay', array(
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            ));
        $tpl->SetVariable('next_url', $url);

        // Previous day
        $date = $jDate->ToBaseDate($year, $month, $day - 1);
        $tpl->SetVariable('prev', $jDate->Format($date['timestamp'], 'DN d MN Y'));
        $info = $jDate->GetDateInfo($year, $month, $day - 1);
        $url = $user?
            $this->gadget->urlMap('ViewDay', array(
                'user' => $user,
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            )) :
            $this->gadget->urlMap('ViewDay', array(
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            ));
        $tpl->SetVariable('prev_url', $url);

        // Today
        $info = $jDate->GetDateInfo($year, $month, $day);
        $date = $jDate->ToBaseDate($year, $month, $day);
        $today = $jDate->Format($date['timestamp'], 'DN d MN Y');
        $this->SetTitle($today . ' - ' . _t('EVENTSCALENDAR_EVENTS'));
        $tpl->SetVariable('year', $info['year']);
        $tpl->SetVariable('month', $info['month']);
        $tpl->SetVariable('day', $info['mday']);
        $tpl->SetVariable('year_url', $user?
            $this->gadget->urlMap('ViewYear', array('user' => $user, 'year' => $info['year'])) :
            $this->gadget->urlMap('ViewYear', array('year' => $info['year'])));
        $tpl->SetVariable('month_url', $user?
            $this->gadget->urlMap('ViewMonth', array('user' => $user, 'year' => $info['year'], 'month' => $info['mon'])) :
            $this->gadget->urlMap('ViewMonth', array('year' => $info['year'], 'month' => $info['mon'])));

        // Fetch events
        $model = $this->gadget->model->load('Calendar');
        $start = $GLOBALS['app']->UserTime2UTC($date['timestamp']);
        $stop = $jDate->ToBaseDate($year, $month, $day, 23, 59, 59);
        $stop = $GLOBALS['app']->UserTime2UTC($stop['timestamp']);
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByHour = array_fill(0, 24, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startIdx = ($e['start_time'] <= $start)? 0 :
                floor(($e['start_time'] - $start) / 3600);
            $stopIdx = ($e['stop_time'] >= $stop)? 23 :
                floor(($e['stop_time'] - $start) / 3600 - 1);
            for ($i = $startIdx; $i <= $stopIdx; $i++) {
                if (!in_array($e['id'], $eventsByHour[$i])) {
                    $eventsByHour[$i][] = $e['id'];
                }
            }
        }

        // Display events
        for ($i = 0; $i <= 23; $i++) {
            $time = date('H:00', mktime($i));
            $tpl->SetBlock('day/hour');
            $tpl->SetVariable('hour', $time);
            foreach ($eventsByHour[$i] as $eventId) {
                $e = $eventsById[$eventId];
                $tpl->SetBlock('day/hour/event');

                $tpl->SetVariable('event', $e['subject']);
                $tpl->SetVariable('type', $e['type']);

                if ($e['priority'] > 0) {
                    $tpl->SetVariable('priority', ($e['priority'] == 1)? 'low' : 'high');
                } else {
                    $tpl->SetVariable('priority', '');
                }

                $url = $user?
                    $this->gadget->urlMap('ViewEvent', array('user' => $user, 'event' => $eventId)) :
                    $this->gadget->urlMap('ViewEvent', array('event' => $eventId));
                $tpl->SetVariable('event_url', $url);

                if ($e['shared']) {
                    $block = ($e['user'] == $e['owner'])? 'shared' : 'foreign';
                    $tpl->SetBlock("day/hour/event/$block");
                    $tpl->ParseBlock("day/hour/event/$block");
                }

                $tpl->ParseBlock('day/hour/event');
            }
            $tpl->ParseBlock('day/hour');
        }

        $tpl->ParseBlock('day');
        return $tpl->Get();
    }
}