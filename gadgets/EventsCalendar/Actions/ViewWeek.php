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
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewWeek.html');
        $tpl->SetBlock('week');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_WEEK'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_WEEK'));
        $tpl->SetVariable('lbl_day', _t('EVENTSCALENDAR_WEEK_DAY'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $jdate = $GLOBALS['app']->loadDate();

        $year = 2013;
        $month = 10;
        $day = 16;
        $info = $jdate->GetDateInfo($year, $month, $day);
        $wday = $info['wday'];
        $startDay = $day - $wday;
        $date = mktime(0, 0, 0, $month, $startDay, $year);
        $monthName = $jdate->Format($date, 'MN');
        $monthDay = $jdate->Format($date, 'd');
        $theYear = $jdate->Format($date, 'Y');
        $tpl->SetVariable('current_date', $theYear . ', ' . $monthDay . ' - ' . ($monthDay + 6) . ' ' . $monthName);

        for ($i = $startDay; $i <= $startDay + 6; $i++) {
            $date = mktime(0, 0, 0, $month, $i, $year);
            $weekDay = $jdate->Format($date, 'DN');
            $monthDay = $jdate->Format($date, 'd');
            $tpl->SetBlock('week/day');
            $tpl->SetVariable('day', $monthDay . ' ' . $weekDay);
            $tpl->SetVariable('events', '');
            $tpl->ParseBlock('week/day');
        }

        $tpl->ParseBlock('week');
        return $tpl->Get();
    }
}