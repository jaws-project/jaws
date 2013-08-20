<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Admin gadget display
     *
     * @access  public
     * @return  string   XHTML template
     */
    function Admin()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Emblems', 'AdminHTML', 'Emblems');
        return $gadget->Emblems();
    }
}