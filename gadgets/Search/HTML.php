<?php
/**
 * Search Gadget
 *
 * @category   Gadget
 * @package    Search
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SearchHTML extends Jaws_GadgetHTML
{
    /**
     * Call SearchBox method
     *
     * @access  public
     * @return  string The Searchable(magic) box
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Search', 'LayoutHTML');
        return $layoutGadget->AdvancedBox();
    }

    /**
     * Display a search box
     *
     * @access  public
     * @return  string  Search box (XHTML output)
     */
    function Box()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Search', 'LayoutHTML');
        return $layoutGadget->Box(true);
    }

    /**
     * Display a simple search box
     *
     * @access  public
     * @return  string  Simple search box (XHTML output)
     */
    function SimpleBox()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Search', 'LayoutHTML');
        return $layoutGadget->SimpleBox();
    }

    /**
     * Display the advanced search box
     *
     * @access  public
     * @return  string  Advanced search box (XHTML output)
     */
    function AdvancedBox()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Search', 'LayoutHTML');
        return $layoutGadget->AdvancedBox();
    }

    /**
     * Display search results
     *
     * @access  public
     * @return  string HTML content of search result
     */
    function Results()
    {
        $tpl = new Jaws_Template('gadgets/Search/templates/');
        $tpl->Load('Results.html');
        $tpl->SetBlock('results');
        $tpl->SetVariable('title', _t('SEARCH_RESULTS'));

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('gadgets', 'all', 'exact', 'least', 'exclude', 'date'), 'get');
        $page = $request->get('page', 'get');
        if (is_null($page) || !is_numeric($page) || $page <= 0 ) {
            $page = 1;
        }

        $searchable = false;
        $model = $GLOBALS['app']->LoadGadget('Search', 'Model');
        $options = $model->parseSearch($post, $searchable);
        if ($searchable) {
            $items = $model->Search($options);
        }

        $query_string = '?gadget=Search&action=Results';
        foreach ($post as $option => $value) {
            if (!empty($value)) {
                $query_string .= '&' . $option . '=' . $value;
            }
        }
        $query_string .= '&page=';

        $results_limit = (int) $GLOBALS['app']->Registry->Get('/gadgets/Search/results_limit');
        if (empty($results_limit)) {
            $results_limit = 10;
        }

        if (!$searchable) {
            $tpl->SetBlock('results/notfound');
            $min_key_len = $GLOBALS['app']->Registry->Get('/gadgets/Search/min_key_len');
            $tpl->SetVariable('message', _t('SEARCH_STRING_TOO_SHORT', $min_key_len));
            $tpl->ParseBlock('results/notfound');
        } elseif (count($items) > 1) {
            $tpl->SetVariable('navigation',
                              $this->GetNumberedPageNavigation($page,
                                                                $results_limit,
                                                                $items['_totalItems'],
                                                                $query_string));
            if (count($items) > 2) {
                $tpl->SetBlock('results/subtitle');
                $tpl->SetVariable('text', _t('SEARCH_RESULTS_SUBTITLE',
                                             $items['_totalItems'],
                                             $model->implodeSearch()));
                $tpl->ParseBlock('results/subtitle');
            }
            unset($items['_totalItems']);

            $date = $GLOBALS['app']->loadDate();
            $max_result_len = (int)$GLOBALS['app']->Registry->Get('/gadgets/Search/max_result_len');
            if (empty($max_result_len)) {
                $max_result_len = 500;
            }

            $item_counter = 0;
            foreach ($items as $gadget => $result) {
                $tpl->SetBlock('results/gadget');
                $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                $tpl->SetVariable('gadget_result', _t('SEARCH_RESULTS_IN_GADGETS',
                                                      count($result),
                                                      $model->implodeSearch(),
                                                      $info->GetName()));
                $tpl->ParseBlock('results/gadget');
                foreach ($result as $item) {
                    $item_counter++;
                    if ($item_counter <= ($page-1)*$results_limit || $item_counter > $page*$results_limit) {
                        continue;
                    }
                    $tpl->SetBlock('results/item');
                    $tpl->SetVariable('title',  $item['title']);
                    $tpl->SetVariable('url',    $item['url']);
                    $tpl->SetVariable('target', (isset($item['outer']) && $item['outer'])? '_blank' : '_self');
                    $tpl->SetVariable('image',  $item['image']);

                    if (!isset($item['parse_text']) || $item['parse_text']) {
                        $item['snippet'] = Jaws_Gadget::ParseText($item['snippet'], $gadget);
                    }
                    if (!isset($item['strip_tags']) || $item['strip_tags']) {
                        $item['snippet'] = strip_tags($item['snippet']);
                    }
                    $item['snippet'] = $GLOBALS['app']->UTF8->substr($item['snippet'], 0, $max_result_len);

                    $tpl->SetVariable('snippet', $item['snippet']);
                    $tpl->SetVariable('date', $date->Format($item['date']));
                    $tpl->ParseBlock('results/item');
                }
            }
        } else {
            $tpl->SetBlock('results/notfound');
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            header($xss->filter($_SERVER['SERVER_PROTOCOL'])." 404 Not Found");
            $tpl->SetVariable('message', _t('SEARCH_NO_RESULTS', $model->implodeSearch()));
            $tpl->ParseBlock('results/notfound');
        }
        $tpl->ParseBlock('results');

        return $tpl->Get();
    }

    /**
     * Get page navigation links
     * @access  private
     */
    function GetNumberedPageNavigation($page, $page_size, $total, $query_string, $id = null)
    {
        $tpl = new Jaws_Template('gadgets/Search/templates/');
        $tpl->Load('PageNavigation.html');
        $tpl->SetBlock('pager');

        $model = $GLOBALS['app']->LoadGadget('Search', 'Model');
        $pager = $model->GetEntryPagerNumbered($page, $page_size, $total);
        if (count($pager) > 0) {
            $tpl->SetBlock('pager/numbered-navigation');
            $tpl->SetVariable('total', _t('SEARCH_RESULT_COUNT', $pager['total']));

            $pager_view = '';
            foreach ($pager as $k => $v) {
                $tpl->SetBlock('pager/numbered-navigation/item');
                if ($k == 'next') {
                    if ($v) {
                        $tpl->SetBlock('pager/numbered-navigation/item/next');
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXTPAGE'));
                        $url = $query_string . $v;
                        $tpl->SetVariable('url_next', $url);
                        $tpl->ParseBlock('pager/numbered-navigation/item/next');
                    } else {
                        $tpl->SetBlock('pager/numbered-navigation/item/no_next');
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXTPAGE'));
                        $tpl->ParseBlock('pager/numbered-navigation/item/no_next');
                    }
                } elseif ($k == 'previous') {
                    if ($v) {
                        $tpl->SetBlock('pager/numbered-navigation/item/previous');
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUSPAGE'));
                        $url = $query_string . $v;
                        $tpl->SetVariable('url_previous', $url);
                        $tpl->ParseBlock('pager/numbered-navigation/item/previous');
                    } else {
                        $tpl->SetBlock('pager/numbered-navigation/item/no_previous');
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUSPAGE'));
                        $tpl->ParseBlock('pager/numbered-navigation/item/no_previous');
                    }
                } elseif ($k == 'separator1' || $k == 'separator2') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_separator');
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_separator');
                } elseif ($k == 'current') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_current');
                    $url = $query_string . $v;
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_current');
                } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_number');
                    $url = $query_string . $v;
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_number');
                }
                $tpl->ParseBlock('pager/numbered-navigation/item');
            }

            $tpl->ParseBlock('pager/numbered-navigation');
        }

        $tpl->ParseBlock('pager');

        return $tpl->Get();
    }
}