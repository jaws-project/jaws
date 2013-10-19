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
class EventsCalendar_Actions_ViewWeek extends Jaws_Gadget_HTML
{
    /**
     * Builds week view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewWeek()
    {
        $data = jaws()->request->fetch(array('year', 'month', 'day'), 'get');
        $year = (int)$data['year'];
        $month = (int)$data['month'];
        $day = (int)$data['day'];

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewWeek.html');
        $tpl->SetBlock('week');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_WEEK'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_WEEK'));
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_WEEK_DAY'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $jdate = $GLOBALS['app']->loadDate();

        // Previous week
        $info = $jdate->GetDateInfo($year, $month, $day - 6);
        $url = $this->gadget->urlMap('ViewWeek', array(
            'year' => $info['year'],
            'month' => $info['mon'],
            'day' => $info['mday']
        ));
        $tpl->SetVariable('prev', $url);

        // Next week
        $info = $jdate->GetDateInfo($year, $month, $day + 6);
        $url = $this->gadget->urlMap('ViewWeek', array(
            'year' => $info['year'],
            'month' => $info['mon'],
            'day' => $info['mday']
        ));
        $tpl->SetVariable('next', $url);

        $todayInfo = $jdate->GetDateInfo($year, $month, $day);
        $startDay = $day - $todayInfo['wday'];
        $stopDay = $startDay + 6;
        $startDayInfo = $jdate->GetDateInfo($year, $month, $startDay);
        $stopDayInfo = $jdate->GetDateInfo($year, $month, $stopDay);

        // Current week
        $start = $jdate->ToBaseDate($year, $month, $startDay);
        $start = $start['timestamp'];
        $stop = $jdate->ToBaseDate($year, $month, $stopDay);
        $stop = $stop['timestamp'];
        $from = $jdate->Format($start, 'Y MN d');
        $to = $jdate->Format($stop, 'Y MN d');
        $tpl->SetVariable('current_week', $from . ' - ' . $to);

        // Fetch events
        $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Month');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByDay = array_fill(0, 7, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startIdx = ($e['start_time'] <= $start)? 0:
                floor(($e['start_time'] - $start) / 86400);
            $stopIdx = ($e['stop_time'] >= $stop)? 6:
                floor(($e['stop_time'] - $start) / 86400);
            for ($i = $startIdx; $i <= $stopIdx; $i++) {
                $eventsByDay[$i][] = $e['id'];
            }
        }

        // Display events
        for ($i = 0; $i <= 6; $i++) {
            $info = $jdate->GetDateInfo($year, $month, $startDay + $i);
            $tpl->SetBlock('week/day');
            $tpl->SetVariable('day', $info['mday'] . ' ' . $info['weekday']);
            foreach ($eventsByDay[$i] as $event_id) {
                $tpl->SetBlock('week/day/event');
                $tpl->SetVariable('event', $eventsById[$event_id]['subject']);
                $tpl->ParseBlock('week/day/event');
            }
            $tpl->ParseBlock('week/day');
        }

        $tpl->ParseBlock('week');
        return $tpl->Get();
    }
}