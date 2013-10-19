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

        // Current date
        $jdate = $GLOBALS['app']->loadDate();
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