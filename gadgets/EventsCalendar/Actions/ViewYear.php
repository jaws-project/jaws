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
class EventsCalendar_Actions_ViewYear extends Jaws_Gadget_HTML
{
    /**
     * Builds year view UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewYear()
    {
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('ViewYear.html');
        $tpl->SetBlock('year');

        $this->SetTitle(_t('EVENTSCALENDAR_VIEW_YEAR'));
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_VIEW_YEAR'));

        $jdate = $GLOBALS['app']->loadDate();

        $year = 1392;
        $tpl->SetVariable('current_date', $year);

        for ($i = 1; $i <= 12; $i++) {
            $date = $jdate->ToBaseDate($year, $i);
            $month = $jdate->Format($date['timestamp'], 'MN');
            $tpl->SetBlock('year/month');
            $tpl->SetVariable('month', $month);
            $tpl->ParseBlock('year/month');
        }

        $tpl->ParseBlock('year');
        return $tpl->Get();
    }
}