<?php
/**
 * Quotes Gadget
 *
 * @category   Gadget
 * @package    Quotes
 */
class Quotes_Actions_Quotes extends Jaws_Gadget_Action
{
    /**
     * Recent quotes
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function recentQuotes()
    {
        $page = $this->gadget->request->fetch('page:integer', 'get');
        $page = empty($page) ? 1 : (int)$page;

        $assigns = array();

        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();

        $limit = 10;
        $filters = array(
            'published' => true,
            'ftime' => time(),
            'ttime' => time(),
            'classification' => array($this->getCurrentUserClassification(), '<='),
        );
        $assigns['quotes'] = $this->gadget->model->load('Quotes')->list(
            $filters,
            $limit,
            $limit * ($page - 1)
        );
        $total = $this->gadget->model->load('Quotes')->count($filters);
        $assigns['pagination'] = $this->gadget->action->load('PageNavigation')->xpagination(
            $page,
            $limit,
            $total,
            'recentQuotes'
        );
        return $this->gadget->template->xLoad('Quotes.html')->render($assigns);
    }

    /**
     * View a quote
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function viewQuote()
    {
        $id = (int)$this->gadget->request->fetch('id:integer', 'get');
        $quote = $this->gadget->model->load('Quotes')->get($id);
        if (Jaws_Error::IsError($quote)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($quote) || !$quote['published'] || (!empty($quote['ftime']) && $quote['ftime'] < time()) ||
            (!empty($quote['ttime']) && $quote['ttime'] >= time())) {
            return Jaws_HTTPError::Get(404);
        }
        if ($quote['classification'] > $this->getCurrentUserClassification()) {
            return Jaws_HTTPError::Get(403);
        }

        $assigns = array();

        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();

        $assigns['quote'] = $quote;
        return $this->gadget->template->xLoad('Quote.html')->render($assigns);
    }


    /**
     * Get current user classification access
     *
     * @access  public
     * @return  int
     */
    function getCurrentUserClassification()
    {
        $classification = Quotes_Info::CLASSIFICATION_TYPE_PUBLIC;
        if ($this->app->session->user->logged) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_INTERNAL;
        }
        if ($this->gadget->GetPermission('ClassificationRestricted')) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_RESTRICTED;
        }
        if ($this->gadget->GetPermission('ClassificationConfidential')) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_CONFIDENTIAL;
        }

        return $classification;
    }
}