<?php
/**
 * Faq Gadget
 *
 * @category   Gadget
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(View)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $HTML = $GLOBALS['app']->LoadGadget('Faq', 'HTML', 'Question');
        return $HTML->View();
    }
}