<?php
/**
 * ServerTime Gadget
 *
 * @category   Gadget
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTimeHTML extends Jaws_GadgetHTML
{
    /**
     * Executes the default action
     *
     * @access  public
     * @return  string  ServerTime
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('ServerTime', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}