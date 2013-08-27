<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default Action
     *
     * @access  public
     * @return  string  XHTML content of DefaultAction
     */
    function DefaultAction()
    {
        $this->SetTitle(_t('CONTACT_US'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('Contact', 'LayoutHTML');
        return $layoutGadget->Display();
    }
}