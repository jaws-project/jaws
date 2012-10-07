<?php
/**
 * Skeleton Gadget - An example gadget to be used by gadget developers
 *
 * @category   Gadget
 * @package    Skeleton
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SkeletonHTML extends Jaws_GadgetHTML
{
    /**
     * Executes the default action
     *
     * @access  public
     * @return  string
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Skeleton', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}