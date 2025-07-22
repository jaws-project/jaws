<?php
/**
 * Search results actions
 *
 * @category    GadgetLayout
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Actions_Results extends Jaws_Gadget_Action
{
    /**
     * Displays search results
     *
     * @access  public
     * @return  string  XHTML content of search results
     */
    function Results()
    {
        $reqPhrases = $this->gadget->request->fetch(
            array('all', 'exact', 'least', 'exclude'),
            'get'
        );
        $reqOptions = $this->gadget->request->fetch(
            array('gadgets', 'date', 'page'),
            'get'
        );
        $reqOptions['page'] = (int)($reqOptions['page']?? 1);
        $reqOptions['page'] = ($reqOptions['page'] <= 0)? 1 : $reqOptions['page'];

        $reqOptions['limit'] = (int)$this->gadget->registry->fetch('results_limit');
        $reqOptions['limit'] = $reqOptions['limit']? $reqOptions['limit'] : 10;

        $assigns = array();
        try {
            // parse search query
            $reqPhrases = $this->gadget->model->load('Search')->parseSearchQuery($reqPhrases);
            if (empty($reqPhrases)) {
                $min_key_len = $this->gadget->registry->fetch('min_key_len');
                throw new Exception($this::t('STRING_TOO_SHORT', $min_key_len));
            }

            $reqOptions['date'] = $reqOptions['date']?? ['anytime'];
            if (!in_array(
                $reqOptions['date'],
                ['past_1month', 'past_2month', 'past_3month', 'past_6month', 'past_1year', 'anytime'])
            ) {
                $reqOptions['date'] = ['anytime'];
            }

            $assigns['result'] = $this->gadget->model->load('Search')->Search($reqPhrases, $reqOptions);
            $assigns['result']['items'] = array_slice(
                $assigns['result']['items'],
                ($reqOptions['page'] - 1) * $reqOptions['limit'],
                $reqOptions['limit']
            );
            $assigns['phrases'] = $this->gadget->model->load('Search')->implodeSearch($reqPhrases);

            $assigns['pagination'] = $this->gadget->action->load('PageNavigation')->xpagination(
                array(
                    'pages' => true,
                    'page'  => $reqOptions['page'],
                    'limit' => $reqOptions['limit'],
                    'total' => $assigns['result']['total'],
                ),
                'Results',
                $reqPhrases
            );

            // meta description
            $this->description = $this::t('results_subtitle', $assigns['result']['total'], $assigns['phrases']);

        } catch (Exception $e) {
            $assigns['result']['error'] = $e->getMessage();
            // meta description
            $this->description = $assigns['result']['error'];

        } finally {
            // page title
            $this->title = $this::t('results');
            return $this->gadget->template->xLoad('Results.html')->render($assigns);
        }
    }

}