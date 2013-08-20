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
class Quotes_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access       public
     * @return       template content
     */
    function DefaultAction()
    {
        $HTML = $GLOBALS['app']->LoadGadget('Quotes', 'HTML', 'Quotes');
        return $HTML->RecentQuotes();
    }


}