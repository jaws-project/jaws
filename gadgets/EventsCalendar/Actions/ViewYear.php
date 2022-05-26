<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_ViewYear extends Jaws_Gadget_Action
{
    /**
     * Builds year view UI
     *
     * @access  public
     * @param   int     $user   User ID or 0
     * @return  string  XHTML UI
     */
    function ViewYear($user = null)
    {
        // Validate user
        if ($user === null) {
            $user = (int)$this->gadget->request->fetch('user:int', 'get');
        }
        if ($user > 0 && $user !== (int)$this->app->session->user->id) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->app->layout->addLink('gadgets/EventsCalendar/Resources/index.css');

        $jDate = Jaws_Date::getInstance();
        $year = (int)$this->gadget->request->fetch('year:int', 'get');
        $year = empty($year)? (int)$jDate->Format(time(), 'Y') : $year;

        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ViewYear.html');
        $tpl->SetBlock('year');

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        // Current year
        $tpl->SetVariable('title', $year);
        $this->SetTitle($year . ' - ' . $this::t('EVENTS'));

        // Next year
        $nextURL = $user?
            $this->gadget->urlMap('ViewYear', array('user' => $user, 'year' => $year + 1)) :
            $this->gadget->urlMap('ViewYear', array('year' => $year + 1));
        $tpl->SetVariable('next_url', $nextURL);
        $tpl->SetVariable('next', $year + 1);

        // Previous year
        $prevURL = $user?
            $this->gadget->urlMap('ViewYear', array('user' => $user, 'year' => $year - 1)) :
            $this->gadget->urlMap('ViewYear', array('year' => $year - 1));
        $tpl->SetVariable('prev_url', $prevURL);
        $tpl->SetVariable('prev', $year - 1);

        // Fetch events
        $model = $this->gadget->model->load('Calendar');
        $events = $model->GetYearEvents($user, null, null, $year);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Months
        for ($s = 0; $s <= 3; $s++) {
            $tpl->SetBlock('year/season');
            for ($i = 1; $i <= 3; $i++) {
                $m = $i + ($s * 3);
                $date = $jDate->ToBaseDate($year, $m);
                $month = $jDate->Format($date['timestamp'], 'MN');
                $tpl->SetBlock('year/season/month');
                $tpl->SetVariable('month', $month);
                $url = $user?
                    $this->gadget->urlMap('ViewMonth', array('user' => $user, 'year' => $year, 'month' => $m)) :
                    $this->gadget->urlMap('ViewMonth', array('year' => $year, 'month' => $m));
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