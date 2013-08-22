<?php
/**
 * TMS (Theme Management System) Gadget Admin view
 *
 * @category   GadgetAdmin
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls Themes function
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Tms', 'AdminHTML', 'Themes');
        return $gadget->Themes();
    }
}