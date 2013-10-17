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
class EventsCalendar_Actions_ViewDay extends Jaws_Gadget_HTML
{
    /**
     * Builds week view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewDay()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewDay.html');
        $tpl->SetBlock('day');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_DAY'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_DAY'));
        $tpl->SetVariable('lbl_hour', _t('EVENTSCALENDAR_HOUR'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        $jdate = $GLOBALS['app']->loadDate();

        $year = 1392;
        $month = 7;
        $day = 24;
        $date = $jdate->ToBaseDate($year, $month, $day);
        $tpl->SetVariable('current_date', $jdate->Format($date['timestamp'], 'DN d MN Y'));

        for ($i = 0; $i <= 23; $i++) {
            //$time = date('ga', mktime($hour));
            $time = date('H:00', mktime($i));
            $tpl->SetBlock('day/hour');
            $tpl->SetVariable('hour', $time);
            $tpl->SetVariable('events', '');
            $tpl->ParseBlock('day/hour');
        }

        $tpl->ParseBlock('day');
        return $tpl->Get();
    }
}