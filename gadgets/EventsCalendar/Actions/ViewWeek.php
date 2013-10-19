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

        $todayInfo = $jdate->GetDateInfo($year, $month, $day);
        $todayInfo['monthDays'] = 30; // we need this property
        $startDay = $day - $todayInfo['wday'];
        $stopDay = $startDay + 6;
        $startDayInfo = $jdate->GetDateInfo($year, $month, $startDay);
        $stopDayInfo = $jdate->GetDateInfo($year, $month, $stopDay);

        $startDate = $jdate->ToBaseDate($year, $month, $startDay);
        $stopDate = $jdate->ToBaseDate($year, $month, $stopDay);
        $from = $jdate->Format($startDate['timestamp'], 'Y MN d');
        $to = $jdate->Format($stopDate['timestamp'], 'Y MN d');
        $tpl->SetVariable('current_date', $from . ' - ' . $to);

        for ($day = $startDay; $day <= $stopDay; $day++) {
            //$date = $jdate->ToBaseDate($year, $month, $day);
            $info = $jdate->GetDateInfo($year, $month, $day);
            $tpl->SetBlock('week/day');
            $tpl->SetVariable('day', $info['mday'] . ' ' . $info['weekday']);
            $tpl->SetVariable('events', '');
            $tpl->ParseBlock('week/day');
        }

        $tpl->ParseBlock('week');
        return $tpl->Get();
    }
}