<?php
/**
 * Glossary Gadget
 *
 * @category   Gadget
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_HTML extends Jaws_Gadget_HTML
{
    /**
     * Runs the default action
     *
     * @access  public
     * @return  string  HTML content of Default action
     */
    function DefaultAction()
    {
        $HTML = $GLOBALS['app']->LoadGadget('Glossary', 'HTML', 'Term');
        return $HTML->ViewTerms();
    }
}