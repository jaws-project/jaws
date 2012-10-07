<?php
/**
 * Forum AJAX API
 *
 * @category   Ajax
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function ForumAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   Group information
     */
    function GetGroup($gid)
    {
        $this->CheckSession('Forum', 'default');
        $gModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Groups');
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
     * @return  array   Forum information
     */
    function GetForum($fid)
    {
        $this->CheckSession('Forum', 'default');
        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'Model', 'Forums');
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
     * @return  string  XHTML of groupForm
     */
    function GetGroupUI()
    {
        $this->CheckSession('Forum', 'default');
        $gHTML = $GLOBALS['app']->LoadGadget('Forum', 'AdminHTML', 'Group');
        return $gHTML->GetGroupUI();
    }

    /**
     * Returns the forum form
     *
     * @access  public
     * @return  string  XHTML of groupForm
     */
    function GetForumUI()
    {
        $this->CheckSession('Forum', 'default');
        $fHTML = $GLOBALS['app']->LoadGadget('Forum', 'AdminHTML', 'Forum');
        return $fHTML->GetForumUI();
    }

    /**
     * Insert forum
     *
     * @access  public
     * @return  boolean True on success and Jaws_Error on failure
     */
    function InsertForum($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'AdminModel', 'Forums');
        $res = $fModel->InsertForum($gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FORUM_ERROR_FORUM_CREATED'),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('FORUM_NOTICE_FORUM_CREATED'),
                                                     RESPONSE_NOTICE,
                                                     $res);
    }

    /**
     * Update forum
     *
     * @access  public
     * @return  boolean True on success and Jaws_Error on failure
     */
    function UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $fModel = $GLOBALS['app']->LoadGadget('Forum', 'AdminModel', 'Forums');
        $res = $fModel->UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FORUM_ERROR_FORUM_UPDATED'),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('FORUM_NOTICE_FORUM_UPDATED'),
                                                     RESPONSE_NOTICE);
    }

    /**
     * Insert group
     *
     * @access  public
     * @return  boolean True on success and Jaws_Error on failure
     */
    function InsertGroup($title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $gModel = $GLOBALS['app']->LoadGadget('Forum', 'AdminModel', 'Groups');
        $res = $gModel->InsertGroup($title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FORUM_ERROR_GROUP_CREATED'),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('FORUM_NOTICE_GROUP_CREATED'),
                                                     RESPONSE_NOTICE,
                                                     $res);
    }

    /**
     * Update group
     *
     * @access  public
     * @return  boolean True on success and Jaws_Error on failure
     */
    function UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $this->CheckSession('Forum', 'ManageForums');
        $gModel = $GLOBALS['app']->LoadGadget('Forum', 'AdminModel', 'Groups');
        $res = $gModel->UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FORUM_ERROR_GROUP_UPDATED'),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('FORUM_NOTICE_GROUP_UPDATED'),
                                                     RESPONSE_NOTICE);
    }

}