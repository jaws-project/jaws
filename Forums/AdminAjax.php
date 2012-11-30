<?php
/**
 * Forums AJAX API
 *
 * @category   Ajax
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumsAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Get information of a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  mixed   Group information or False on error
     */
    function GetGroup($gid)
    {
        $this->CheckSession('Forum', 'default');
        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Groups');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Get information of a forum
     *
     * @access  public
     * @param   int     $fid    Forum ID
     * @return  mixed   Forum information or False on error
     */
    function GetForum($fid)
    {
        $this->CheckSession('Forum', 'default');
        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $forum = $fModel->GetForum($fid);
        if (Jaws_Error::IsError($forum)) {
            return false; //we need to handle errors on ajax
        }

        return $forum;
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetGroupUI()
    {
        $this->CheckSession('Forum', 'default');
        $gHTML = $GLOBALS['app']->LoadGadget('Forums', 'AdminHTML', 'Group');
        return $gHTML->GetGroupUI();
    }

    /**
     * Returns the forum form
     *
     * @access  public
     * @return  string  XHTML template content of groupForm
     */
    function GetForumUI()
    {
        $this->CheckSession('Forum', 'default');
        $fHTML = $GLOBALS['app']->LoadGadget('Forums', 'AdminHTML', 'Forum');
        return $fHTML->GetForumUI();
    }

    /**
     * Insert forum
     *
     * @access  public
     * @param   int     $gid            group ID
     * @param   string  $title          forum title
     * @param   string  $description    forum description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked         is locked
     * @param   bool    $published      is published
     * @return  array   Response array (notice or error)
     */
    function InsertForum($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'AdminModel', 'Forums');
        $res = $fModel->InsertForum($gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_ERROR_FORUM_CREATED'),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FORUMS_NOTICE_FORUM_CREATED'),
            RESPONSE_NOTICE,
            $res
        );
    }

    /**
     * Update forum
     *
     * @access  public
     * @param   int     $fid            forum ID
     * @param   int     $gid            group ID
     * @param   string  $title          forum title
     * @param   string  $description    forum description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'AdminModel', 'Forums');
        $res = $fModel->UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FORUMS_ERROR_FORUM_UPDATED'),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('FORUMS_NOTICE_FORUM_UPDATED'),
                                                     RESPONSE_NOTICE);
    }

    /**
     * Insert group
     *
     * @access  public
     * @param   string  $title          group title
     * @param   string  $description    group description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function InsertGroup($title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'AdminModel', 'Groups');
        $gid = $gModel->InsertGroup($title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($gid)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_ERROR_GROUP_CREATED'),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FORUMS_NOTICE_GROUP_CREATED'),
            RESPONSE_NOTICE,
            $gid
        );
    }

    /**
     * Update group
     *
     * @access  public
     * @param   int     $gid            group ID
     * @param   string  $title          group title
     * @param   string  $description    group description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $gModel = $GLOBALS['app']->LoadGadget('Forums', 'AdminModel', 'Groups');
        $res = $gModel->UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_ERROR_GROUP_UPDATED'),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FORUMS_NOTICE_GROUP_UPDATED'),
            RESPONSE_NOTICE
        );
    }

}