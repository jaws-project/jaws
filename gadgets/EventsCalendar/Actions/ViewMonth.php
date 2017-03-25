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
class EventsCalendar_Actions_ViewMonth extends Jaws_Gadget_Action
{
    /**
     * Builds month view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewMonth()
    {
        // Validate user
        $user = (int)jaws()->request->fetch('user:int', 'get');
        if ($user > 0 && $user !== (int)$GLOBALS['app']->Session->GetAttribute('user')) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->addLink('gadgets/EventsCalendar/Resources/index.css');

        $get = jaws()->request->fetch(array('year', 'month'), 'get');
        $year = (int)$get['year'];
        $month = (int)$get['month'];

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ViewMonth.html');
        $tpl->SetBlock('month');

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $jDate = Jaws_Date::getInstance();
        $daysInMonth = $jDate->monthDays($year, $month);
        $start = $jDate->ToBaseDate($year, $month, 1);
        $start = $GLOBALS['app']->UserTime2UTC($start['timestamp']);
        $stop = $jDate->ToBaseDate($year, $month, $daysInMonth, 23, 59, 59);
        $stop = $GLOBALS['app']->UserTime2UTC($stop['timestamp']);

        // Current month
        $info = $jDate->GetDateInfo($year, $month, 1);
        $tpl->SetVariable('year', $info['year']);
        $tpl->SetVariable('month', $info['month']);
        $tpl->SetVariable('year_url', $user?
            $this->gadget->urlMap('ViewYear', array('user' => $user, 'year' => $info['year'])) :
            $this->gadget->urlMap('ViewYear', array('year' => $info['year'])));

        $current = $jDate->Format($start, 'Y MN');
        $tpl->SetVariable('title', $current);
        $this->SetTitle($current . ' - ' . _t('EVENTSCALENDAR_EVENTS'));

        // Next month
        $next = $jDate->ToBaseDate($year, $month + 1, 1);
        $next = $jDate->Format($next['timestamp'], 'Y MN');
        $tpl->SetVariable('next', $next);
        $nextYear = $year;
        $nextMonth = $month + 1;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        $nextURL = $user?
            $this->gadget->urlMap('ViewMonth', array('user' => $user, 'year' => $nextYear, 'month' => $nextMonth)) :
            $this->gadget->urlMap('ViewMonth', array('year' => $nextYear, 'month' => $nextMonth));
        $tpl->SetVariable('next_url', $nextURL);

        // Previous month
        $prev = $jDate->ToBaseDate($year, $month - 1, 1);
        $prev = $jDate->Format($prev['timestamp'], 'Y MN');
        $tpl->SetVariable('prev', $prev);
        $prevYear = $year;
        $prevMonth = $month - 1;
        if ($prevMonth === 0) {
            $prevMonth = 12;
            $prevYear--;
        }
        $prevURL = $user?
            $this->gadget->urlMap('ViewMonth', array('user' => $user, 'year' => $prevYear, 'month' => $prevMonth)) :
            $this->gadget->urlMap('ViewMonth', array('year' => $prevYear, 'month' => $prevMonth));
        $tpl->SetVariable('prev_url', $prevURL);

        // Fetch events
        $model = $this->gadget->model->load('Calendar');
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByDay = array_fill(1, $daysInMonth, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startIdx = ($e['start_time'] <= $start)? 1:
                floor(($e['start_time'] - $start) / 86400) + 1;
            $stopIdx = ($e['stop_time'] >= $stop)? $daysInMonth:
                ceil(($e['stop_time'] - $start) / 86400);
            for ($i = $startIdx; $i <= $stopIdx; $i++) {
                if (!in_array($e['id'], $eventsByDay[$i])) {
                    $eventsByDay[$i][] = $e['id'];
                }
            }
        }

        // Display events
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $jDate->ToBaseDate($year, $month, $i);
            $weekDay = $jDate->Format($date['timestamp'], 'DN');
            $tpl->SetBlock('month/day');
            $dayUrl = $user?
                $this->gadget->urlMap('ViewDay', array('user' => $user, 'year' => $year, 'month' => $month, 'day' => $i)) :
                $this->gadget->urlMap('ViewDay', array('year' => $year, 'month' => $month, 'day' => $i));
            $tpl->SetVariable('day_url', $dayUrl);
            $tpl->SetVariable('day', $i . ' ' . $weekDay);
            foreach ($eventsByDay[$i] as $eventId) {
                $e = $eventsById[$eventId];
                $tpl->SetBlock('month/day/event');

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
                    $tpl->SetBlock("month/day/event/$block");
                    $tpl->ParseBlock("month/day/event/$block");
                }
                $tpl->ParseBlock('month/day/event');
            }
            $tpl->ParseBlock('month/day');
        }

        $tpl->ParseBlock('month');
        return $tpl->Get();
    }
}