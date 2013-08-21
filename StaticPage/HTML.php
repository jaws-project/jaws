<?php
/**
 * StaticPage Gadget
 *
 * @category   Gadget
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_HTML extends Jaws_Gadget_HTML
{
    /**
     * Excutes the default action, currently displaying the default page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        $gadget = $GLOBALS['app']->LoadGadget('StaticPage', 'HTML', 'Page');
        return $gadget->Page($this->gadget->registry->fetch('default_page'));
    }


}