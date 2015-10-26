<?php
/**
 * Shows a simple calendar
 *
 * @category   Calendar
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Start calendar in a specific day of week:
 * 0: Sunday
 * 1: Monday
 * ..
 * 6: Saturday
 */
define ('START_WEEKDAY', 0);

/**
 * Class to show a simple calendar
 */
class Jaws_Calendar
{
    var $Items;
    var $Today;
    var $Day;
    var $Month;
    var $Year;
    var $_TemplateDir;
    var $Arrow;

    /**
     * Class constructor
     */
    function Jaws_Calendar($templateDir)
    {
        $objDate = Jaws_Date::getInstance();
        $this->Items = array();
        $this->Today = $objDate->GetDateInfo(time());
        $this->_TemplateDir = $templateDir;
    }

    /**
     * Adds left/right arrows info
     * Only accept left / right as direction
     */
    function AddArrow($direction, $text, $url)
    {
        if (in_array($direction, array('left', 'right'))) {
            $this->Arrow[$direction] = array('text' => $text, 'url' => $url);
        }
    }

    /**
     * Adds an item to the Items property
     */
    function AddItem($year, $month, $day, $url, $title)
    {
        $this->Items[(int)$year][(int)$month][(int)$day]['url']   = $url;
        $this->Items[(int)$year][(int)$month][(int)$day]['title'] = $title;
    }

    /**
     * Shows a given month/year
     */
    function ShowMonth($month,$year = null)
    {
        if (empty($year)) {
            $year = $this->Today['year'];
        }
        $tpl = new Jaws_Template();
        $tpl->Load('Calendar.html', $this->_TemplateDir);
        $tpl->SetBlock('calendar');
        $tpl->setVariable('title', _t('BLOG_LAYOUT_CALENDAR'));

        $objDate = Jaws_Date::getInstance();
        // Obtain first week day of month
        $date = $objDate->GetDateInfo($year, $month, 1, 12, 0, 0);
        $first = $date['wday'];
        if (($first == 0) && (START_WEEKDAY != 0)) {
             $first += 7;
        }
        // Move to date to first week day.
        $date = $objDate->GetDateInfo($year, $month, START_WEEKDAY + 1 - $first, 12, 0, 0);

        // type cast to int
        $date['year'] = (int)$date['year'];
        $date['mon']  = (int)$date['mon'];
        $date['mday'] = (int)$date['mday'];

        // Set header
        if (isset($this->Arrow['left'])) {
            $left_arrow = '<a href="' . $this->Arrow['left']['url'] . '">' . $this->Arrow['left']['text'] . '</a>';
            $tpl->setVariable('left_arrow', $left_arrow);
        }

        if ($this->Arrow['right']) {
            $right_arrow = '<a href="' . $this->Arrow['right']['url'] . '">' . $this->Arrow['right']['text'] . '</a>';
            $tpl->setVariable('right_arrow', $right_arrow);
        }

        $tpl->SetVariable('month', $objDate->MonthString($month));
        $tpl->SetVariable('year', $year);


        // weekdays
        $wd = START_WEEKDAY;
        for ($i = 0; $i <= 6; $i++) {
            $tpl->SetBlock('calendar/weekday');
            $tpl->SetVariable('weekday', $objDate->DayShortString($wd));
            $tpl->ParseBlock('calendar/weekday');
            $wd < 6 ? $wd++ : $wd = 0;
        }

        // days
        $end = false;
        while (!$end) {
            $tpl->SetBlock('calendar/week');
            for ($iDay = 0; $iDay <= 6; $iDay++) {
                $tpl->SetBlock('calendar/week/day');
                if (
                    $date['mday'] == $this->Today['mday'] &&
                    $date['mon'] == $this->Today['mon']   &&
                    $date['year'] == $this->Today['year']
                 ) {
                    $style = 'today';
                } elseif (
                    $date['mday'] == $this->Day &&
                    $date['mon'] == $this->Month &&
                    $date['year'] == $this->Year
                ) {
                    $style = 'selectedday';
                } elseif ($date['mon'] == $month) {
                    $style = 'day';
                } else {
                    $style = 'noday';

                    // TODO: Simplify this condition ;-)
                    if (
                        (
                            ($date['mon'] > $month)
                            ||
                            ($date['mon'] == 1 && $month == 12)
                        )
                        &&
                        !(
                            ($date['mon'] == 12)
                            &&
                            ($month == 1)
                        )
                    ) {
                        $end = true;
                    }
                }
                $tpl->SetVariable('style', $style);

                $url = $title = '';
                // Got URL?
                if (isset($this->Items[$date['year']][$date['mon']][$date['mday']]['url'])) {
                    $url = $this->Items[$date['year']][$date['mon']][$date['mday']]['url'];
                }

                if (isset($this->Items[$date['year']][$date['mon']][$date['mday']]['title'])) {
                    $title = $this->Items[$date['year']][$date['mon']][$date['mday']]['title'];
                }

                if (!empty($url)) {
                    $tpl->SetVariable('day', '<a href="'.$url.'" title="'.$title.'">'.$date['mday'].'</a>');
                } else {
                    $tpl->SetVariable('day', $date['mday']);
                }
                $date = $objDate->GetDateInfo($date['year'], $date['mon'], $date['mday']+1, 12, 0, 0);
                // type cast to int
                $date['year'] = (int)$date['year'];
                $date['mon']  = (int)$date['mon'];
                $date['mday'] = (int)$date['mday'];

                $tpl->ParseBlock('calendar/week/day');
            }

            $tpl->ParseBlock('calendar/week');
        }

        $tpl->ParseBlock('calendar');
        return $tpl->Get();
    }

}