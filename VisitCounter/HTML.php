<?php
/**
 * Visit Counter Gadget
 *
 * @category   Gadget
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls Display function if no action is specified
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('VisitCounter', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}