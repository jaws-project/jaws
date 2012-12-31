<?php
/**
 * Emblems Gadget
 *
 * @category   Gadget
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_HTML extends Jaws_Gadget_HTML
{
    /**
     * Executes the default action
     *
     * @access  public
     * @return  string  XHTML template content
     * @see     Display()
     */
    function DefaultAction()
    {
        return $this->Display();
    }

    /**
     * Displays the emblems in our site
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function Display()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Emblems', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}