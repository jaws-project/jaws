<?php
/**
 * Search Gadget
 *
 * @category    Gadget
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls AdvancedBox method
     *
     * @access  public
     * @return  string  XHTML search form
     */
    function DefaultAction()
    {
        $objSearch = $GLOBALS['app']->LoadGadget('Search', 'HTML', 'Search');
        return $objSearch->AdvancedBox();
    }

}