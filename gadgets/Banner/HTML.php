<?php
/**
 * Banner Gadget
 *
 * @category   Gadget
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access    public
     * @return    string    XTHML template content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Banner', 'HTML', 'Banners');
        return $layoutGadget->Banners();
    }

}