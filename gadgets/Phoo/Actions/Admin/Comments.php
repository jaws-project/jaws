<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Comments extends Phoo_Actions_Admin_Default
{
    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML template content of a datagrid
     */
    function CommentsDatagrid()
    {
        $cHtml = Jaws_Gadget::getInstance('Comments')->action->loadAdmin('Comments');
        return $cHtml->Get($this->gadget->name);
    }

    /**
     * Builds the data (an array) of filtered comments
     *
     * @access  public
     * @param   int     $limit   Limit of comments
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  array   Filtered Comments
     */
    function CommentsData($limit = 0, $filter = '', $search = '', $status = '')
    {
        $cHtml = Jaws_Gadget::getInstance('Comments')->action->loadAdmin('Comments');
        return $cHtml->GetDataAsArray(
            'phoo',
            $filter,
            $search,
            $status,
            $limit
        );
    }

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
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo');
        }

        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddScriptLink('gadgets/Comments/Resources/script.js');

        $cHTML = Jaws_Gadget::getInstance('Comments')->action->loadAdmin('Comments');
        return $cHTML->Comments('phoo', $this->MenuBar('ManageComments'));
    }

}