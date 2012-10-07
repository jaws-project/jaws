<?php
/**
 * LinkDump AJAX API
 *
 * @category   Ajax
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDumpAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   Jaws_Model  $model  Jaws_Model reference
     */
    function LinkDumpAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetGroupUI()
    {
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML');
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
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML');
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
        $linkInfo = $this->_Model->GetLink($id);
        if (Jaws_Error::IsError($linkInfo)) {
            return false; //Maybe handled one day
        }

        $linkInfo['tags'] = implode(', ', $linkInfo['tags']);
        return $linkInfo;
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
        $groupInfo = $this->_Model->GetGroup($gid);
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
        $gadget = $GLOBALS['app']->LoadGadget('LinkDump', 'AdminHTML');
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
     * @return  array   response array
     */
    function InsertGroup($title, $fast_url, $limitation, $links_type, $order_type)
    {
        $this->CheckSession('LinkDump', 'ManageGroups');
        $this->_Model->InsertGroup($title, $fast_url, $limitation, $links_type, $order_type);

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
     * @return  array   response array
     */
    function InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $this->CheckSession('LinkDump', 'ManageLinks');
        $this->_Model->InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank);

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
     * @return  array   response array
     */
    function UpdateGroup($gid, $title, $fast_url, $limitation, $links_type, $order_type)
    {
        $this->CheckSession('LinkDump', 'ManageGroups');
        $this->_Model->UpdateGroup($gid, $title, $fast_url, $limitation, $links_type, $order_type);

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
     * @return  array   response array
     */
    function UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $this->CheckSession('LinkDump', 'ManageLinks');
        $this->_Model->UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a link
     * 
     * @access  public
     * @param   int     $id         Link id
     * @param   int     $gid        Group ID
     * @param   string  $rank
     * @return  array   response array
     */
    function DeleteLink($id, $gid, $rank)
    {
        $this->CheckSession('LinkDump', 'ManageLinks');
        $this->_Model->DeleteLink($id, $gid, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @param   int     $gid   group ID
     * @return  array   Response (notice or error)
     */
    function DeleteGroup($gid)
    {
        $this->CheckSession('LinkDump', 'ManageGroups');
        $this->_Model->DeleteGroup($gid);

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}