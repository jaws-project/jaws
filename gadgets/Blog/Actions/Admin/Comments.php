<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Comments extends Blog_Actions_Admin_Default
{

    /**
     * Displays blog comments manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        if (!Jaws_Gadget::IsGadgetInstalled('Comments')) {
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog');
        }

        $cHTML = Jaws_Gadget::getInstance('Comments')->action->loadAdmin('Comments');
        return $cHTML->Comments($this->gadget->name, $this->MenuBar('ManageComments'));
    }

}