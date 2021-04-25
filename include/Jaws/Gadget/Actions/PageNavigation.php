<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_PageNavigation extends Jaws_Gadget_Class
{
    /**
     * Get navigate pagination
     *
     * @access  public
     * @param   object  $tpl        (Optional) Jaws Template object
     * @param   int     $current    Current page number
     * @param   int     $ipp        Item per page
     * @param   int     $total      Total items
     * @param   string  $action     Gadget action name
     * @param   array   $params     Action params array
     * @param   string  $label      Total label
     * @param   string  $gadget     Gadget name
     * @return  string  XHTML template content
     */
    function pagination(&$tpl, $current, $ipp, $total, $action, $params = array(), $label = '', $gadget = '')
    {
        $pager = $this->GetPagerNumbered($current, $ipp, $total);
        if (empty($pager)) {
            return '';
        }

        if (empty($tpl)) {
            $tpl = new Jaws_Template();
            $tpl->Load('Navigation.html', 'include/Jaws/Resources');
            $block = '';
        } else {
            $block = $tpl->GetCurrentBlockPath();
        }
        $tpl->SetBlock("$block/pagination");
        //$tpl->SetVariable('total', Jaws::t('PAGINATION_COUNT', $pager['total']));

        foreach ($pager as $k => $v) {
            $tpl->SetBlock("$block/pagination/page");
            $params['page'] = $v;
            $pageURL = $this->gadget->urlMap($action, $params, array(), $gadget);
            if ($k == 'next') {
                if ($v) {
                    $tpl->SetBlock("$block/pagination/page/next");
                    $tpl->SetVariable('lbl', Jaws::t('PAGINATION_NEXT'));
                    $tpl->SetVariable('url', $pageURL);
                    $tpl->ParseBlock("$block/pagination/page/next");
                } else {
                    $tpl->SetBlock("$block/pagination/page/stop");
                    $tpl->SetVariable('lbl', Jaws::t('PAGINATION_NEXT'));
                    $tpl->ParseBlock("$block/pagination/page/stop");
                }
            } elseif ($k == 'previous') {
                if ($v) {
                    $tpl->SetBlock("$block/pagination/page/previous");
                    $tpl->SetVariable('lbl', Jaws::t('PAGINATION_PREVIOUS'));
                    $tpl->SetVariable('url', $pageURL);
                    $tpl->ParseBlock("$block/pagination/page/previous");
                } else {
                    $tpl->SetBlock("$block/pagination/page/start");
                    $tpl->SetVariable('lbl', Jaws::t('PAGINATION_PREVIOUS'));
                    $tpl->ParseBlock("$block/pagination/page/start");
                }
            } elseif ($k == 'separator') {
                $tpl->SetBlock("$block/pagination/page/separator");
                $tpl->ParseBlock("$block/pagination/page/separator");
            } elseif ($k == 'current') {
                $tpl->SetBlock("$block/pagination/page/current");
                $tpl->SetVariable('lbl', $v);
                $tpl->SetVariable('url', $pageURL);
                $tpl->ParseBlock("$block/pagination/page/current");
            } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                $tpl->SetBlock("$block/pagination/page/number");
                $tpl->SetVariable('lbl', $v);
                $tpl->SetVariable('url', $pageURL);
                $tpl->ParseBlock("$block/pagination/page/number");
            }
            $tpl->ParseBlock("$block/pagination/page");
        }

        $tpl->ParseBlock("$block/pagination");
        return $tpl->Get();
    }


    /**
     * Get navigate pagination
     *
     * @access  public
     * @param   int     $current    Current page number
     * @param   int     $ipp        Item per page
     * @param   int     $total      Total items
     * @param   string  $action     Gadget action name
     * @param   array   $params     Action params array
     * @param   string  $label      Total label
     * @param   string  $gadget     Gadget name
     * @return  array   Pager navigation array
     */
    function xpagination($current, $ipp, $total, $action, $params = array(), $label = '', $gadget = '')
    {
        $pager = $this->GetPagerNumbered($current, $ipp, $total);
        if (empty($pager)) {
            return false;
        }

        return array(
            'gadget' => $this->gadget->name,
            'action' => $action,
            'label'  => $label,
            'pager'  => $pager
        );
    }

    /**
     * Get pager numbered links
     *
     * @access  public
     * @param   int     $page      Current page number
     * @param   int     $page_size Entries count per page
     * @param   int     $total     Total entries count
     * @return  array   array with numbers of pages
     */
    private function GetPagerNumbered($page, $page_size, $total)
    {
        $tail = 1;
        $paginator_size = 4;
        $pages = array();
        if ($page_size == 0) {
            return $pages;
        }

        $npages = ceil($total / $page_size);

        if ($npages < 2) {
            return $pages;
        }

        // Previous
        if ($page == 1) {
            $pages['previous'] = false;
        } else {
            $pages['previous'] = $page - 1;
        }

        if ($npages <= ($paginator_size + $tail)) {
            for ($i = 1; $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } elseif ($page < $paginator_size) {
            for ($i = 1; $i <= $paginator_size; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator'] = true;

            for ($i = $npages - $paginator_size + ($tail - 1); $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } else {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator'] = true;

            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        }

        // Next
        if ($page == $npages) {
            $pages['next'] = false;
        } else {
            $pages['next'] = $page + 1;
        }

        $pages['total'] = $total;

        return $pages;
    }

}