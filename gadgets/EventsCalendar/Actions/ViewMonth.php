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
class EventsCalendar_Actions_ViewMonth extends Jaws_Gadget_HTML
{
    /**
     * Builds week view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewMonth()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewMonth.html');
        $tpl->SetBlock('month');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_MONTH'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_MONTH'));
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_WEEK_DAY'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $year = 1392;
        $month = 7;
        $daysInMonth = 30;
        $jdate = $GLOBALS['app']->loadDate();
        $start = $jdate->ToBaseDate($year, $month, 1);
        $start = $start['timestamp'];
        $stop = $jdate->ToBaseDate($year, $month, $daysInMonth, 23, 59, 59);
        $stop = $stop['timestamp'];

        $monthName = $jdate->Format($start, 'MN');
        $tpl->SetVariable('current_date', $year . ' ' . $monthName);

        // Fetch events
        $model = $GLOBALS['app']->LoadGadget('EventsCalendar', 'Model', 'Month');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $events = $model->GetEvents($user, null, null, $start, $stop);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Prepare events
        $eventsById = array();
        $eventsByDay = array_fill(1, $daysInMonth, array());
        foreach ($events as $e) {
            $eventsById[$e['id']] = $e;
            $startDay = $e['start_time'] - $start;
            $startDay = ($startDay <= 0)? 1 : ceil($startDay / 86400);
            $length = ceil($e['stop_time'] - $e['start_time']) / 86400;
            $stopDay = ($startDay + $length > $daysInMonth)? $daysInMonth : $startDay + $length;
            for ($dayIndex = $startDay; $dayIndex <= $stopDay; $dayIndex++) {
                $eventsByDay[$dayIndex][] = $e['id'];
            }
        }

        // Display events
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $jdate->ToBaseDate($year, $month, $i);
            $weekDay = $jdate->Format($date['timestamp'], 'DN');
            $tpl->SetBlock('month/day');
            $tpl->SetVariable('day', $i . ' ' . $weekDay);
            foreach ($eventsByDay[$i] as $event_id) {
                $tpl->SetBlock('month/day/event');
                $tpl->SetVariable('event', $eventsById[$event_id]['subject']);
                $tpl->ParseBlock('month/day/event');
            }
            $tpl->ParseBlock('month/day');
        }

        $tpl->ParseBlock('month');
        return $tpl->Get();
    }
}