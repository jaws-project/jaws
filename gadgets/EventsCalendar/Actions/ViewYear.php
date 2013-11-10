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
class EventsCalendar_Actions_ViewYear extends Jaws_Gadget_Action
{
    /**
     * Builds year view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewYear()
    {
        $jdate = Jaws_Date::getInstance();
        $year = jaws()->request->fetch('year', 'get');
        $year = empty($year)? (int)$jdate->Format(time(), 'Y') : (int)$year;

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('ViewYear.html');
        $tpl->SetBlock('year');

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar('Events'));

        // Current year
        $tpl->SetVariable('title', $year);
        $this->SetTitle($year . ' - ' . _t('EVENTSCALENDAR_EVENTS'));

        // Previous year
        $prevURL = $this->gadget->urlMap('ViewYear', array('year' => $year - 1));
        $tpl->SetVariable('prev_url', $prevURL);
        $tpl->SetVariable('prev', $year - 1);

        // Next year
        $nextURL = $this->gadget->urlMap('ViewYear', array('year' => $year + 1));
        $tpl->SetVariable('next_url', $nextURL);
        $tpl->SetVariable('next', $year + 1);

        // Fetch events
        $model = $this->gadget->model->load('Calendar');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $events = $model->GetYearEvents($user, null, null, $year);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Month's
        for ($s = 0; $s <= 3; $s++) {
            $tpl->SetBlock('year/season');
            for ($i = 1; $i <= 3; $i++) {
                $m = $i + ($s * 3);
                $date = $jdate->ToBaseDate($year, $m);
                $month = $jdate->Format($date['timestamp'], 'MN');
                $tpl->SetBlock('year/season/month');
                $tpl->SetVariable('month', $month);
                $url = $this->gadget->urlMap('ViewMonth', array('year' => $year, 'month' => $m));
                $tpl->SetVariable('month_url', $url);
                $tpl->SetVariable('events_count', $events[$m]);
                $tpl->ParseBlock('year/season/month');
            }
            $tpl->ParseBlock('year/season');
        }

        $tpl->ParseBlock('year');
        return $tpl->Get();
    }
}