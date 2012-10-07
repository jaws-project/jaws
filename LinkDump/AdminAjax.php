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
     */
    function LinkDumpAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML of groupForm
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
     * @return  string  XHTML of groupForm
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
     * @return  array   Link information
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
     * @return  array   Group information
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
     * @return  array   Group information
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
     * @return  boolean True on success and Jaws_Error on failure
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
     * @return  boolean True on success and Jaws_Error on failure
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
     * @return  boolean True on success and Jaws_Error on failure
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
     * @param   int     $id     Link ID
     * @param   string  $title  Link title
     * @param   string  $desc   Link description
     * @param   string  $url    Link URL
     * @param   string  $tags   Link's tags
     */
    function UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $this->CheckSession('LinkDump', 'ManageLinks');
        $this->_Model->UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a link
     * @access  public
     * @param   int $id     Link id
     * @param   int $gid    Group ID
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