<?php
/**
 * Quotes Gadget
 *
 * @category   Gadget
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class QuotesHTML extends Jaws_GadgetHTML
{
    /**
     * Calls default action(display)
     *
     * @access       public
     * @return       template content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Quotes', 'LayoutHTML');
        return $layoutGadget->RecentQuotes();
    }

    /**
     * Print the recent quotes
     *
     * @access  public
     * @return  template content
     */
    function RecentQuotes()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Quotes', 'LayoutHTML');
        return $layoutGadget->RecentQuotes();
    }

    /**
     * Displays quotes by group
     *
     * @access public
     * @return template content
     */
    function ViewGroupQuotes()
    {
        $request =& Jaws_Request::getInstance();
        $gid = $request->get('id', 'get');
        $layoutGadget = $GLOBALS['app']->LoadGadget('Quotes', 'LayoutHTML');
        return $layoutGadget->Display($gid);
    }

    /**
     * Displays quotes by group in standalone mode
     *
     * @access public
     * @return template content
     */
    function QuotesByGroup()
    {
        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        header($xss->filter($_SERVER['SERVER_PROTOCOL'])." 200 OK");
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
        $request =& Jaws_Request::getInstance();
        $qid = $request->get('id', 'get');
        $model = $GLOBALS['app']->LoadGadget('Quotes', 'Model');
        $quote = $model->GetQuote($qid);
        if (Jaws_Error::IsError($quote) || !isset($quote['id']) || !$quote['published']) {
            return '';
        }
        $group = $model->GetGroup($quote['gid']);
        if (Jaws_Error::IsError($group) || !isset($group['id']) || !$group['published']) {
            return '';
        }

        $this->SetTitle($quote['title']);
        $tpl = new Jaws_Template('gadgets/Quotes/templates/');
        $tpl->Load('Quote.html');
        $tpl->SetBlock('quote');

        $tpl->SetVariable('title', $group['title']);
        $tpl->SetVariable('quote_title', $quote['title']);
        $tpl->SetVariable('quotation', $this->ParseText($quote['quotation'], 'Quotes'));

        $tpl->ParseBlock('quote');
        return $tpl->Get();
    }

}