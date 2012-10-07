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
class VisitCounterHTML extends Jaws_GadgetHTML
{
    /**
     * Runs the display action if none is specified.
     *
     * @access public
     * @return boolean HTML content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('VisitCounter', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}