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
class EventsCalendar_Actions_ViewWeek extends Jaws_Gadget_Action
{
    /**
     * Builds week view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewWeek()
    {
        // Validate user
        $user = (int)jaws()->request->fetch('user:int', 'get');
        if ($user > 0 && $user !== (int)$GLOBALS['app']->Session->GetAttribute('user')) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/index.css');

        $data = jaws()->request->fetch(array('year', 'month', 'day'), 'get');
        $year = (int)$data['year'];
        $month = (int)$data['month'];
        $day = (int)$data['day'];

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ViewWeek.html');
        $tpl->SetBlock('week');

        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_DAY'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('Events'));

        $jDate = Jaws_Date::getInstance();

        // Next week
        $info = $jDate->GetDateInfo($year, $month, $day + 7);
        $nextUrl = $user?
            $this->gadget->urlMap('ViewWeek', array(
                'user' => $user,
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            )) :
            $this->gadget->urlMap('ViewWeek', array(
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            ));
        $tpl->SetVariable('next_url', $nextUrl);
        $tpl->SetVariable('next', _t('EVENTSCALENDAR_NEXT_WEEK'));

        // Previous week
        $info = $jDate->GetDateInfo($year, $month, $day - 7);
        $prevUrl = $user?
            $this->gadget->urlMap('ViewWeek', array(
                'user' => $user,
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            )) :
            $this->gadget->urlMap('ViewWeek', array(
                'year' => $info['year'],
                'month' => $info['mon'],
                'day' => $info['mday']
            ));
        $tpl->SetVariable('prev_url', $prevUrl);
        $tpl->SetVariable('prev', _t('EVENTSCALENDAR_PREV_WEEK'));

        $todayInfo = $jDate->GetDateInfo($year, $month, $day);
        $startDay = $day - $todayInfo['wday'];
        $stopDay = $startDay + 6;

        // This week
        $start = $jDate->ToBaseDate($year, $month, $startDay);
        $start = $GLOBALS['app']->UserTime2UTC($start['timestamp']);
        $stop = $jDate->ToBaseDate($year, $month, $stopDay, 23, 59, 59);
        $stop = $GLOBALS['app']->UserTime2UTC($stop['timestamp']);
        $from = $jDate->Format($start, 'Y MN d');
        $to = $jDate->Format($stop, 'Y MN d');
        $current = $from . ' - ' . $to;
        $this->SetTitle($current . ' - ' . _t('EVENTSCALENDAR_EVENTS'));
        $tpl->SetVariable('title', $current);

        // Fetch events
        $model = $this->gadget->model->load('Calendar');
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByDay = array_fill(1, 7, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startIdx = ($e['start_time'] <= $start)? 1:
                floor(($e['start_time'] - $start) / 86400) + 1;
            $stopIdx = ($e['stop_time'] >= $stop)? 7:
                ceil(($e['stop_time'] - $start) / 86400);
            for ($i = $startIdx; $i <= $stopIdx; $i++) {
                if (!in_array($e['id'], $eventsByDay[$i])) {
                    $eventsByDay[$i][] = $e['id'];
                }
            }
        }

        // Display events
        for ($i = 1; $i <= 7; $i++) {
            $info = $jDate->GetDateInfo($year, $month, $startDay + $i - 1);
            $tpl->SetBlock('week/day');
            $dayUrl = $user?
                $this->gadget->urlMap('ViewDay', array(
                    'user' => $user,
                    'year' => $year,
                    'month' => $month,
                    'day' => $info['mday']
                )) :
                $this->gadget->urlMap('ViewDay', array(
                    'year' => $year,
                    'month' => $month,
                    'day' => $info['mday']
                ));
            $tpl->SetVariable('day_url', $dayUrl);
            $tpl->SetVariable('day', $info['mday'] . ' ' . $info['weekday']);
            foreach ($eventsByDay[$i] as $eventId) {
                $e = $eventsById[$eventId];
                $tpl->SetBlock('week/day/event');

                $tpl->SetVariable('event', $e['subject']);
                $tpl->SetVariable('type', $e['type']);

                if ($e['priority'] > 0) {
                    $tpl->SetVariable('priority', ($e['priority'] == 1)? 'low' : 'high');
                } else {
                    $tpl->SetVariable('priority', '');
                }

                $url = $this->gadget->urlMap('ViewEvent', $user?
                    array('user' => $user, 'event' => $eventId) :
                    array('event' => $eventId)
                );
                $tpl->SetVariable('event_url', $url);

                if ($e['shared']) {
                    $block = ($e['user'] == $e['owner'])? 'shared' : 'foreign';
                    $tpl->SetBlock("week/day/event/$block");
                    $tpl->ParseBlock("week/day/event/$block");
                }

                $tpl->ParseBlock('week/day/event');
            }
            $tpl->ParseBlock('week/day');
        }

        $tpl->ParseBlock('week');
        return $tpl->Get();
    }
}