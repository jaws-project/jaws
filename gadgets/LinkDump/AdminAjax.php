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
     * @return  mixed   Link information array or false on error
     */
    function GetLink()
    {
        @list($id) = jaws()->request->getAll('post');
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
     * @return  mixed   Group information array or false one error
     */
    function GetGroups()
    {
        @list($gid) = jaws()->request->getAll('post');
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
     * @return  array   Group information array
     */
    function GetLinksList()
    {
        @list($gid) = jaws()->request->getAll('post');
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML', 'Links');
        return $gadget->GetLinksList($gid);
    }

    /**
     * Insert group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        @list($title, $fast_url, $limitation, $links_type, $order_type) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->InsertGroup($title, $fast_url, $limitation, $links_type, $order_type);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Insert link
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertLink()
    {
        @list($gid, $title, $url, $fast_url, $desc, $tags, $rank) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        @list($gid, $title, $fast_url, $limitation, $links_type, $order_type) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->UpdateGroup($gid, $title, $fast_url, $limitation, $links_type, $order_type);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Update a link
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateLink()
    {
        @list($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a link
     * 
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteLink()
    {
        @list($id, $gid, $rank) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Links');
        $model->DeleteLink($id, $gid, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        @list($gid) = jaws()->request->getAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminModel', 'Groups');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}