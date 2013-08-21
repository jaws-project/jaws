<?php
/**
 * Search Gadget Admin
 *
 * @category    Gadget Admin
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays gadget administration section
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Admin()
    {
        $gadgetHTML = $GLOBALS['app']->LoadGadget('Search', 'AdminHTML', 'Settings');
        return $gadgetHTML->Settings();
    }
}