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
class Phoo_Actions_Admin_Comments extends Phoo_AdminAction
{
    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML template content of a datagrid
     */
    function CommentsDatagrid()
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminAction');
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
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminAction');
        return $cHtml->GetDataAsArray(
            'phoo',
            BASE_SCRIPT . '?gadget=Phoo&amp;action=EditComment&amp;id={id}',
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

        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'AdminAction');
        return $cHTML->Comments('phoo', $this->MenuBar('ManageComments'));
    }

}