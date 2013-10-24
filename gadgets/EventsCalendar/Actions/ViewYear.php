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
        $jdate = $GLOBALS['app']->loadDate();
        $year = jaws()->request->fetch('year', 'get');
        $year = empty($year)? (int)$jdate->Format(time(), 'Y') : (int)$year;

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewYear.html');
        $tpl->SetBlock('year');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_YEAR'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_YEAR'));

        // Current year
        $tpl->SetVariable('current_year', $year);

        // Previous year
        $prevURL = $this->gadget->urlMap('ViewYear', array('year' => $year - 1));
        $tpl->SetVariable('prev', $prevURL);
        $tpl->SetVariable('prev_year', $year - 1);

        // Next year
        $nextURL = $this->gadget->urlMap('ViewYear', array('year' => $year + 1));
        $tpl->SetVariable('next', $nextURL);
        $tpl->SetVariable('next_year', $year + 1);

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
                $tpl->ParseBlock('year/season/month');
            }
            $tpl->ParseBlock('year/season');
        }

        $tpl->ParseBlock('year');
        return $tpl->Get();
    }
}