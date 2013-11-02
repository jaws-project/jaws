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
class Search_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Displays gadget administration section
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Admin()
    {
        $gadgetHTML = $this->gadget->loadAdminAction('Settings');
        return $gadgetHTML->Settings();
    }
}