<?php
/**
 * Webcam Gadget
 *
 * @category   Gadget
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WebcamHTML extends Jaws_GadgetHTML
{
    /**
     * Default action to be run if none is defined.
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Webcam', 'LayoutHTML');
        return $layoutGadget->Display();
    }
}