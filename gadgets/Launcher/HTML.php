<?php
/**
 * Launcher Gadget
 *
 * @category   Gadget
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Launcher_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $objHTML = $GLOBALS['app']->LoadGadget('Launcher', 'HTML', 'Execute');
        return $objHTML->Execute('defaultscript');
    }

}