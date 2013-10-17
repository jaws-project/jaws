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
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/resources/site_style.css');
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

        $jdate = $GLOBALS['app']->loadDate();

        $year = 1392;
        $month = 7;
        $daysInMonth = 31;
        $date = $jdate->ToBaseDate($year, $month, 1);
        $monthName = $jdate->Format($date['timestamp'], 'MN');
        $tpl->SetVariable('current_date', $year . ' ' . $monthName);

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $jdate->ToBaseDate($year, $month, $i);
            $weekDay = $jdate->Format($date['timestamp'], 'DN');
            $tpl->SetBlock('month/day');
            $tpl->SetVariable('day', $i . ' ' . $weekDay);
            $tpl->SetVariable('events', '');
            $tpl->ParseBlock('month/day');
        }

        $tpl->ParseBlock('month');
        return $tpl->Get();
    }
}