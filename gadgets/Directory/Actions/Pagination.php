<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

class Directory_Actions_Pagination extends Jaws_Gadget_Action
{
    /**
     * Builds page navigation for given records
     *
     * @access public
     */
    function Pagination($page, $limit, $total, $action, $params = array())
    {
        $prevpage = $page - 1;
        $nextpage = $page + 1;
        $pagesCount = ($total % $limit)? floor($total / $limit) + 1 : floor($total / $limit);
        if ($pagesCount <= 1) {
            return '';
        }

        $pagination = '';
        $pageNext  = _t('DIRECTORY_PAGE_NEXT');
        $pageLast  = _t('DIRECTORY_PAGE_LAST');
        $pageFirst = _t('DIRECTORY_PAGE_FIRST');
        $pagePrev  = _t('DIRECTORY_PAGE_PREVIOUS');

        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('pagination');

        // First
        if ($page > 1) {
            unset($params['page']);
            $url = $this->gadget->urlMap($action, $params);
            $tpl->SetBlock('pagination/first');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('first', $pageFirst);
            $tpl->ParseBlock('pagination/first');
        }

        // Previous
        if ($page > 1) {
            if ($page != 2) {
                $params['page'] = $page - 1;
            }
            $url = $this->gadget->urlMap($action, $params);
            $tpl->SetBlock('pagination/prev');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('previous', $pagePrev);
            $tpl->ParseBlock('pagination/prev');
        }

        // Next
        if ($page < $pagesCount) {
            $params['page'] = $page + 1;
            $url = $this->gadget->urlMap($action, $params);
            $tpl->SetBlock('pagination/next');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('next', $pageNext);
            $tpl->ParseBlock('pagination/next');
        }

        // Last
        if ($page < $pagesCount) {
            $params['page'] = $pagesCount;
            $url = $this->gadget->urlMap($action, $params);
            $tpl->SetBlock('pagination/last');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('last', $pageLast);
            $tpl->ParseBlock('pagination/last');
        }

        // Page Numbers
        $range = 5; // TODO: can be a registry key
        $start = ($page - $range <= 1)? 1 : $page - $range;
        $end = ($page + $range >= $pagesCount)? $pagesCount : $page + $range;
        for ($i = $start; $i <= $end; $i++) {
            $num = Jaws_Gadget::ParseText($i, 'Directory', false);
            $tpl->SetBlock('pagination/page');
            if ($i == $page || ($page == 0 && $i == 1)) {
                $tpl->SetVariable('link', "<span>$num</span>");
                $tpl->SetVariable('current', 'current');
            } else {
                if ($i == 1) {
                    unset($params['page']);
                } else {
                    $params['page'] = $i;
                }
                $url = $this->gadget->urlMap($action, $params);
                $link =& Piwi::CreateWidget('Link', $num, $url);
                $tpl->SetVariable('link', $link->Get());
                $tpl->SetVariable('current', '');
            }
            $tpl->ParseBlock('pagination/page');
        }

        $tpl->ParseBlock('pagination');
        return $tpl->Get();
    }
}