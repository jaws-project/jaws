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
     * @param   Jaws_Model  $model  Jaws_Model reference
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
     * @return  mixed   Group information or False on error
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
     * @return  mixed   Forum information or False on error
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
     * @return  string  XHTML template content of groupForm
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
     * @return  string  XHTML template content of groupForm
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
     * @param   int     $gid            group ID
     * @param   string  $title          forum title
     * @param   string  $description    forum description
     * @return  array   response array
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
     * @param   int     $fid            forum ID
     * @param   int     $gid            group ID
     * @param   string  $title          forum title
     * @param   string  $description    forum description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array  response array
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
     * @param   string  $title          group title
     * @param   string  $description    group description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array   response array
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
     * @param   int     $gid            group ID
     * @param   string  $title          group title
     * @param   string  $description    group description
     * @param   string  $fast_url
     * @param   string  $order
     * @param   bool    $locked
     * @param   bool    $published
     * @return  array   response array
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