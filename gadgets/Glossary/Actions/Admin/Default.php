<?php
/**
 * Glossary Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Manages the main functions of Glossary administration
     *
     * @access  public
     * @return  string  XHTML template Content
     */
    function Admin()
    {
        $gadgetHTML = $this->gadget->loadAdminAction('Term');
        return $gadgetHTML->Term();
    }

}