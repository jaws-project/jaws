<?php
/**
 * Skeleton Gadget - An example gadget to be used by gadget developers
 *
 * @category   Gadget
 * @package    Skeleton
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_HTML extends Jaws_Gadget_HTML
{
    /**
     * Executes the default action
     *
     * @access  public
     * @return  string  Jaws version
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Skeleton', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}