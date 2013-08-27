<?php
/**
 * Blocks Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Creates and prints the administration template
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function Admin()
    {
        $gadgetHTML = $GLOBALS['app']->LoadGadget('Blocks', 'AdminHTML', 'Block');
        return $gadgetHTML->Block();

    }
}