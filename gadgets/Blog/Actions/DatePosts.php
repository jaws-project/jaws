<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_DatePosts extends Blog_Actions_Default
{
    /**
     * Generates and retrieves Date Page
     * 
     * @access  public
     * @param   mixed   $year   year
     * @param   mixed   $month  month
     * @param   mixed   $day    day
     * @return  string  XHTML template content
     */
    function ViewDatePage($year = '', $month = '', $day = '')
    {
        $get = jaws()->request->fetch(array('year', 'month', 'day', 'page'), 'get');
        $page = (empty($get['page']) || $get['page'] <= 0)? 1 : $get['page'];

        if (empty($year)) {
            if (empty($get['year'])) {
                return false;
            }

            //Month, day and year
            $year  = $get['year'];
            $month = (string) $get['month'];
            $day   = (string) empty($month)? '' : $get['day'];
        }

        $bgnYear  = $year;
        $endYear  = empty($month)? ($year + 1) : $year;
        $bgnMonth = empty($month)? 1 : $month;
        $endMonth = empty($month)? 1 : (empty($day)? ($month + 1) : $month);
        $bgnDay   = empty($day)? 1 : $day;
        $endDay   = empty($day)? 1 : $day + 1;
        $objDate  = $GLOBALS['app']->loadDate();
        $min_date = $objDate->ToBaseDate($bgnYear, $bgnMonth, $bgnDay);
        $max_date = $objDate->ToBaseDate($endYear, $endMonth, $endDay);
        if (!$min_date['timestamp'] || !$max_date['timestamp']) {
            return false;
        }

        $min_date = $GLOBALS['app']->UserTime2UTC($min_date['timestamp'], 'Y-m-d H:i:s');
        $max_date = $GLOBALS['app']->UserTime2UTC($max_date['timestamp'], 'Y-m-d H:i:s');

        $pModel = $this->gadget->model->load('Posts');
        $dpModel = $this->gadget->model->load('DatePosts');
        $entries = $pModel->GetEntriesByDate($page, $min_date, $max_date);
        if (!Jaws_Error::IsError($entries)) {
            $tpl = $this->gadget->template->load('DatePosts.html');
            $tpl->SetBlock('view_date');

            if (empty($month)) {
                $title = $year;
            } else {
                if (empty($day)) {
                    $title = $objDate->MonthString($month).' '.$year;
                } else {
                    $title = $objDate->MonthString($month).' '.$day.', '.$year;
                }
            }
            $this->SetTitle($title);
            $tpl->SetVariable('title', $title);

            if ($tpl->VariableExists('page_navigation')) {
                $total  = $dpModel->GetDateNumberOfPages($min_date, $max_date);
                $limit  = $this->gadget->registry->fetch('last_entries_limit');

                $params = array('year'  => $year,
                                'month' => $month,
                                'day'   => $day,
                               );
                foreach (array_keys($params, '') as $e) {
                    unset($params[$e]);
                }

                $tpl->SetVariable('page_navigation',
                                  $this->GetNumberedPageNavigation($page, $limit, $total, 'ViewDatePage', $params));
            }

            if ($tpl->VariableExists('date_navigation')) {
                $tpl->SetVariable('date_navigation', $this->GetDateNavigation($year, $month, $day));
            }

            if(!empty($entries)) {
                foreach ($entries as $entry) {
                    $this->ShowEntry($tpl, 'view_date', $entry);
                }
            } else {
                header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 404 Not Found");
            }

            $tpl->ParseBlock('view_date');
            return $tpl->Get();
        } else {
            return Jaws_HTTPError::Get(404);
        }
    }

    /**
     * Gets year/month/day nav
     *
     * @access  public
     * @param   mixed   $year   year
     * @param   mixed   $month  month
     * @param   mixed   $day    day
     * @return  string  XHTML template content
     */
    function GetDateNavigation($year, $month, $day)
    {
        $purl   = null;
        $ptitle = null;
        $nurl   = null;
        $ntitle = null;
        $objDate = $GLOBALS['app']->loadDate();
        $model   = $this->gadget->model->load('DatePosts');
        $dLimit  = $model->GetPostsDateLimitation(true);
        if ($dLimit['qty_posts'] != 0) {
            if (empty($month)) {
                $dLimit['max_date'] = $objDate->Format($dLimit['max_date'], 'Y');
                $dLimit['min_date'] = $objDate->Format($dLimit['min_date'], 'Y');
                $pDate = $year - 1;
                if ($pDate >= $dLimit['min_date']) {
                    $purl  = $GLOBALS['app']->Map->GetURLFor('Blog',
                                                             'ViewDatePage',
                                                             array('year'  => $pDate));
                    $ptitle = $pDate;
                }

                $nDate = $year + 1;
                if ($nDate <= $dLimit['max_date']) {
                    $nurl = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                                                            array('year'  => $nDate));
                    $ntitle = $nDate;
                }
            } elseif (empty($day)) {
                $dLimit['max_date'] = $objDate->Format($dLimit['max_date'], 'Y-m');
                $dLimit['min_date'] = $objDate->Format($dLimit['min_date'], 'Y-m');
                $pDate = $objDate->GetDateInfo($year, $month - 1, 1);
                $pDate = $pDate['year'].'-'.$pDate['mon'];
                if ($pDate >= $dLimit['min_date']) {
                    $pDate = explode('-', $pDate);
                    $purl  = $GLOBALS['app']->Map->GetURLFor('Blog',
                                                             'ViewDatePage',
                                                             array('year'  => $pDate[0],
                                                                   'month' => $pDate[1]));
                    $ptitle = $objDate->MonthString($pDate[1]) . ' ' . $pDate[0];
                }

                $nDate = $objDate->GetDateInfo($year, $month + 1, 1);
                $nDate = $nDate['year'].'-'.$nDate['mon'];
                if ($nDate <= $dLimit['max_date']) {
                    $nDate = explode('-', $nDate);
                    $nurl = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                                                   array('year'  => $nDate[0],
                                                         'month' => $nDate[1]));
                    $ntitle = $objDate->MonthString($nDate[1]) . ' ' . $nDate[0];
                }
            } else {
                $dLimit['max_date'] = $objDate->Format($dLimit['max_date'], 'Y-m-d');
                $dLimit['min_date'] = $objDate->Format($dLimit['min_date'], 'Y-m-d');
                $pDate = $objDate->GetDateInfo($year, $month, $day - 1);
                $pDate = $pDate['year'].'-'.$pDate['mon'].'-'.$pDate['mday'];
                if ($pDate >= $dLimit['min_date']) {
                    $pDate = explode('-', $pDate);
                    $purl = $GLOBALS['app']->Map->GetURLFor('Blog',
                                                            'ViewDatePage',
                                                            array('year'  => $pDate[0],
                                                                  'month' => $pDate[1],
                                                                  'day'   => $pDate[2]));
                    $ptitle = $objDate->MonthString($pDate[1]) . ' ' . $pDate[2] . ', '. $pDate[0];
                }

                $nDate = $objDate->GetDateInfo($year, $month, $day + 1);
                $nDate = $nDate['year'].'-'.$nDate['mon'].'-'.$nDate['mday'];
                if ($nDate <= $dLimit['max_date']) {
                    $nDate = explode('-', $nDate);
                    $nurl = $GLOBALS['app']->Map->GetURLFor('Blog',
                                                            'ViewDatePage',
                                                            array('year'  => $nDate[0],
                                                                  'month' => $nDate[1],
                                                                  'day'   => $nDate[2]));
                    $ntitle = $objDate->MonthString($nDate[1]) . ' ' . $nDate[2] . ', '. $nDate[0];
                }
            }
        }

        return $this->GetNavigation($purl, $ptitle, $nurl, $ntitle);
    }

    /**
     * Displays a list of blog entries ordered by date and grouped by month
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MonthlyHistory()
    {
        $tpl = $this->gadget->template->load('MonthlyHistory.html');
        $tpl->SetBlock('monthly_history');
        $tpl->SetVariable('title', _t('BLOG_ARCHIVE'));
        $model = $this->gadget->model->load('DatePosts');
        $entries = $model->GetEntriesAsHistory();
        if (!Jaws_Error::IsError($entries)) {
            $aux_mon_year = '';
            $date = $GLOBALS['app']->loadDate();
            foreach ($entries as $key => $entry) {
                $mon_year = $date->Format($entry['publishtime'], 'm,Y');
                if ($mon_year != $aux_mon_year) {
                    if (!empty($aux_mon_year)) {
                        $tpl->SetBlock('monthly_history/item');
                        $tpl->SetVariable('url',
                            $GLOBALS['app']->Map->GetURLFor('Blog',
                                'ViewDatePage',
                                array('year'  => $year,
                                    'month' => $month)));
                        $tpl->SetVariable('month', $date->MonthString($month) );
                        $tpl->SetVariable('year', $year);
                        $tpl->SetVariable('howmany', $howmany);
                        $tpl->ParseBlock('monthly_history/item');
                    }
                    $aux_mon_year = $mon_year;
                    $year  = substr(strstr($mon_year, ','), 1);
                    $month = substr($mon_year, 0, strpos($mon_year, ','));
                    $howmany = 0;
                }
                $howmany++;

                if ($key == (count($entries) - 1)) {
                    $tpl->SetBlock('monthly_history/item');
                    $tpl->SetVariable('url',
                        $GLOBALS['app']->Map->GetURLFor('Blog',
                            'ViewDatePage',
                            array('year'  => $year,
                                'month' => $month)));
                    $tpl->SetVariable('month', $date->MonthString($month) );
                    $tpl->SetVariable('year', $year);
                    $tpl->SetVariable('howmany', $howmany);
                    $tpl->ParseBlock('monthly_history/item');
                }
            }
        }
        $tpl->ParseBlock('monthly_history');

        return $tpl->Get('archive');
    }

    /**
     * Displays a calendar of the current month/year
     *
     * @access  public
     * @return  bool    True on successful installation, False otherwise
     */
    function Calendar()
    {
        $cal = new Jaws_Calendar('gadgets/Blog/Templates/');
        //By default.
        $objDate = $GLOBALS['app']->loadDate();
        $dt = explode('-', $objDate->Format(time(), 'Y-m-d'));
        $year  = $dt[0];
        $month = $dt[1];
        $day   = $dt[2];

        $get = jaws()->request->fetch(array('gadget', 'action', 'year', 'month', 'day'), 'get');

        // If we are showing a specific month then show calendar of such month
        if (!is_null($get['gadget']) && !is_null($get['action']) && !is_null($get['month'])) {
            if (
                ($get['gadget'] == 'Blog') &&
                ($get['action'] == 'ViewDatePage') &&
                (trim($get['month']) != '')
            ) {
                $year  = $get['year'];
                $month = !is_null($get['month']) ? $get['month'] : '';
                $day   = !is_null($get['day'])   ? $get['day']   : '';
            }
        }

        $cal->Year  = $year;
        $cal->Month = $month;
        $cal->Day   = $day;

        if ($month == '1') {
            $lyear  = $year - 1;
            $lmonth = '12';
        } else {
            $lyear  = $year;
            $lmonth = $month - 1;
        }
        if ($lmonth < 10) {
            $lmonth = '0' . $lmonth;
        }
        $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
            array('year'  => $lyear,
                'month' => $lmonth,
            ));
        $date = $GLOBALS['app']->loadDate();
        $cal->addArrow('left', '&laquo;' . $date->MonthString($lmonth), $url);

        if ($month == '12') {
            $ryear  = $year + 1;
            $rmonth = '1';
        } else {
            $ryear  = $year;
            $rmonth = $month + 1;
        }
        if ($rmonth < 10) {
            $rmonth = '0' . $rmonth;
        }
        $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
            array('year'  => $ryear,
                'month' => $rmonth,
            ));
        $cal->addArrow('right', $date->MonthString($rmonth) . '&raquo;', $url);

        $model = $this->gadget->model->load('DatePosts');
        $bgnDate = $objDate->ToBaseDate($year, $month, 1, 0, 0, 0, 'Y-m-d H:i:s');
        $endDate = $objDate->ToBaseDate($year, $month + 1, 1, 0, 0, 0, 'Y-m-d H:i:s');
        $entries = $model->GetEntriesAsCalendar($bgnDate, $endDate);
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $e) {
                $edt = explode('-', $objDate->Format($e['publishtime'], 'Y-m-d'));
                $cal->AddItem($edt[0], $edt[1], $edt[2],
                    $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                        array('year'  => $edt[0],
                            'month' => $edt[1],
                            'day'   => $edt[2],
                        )),
                    $e['title']);
            }
        }

        return $cal->ShowMonth($cal->Month, $cal->Year);
    }

}