<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_Navigation
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;


    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
    }


    /**
     * Get navigate pagination
     *
     * @access  public
     * @param   int     $page       page number
     * @param   int     $page_size
     * @param   int     $total
     * @param   string  $action     action
     * @param   array   $params     params array
     * @return  string  XHTML template content
     */
    function pagination($page, $page_size, $total, $action, $params = array())
    {
        $pager = $this->GetPagerNumbered($page, $page_size, $total);
        var_dump($pager);
        if (empty($pager)) {
            return '';
        }

        $tpl = new Jaws_Template();
        $tpl->Load('Navigation.html', 'include/Jaws/Resources');
        $tpl->SetBlock('pagination');
        //$tpl->SetVariable('total', _t('GLOBAL_PAGINATION_COUNT', $pager['total']));

        foreach ($pager as $k => $v) {
            $tpl->SetBlock('pagination/page');
            $params['page'] = $v;
            $pageURL = $this->gadget->urlMap($action, $params);
            if ($k == 'next') {
                if ($v) {
                    $tpl->SetBlock('pagination/page/next');
                    $tpl->SetVariable('lbl', _t('GLOBAL_PAGINATION_NEXT'));
                    $tpl->SetVariable('url', $pageURL);
                    $tpl->ParseBlock('pagination/page/next');
                } else {
                    $tpl->SetBlock('pagination/page/stop');
                    $tpl->SetVariable('lbl', _t('GLOBAL_PAGINATION_NEXT'));
                    $tpl->ParseBlock('pagination/page/stop');
                }
            } elseif ($k == 'previous') {
                if ($v) {
                    $tpl->SetBlock('pagination/page/previous');
                    $tpl->SetVariable('lbl', _t('GLOBAL_PAGINATION_PREVIOUS'));
                    $tpl->SetVariable('url', $pageURL);
                    $tpl->ParseBlock('pagination/page/previous');
                } else {
                    $tpl->SetBlock('pagination/page/start');
                    $tpl->SetVariable('lbl', _t('GLOBAL_PAGINATION_PREVIOUS'));
                    $tpl->ParseBlock('pagination/page/start');
                }
            } elseif ($k == 'separator1' || $k == 'separator2') {
                $tpl->SetBlock('pagination/page/separator');
                $tpl->ParseBlock('pagination/page/separator');
            } elseif ($k == 'current') {
                $tpl->SetBlock('pagination/page/current');
                $tpl->SetVariable('lbl', $v);
                $tpl->SetVariable('url', $pageURL);
                $tpl->ParseBlock('pagination/page/current');
            } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                $tpl->SetBlock('pagination/page/number');
                $tpl->SetVariable('lbl', $v);
                $tpl->SetVariable('url', $pageURL);
                $tpl->ParseBlock('pagination/page/number');
            }
            $tpl->ParseBlock('pagination/page');
        }

        $tpl->ParseBlock('pagination');
        return $tpl->Get();
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

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

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

            $pages['separator1'] = true;

            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

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