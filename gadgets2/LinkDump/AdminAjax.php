<?php
/**
 * LinkDump AJAX API
 *
 * @category   Ajax
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function LinkDump_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetGroupUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML', 'Groups');
        return $gadget->GetGroupUI();
    }

    /**
     * Returns the link form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetLinkUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML', 'Links');
        return $gadget->GetLinkUI();
    }

    /**
     * Get information of a Link
     *
     * @access  public
     * @param   int     $id     Link id
     * @return  mixed   Link information array or false on error
     */
    function GetLink($id)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Links');
        $link = $model->GetLink($id);
        if (Jaws_Error::IsError($link) || empty($link)) {
            return false; //Maybe handled one day
        }

        $link['tags'] = implode(', ', $link['tags']);
        return $link;
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  mixed   Group information array or false one error
     */
    function GetGroups($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
        $groupInfo = $model->GetGroup($gid);
        if (Jaws_Error::IsError($groupInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $groupInfo;
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   Group information array
     */
    function GetLinksList($gid)
    {
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML', 'Links');
        return $gadget->GetLinksList($gid);
    }

    /**
     * Insert group
     *
     * @access  public
     * @param   string  $title      group title
     * @param   string  $fast_url
     * @param   string  $limitation
     * @param   string  $links_type
     * @param   string  $order_type
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $fast_url, $limitation, $links_type, $order_type)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->InsertGroup($title, $fast_url, $limitation, $links_type, $order_type);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert link
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   string  $title      link title
     * @param   string  $url        url address
     * @param   string  $fast_url
     * @param   string  $desc       description
     * @param   string  $tags
     * @param   string  $rank
     * @return  array   Response array (notice or error)
     */
    function InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update group
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   string  $title      group title
     * @param   string  $fast_url
     * @param   string  $limitation
     * @param   string  $links_type
     * @param   string  $order_type
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $fast_url, $limitation, $links_type, $order_type)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->UpdateGroup($gid, $title, $fast_url, $limitation, $links_type, $order_type);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Update a link
     * @access  public
     * @param   int     $id         Link ID
     * @param   int     $gid        group ID
     * @param   string  $title      Link title
     * @param   string  $url        Link URL
     * @param   string  $fast_url   
     * @param   string  $desc       Link description
     * @param   string  $tags       Link's tags
     * @param   string  $rank
     * @return  array   Response array (notice or error)
     */
    function UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a link
     * 
     * @access  public
     * @param   int     $id         Link id
     * @param   int     $gid        Group ID
     * @param   string  $rank
     * @return  array   Response array (notice or error)
     */
    function DeleteLink($id, $gid, $rank)
    {
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->DeleteLink($id, $gid, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @param   int     $gid   group ID
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}