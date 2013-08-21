<?php
/**
 * Shoutbox Gadget
 *
 * @category   Gadget
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $HTML = $GLOBALS['app']->LoadGadget('Shoutbox', 'HTML', 'Message');
        return $HTML->Display();
    }

 }
