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
class EventsCalendar_Actions_ViewDay extends Jaws_Gadget_HTML
{
    /**
     * Builds day view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewDay()
    {
        $data = jaws()->request->fetch(array('year', 'month', 'day'), 'get');
        $year = (int)$data['year'];
        $month = (int)$data['month'];
        $day = (int)$data['day'];

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewDay.html');
        $tpl->SetBlock('day');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_DAY'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_DAY'));
        $tpl->SetVariable('lbl_hour', _t('EVENTSCALENDAR_HOUR'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $jdate = $GLOBALS['app']->loadDate();

        // Current date
        $date = $jdate->ToBaseDate($year, $month, $day);
        $tpl->SetVariable('current_date', $jdate->Format($date['timestamp'], 'DN d MN Y'));

        // Previous day
        $info = $jdate->GetDateInfo($year, $month, $day - 1);
        $url = $this->gadget->urlMap('ViewDay', array(
            'year' => $info['year'],
            'month' => $info['mon'],
            'day' => $info['mday']
        ));
        $tpl->SetVariable('prev', $url);
        $tpl->SetVariable('prev_day', $info['weekday']);

        // Next day
        $info = $jdate->GetDateInfo($year, $month, $day + 1);
        $url = $this->gadget->urlMap('ViewDay', array(
            'year' => $info['year'],
            'month' => $info['mon'],
            'day' => $info['mday']
        ));
        $tpl->SetVariable('next', $url);
        $tpl->SetVariable('next_day', $info['weekday']);

        // Fetch events
        $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Month');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $start = $date['timestamp'];
        $stop = $jdate->ToBaseDate($year, $month, $day, 23, 59, 59);
        $stop = $stop['timestamp'];
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByHour = array_fill(0, 24, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startIdx = ($e['start_time'] <= $start)? 0:
                floor(($e['start_time'] - $start) / 3600);
            $stopIdx = ($e['stop_time'] >= $stop)? 23:
                floor(($e['stop_time'] - $start) / 3600);
            for ($i = $startIdx; $i <= $stopIdx; $i++) {
                $eventsByHour[$i][] = $e['id'];
            }
        }

        // Display events
        for ($i = 0; $i <= 23; $i++) {
            $time = date('H:00', mktime($i));
            $tpl->SetBlock('day/hour');
            $tpl->SetVariable('hour', $time);
            foreach ($eventsByHour[$i] as $event_id) {
                $tpl->SetBlock('day/hour/event');
                $tpl->SetVariable('event', $eventsById[$event_id]['subject']);
                $tpl->ParseBlock('day/hour/event');
            }
            $tpl->ParseBlock('day/hour');
        }

        $tpl->ParseBlock('day');
        return $tpl->Get();
    }
}