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
class LinkDump_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetGroupUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Groups');
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
        $gadget = $this->gadget->action->loadAdmin('Links');
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
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Links');
        $link = $model->GetLink($id);
        if (Jaws_Error::IsError($link) || empty($link)) {
            return false; //Maybe handled one day
        }

        if (isset($link['tags'])) {
            $link['tags'] = implode(', ', $link['tags']);
        }
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
        @list($gid) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Groups');
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
        @list($gid) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Links');
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
        @list($title, $fast_url, $limitation, $links_type, $order_type) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $this->gadget->model->loadAdmin('Groups');
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
        @list($gid, $title, $url, $fast_url, $desc, $tags, $rank) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $this->gadget->model->loadAdmin('Links');
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
        @list($gid, $title, $fast_url, $limitation, $links_type, $order_type) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $this->gadget->model->loadAdmin('Groups');
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
        @list($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $this->gadget->model->loadAdmin('Links');
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
        @list($id, $gid, $rank) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageLinks');
        $model = $this->gadget->model->loadAdmin('Links');
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
        @list($gid) = jaws()->request->fetchAll('post');
        $this->gadget->CheckPermission('ManageGroups');
        $model = $this->gadget->model->loadAdmin('Groups');
        $model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}