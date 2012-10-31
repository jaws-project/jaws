<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_DatePosts extends BlogHTML
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
        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('year', 'month', 'day', 'page'), 'get');
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

        $bModel = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entries = $bModel->GetEntriesByDate($page, $min_date, $max_date);
        if (!Jaws_Error::IsError($entries)) {
            $tpl = new Jaws_Template('gadgets/Blog/templates/');
            $tpl->Load('ViewDate.html', true);
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
                $total  = $bModel->GetDateNumberOfPages($min_date, $max_date);
                $limit  = $GLOBALS['app']->Registry->Get('/gadgets/Blog/last_entries_limit');

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
                $res = '';
                $tpl->SetBlock('view_date/entry');
                $tplEntry = $tpl->GetRawBlockContent();
                foreach ($entries as $entry) {
                    $res .= $this->ShowEntry($entry, true, true, $tplEntry);
                }
                $tpl->SetCurrentBlockContent($res);
                $tpl->ParseBlock('view_date/entry');
            } else {
                $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
                header($xss->filter($_SERVER['SERVER_PROTOCOL'])." 404 Not Found");
            }

            $tpl->ParseBlock('view_date');
            return $tpl->Get();
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
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
        $model   = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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

}