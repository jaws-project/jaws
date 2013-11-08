<?php
/**
 * Quotes Gadget
 *
 * @category   Gadget
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Actions_Quotes extends Jaws_Gadget_Action
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayLayoutParams()
    {
        $result = array();
        $qModel = $this->gadget->model->load('Groups');
        $groups = $qModel->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => _t('QUOTES_QUOTE'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Prints the recent quotes
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RecentQuotes()
    {
        $group['id']          = 0;
        $group['title']       = _t('QUOTES_GROUPS_RECENT');
        $group['view_mode']   = $this->gadget->registry->fetch('last_entries_view_mode');
        $group['view_type']   = $this->gadget->registry->fetch('last_entries_view_type');
        $group['show_title']  = $this->gadget->registry->fetch('last_entries_show_title') == 'true';
        $group['limit_count'] = $this->gadget->registry->fetch('last_entries_limit');
        $group['random']      = $this->gadget->registry->fetch('last_entries_view_random') == 'true';

        $model = $this->gadget->model->load('Quotes');
        $quotes = $model->GetRecentQuotes($group['limit_count'], $group['random']);
        if (Jaws_Error::IsError($quotes)) {
            return false;
        }

        return $this->DisplayQuotes($group, $quotes);
    }

    /**
     * Displays quotes of the specified group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  string  XHTML template content
     */
    function Display($gid)
    {
        $qModel = $this->gadget->model->load('Quotes');
        $gModel = $this->gadget->model->load('Groups');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $quotes = $qModel->GetPublishedQuotes($gid, $group['limit_count'], $group['random']);
        if (Jaws_Error::IsError($quotes)) {
            return false;
        }

        return $this->DisplayQuotes($group, $quotes);
    }

    /**
     * Builds the template for displaying quotes
     *
     * @access  public
     * @param   array   $group      Group data array
     * @param   array   $quotes     List of quotes to be displayed
     * @return  string  XHTML template content
     */
    function DisplayQuotes(&$group, &$quotes)
    {
        if (empty($quotes)) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('Quotes.html');
        $tpl->SetBlock('quotes');
        $tpl->SetVariable('gid', $group['id']);
        if ($group['show_title']) {
            $tpl->SetBlock("quotes/title");
            $tpl->SetVariable('title', $group['title']);
            $tpl->ParseBlock("quotes/title");
        }
        $block = ($group['view_type']==0)? 'simple' : 'marquee';
        $tpl->SetBlock("quotes/$block");
        $tpl->SetVariable('marquee_direction', (($group['view_type']==2)? 'down' :
            (($group['view_type']==3)? 'left' :
                (($group['view_type']==4)? 'right' : 'up'))));

        foreach($quotes as $quote) {
            $tpl->SetBlock("quotes/$block/quote");
            if ($quote['show_title'] || ($group['view_mode'] == 0)) {
                $tpl->SetBlock("quotes/$block/quote/quote_title");
                $tpl->SetVariable('quote_title', $quote['title']);
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Quotes', 'ViewQuote', array('id' => $quote['id'])));
                $tpl->ParseBlock("quotes/$block/quote/quote_title");
            }
            if ($group['view_mode']!= 0) {
                $tpl->SetBlock("quotes/$block/quote/full_mode");
                $tpl->SetVariable('quotation', $this->gadget->ParseText($quote['quotation']));
                $tpl->ParseBlock("quotes/$block/quote/full_mode");
            }
            $tpl->ParseBlock("quotes/$block/quote");
        }
        $tpl->ParseBlock("quotes/$block");

        $tpl->ParseBlock('quotes');
        return $tpl->Get();
    }

    /**
     * Displays quotes by group in standalone mode
     *
     * @access  public
     * @return  XHTML template content
     */
    function QuotesByGroup()
    {
        header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 200 OK");
        return $this->ViewGroupQuotes();
    }

    /**
     * view quote(title and quotation)
     *
     * @access  public
     * @return  string
     */
    function ViewQuote()
    {
        $qid = jaws()->request->fetch('id', 'get');
        $qModel = $this->gadget->model->load('Quotes');
        $gModel = $this->gadget->model->load('Groups');
        $quote = $qModel->GetQuote($qid);
        if (Jaws_Error::IsError($quote) || empty($quote) || !$quote['published']) {
            return false;
        }
        $group = $gModel->GetGroup($quote['gid']);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $this->SetTitle($quote['title']);
        $tpl = $this->gadget->loadTemplate('Quote.html');
        $tpl->SetBlock('quote');

        $tpl->SetVariable('title', $group['title']);
        $tpl->SetVariable('quote_title', $quote['title']);
        $tpl->SetVariable('quotation', $this->gadget->ParseText($quote['quotation']));

        $tpl->ParseBlock('quote');
        return $tpl->Get();
    }
}