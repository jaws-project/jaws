<?php
/**
 * Forums AJAX API
 *
 * @category   Ajax
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get information of a group
     *
     * @access   public
     * @internal param  int     $gid    Group ID
     * @return   mixed  Group information or False on error
     */
    function GetGroup()
    {
        $this->gadget->CheckPermission('default');
        @list($gid) = $this->gadget->request->fetchAll('post');
        $gModel = $this->gadget->model->load('Groups');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }

    /**
     * Get information of a forum
     *
     * @access   public
     * @internal param  int     $fid    Forum ID
     * @return   mixed  Forum information or False on error
     */
    function GetForum()
    {
        $this->gadget->CheckPermission('default');
        @list($fid) = $this->gadget->request->fetchAll('post');
        $fModel = $this->gadget->model->load('Forums');
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
        $this->gadget->CheckPermission('default');
        $gHTML = $this->gadget->action->loadAdmin('Group');
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
        $this->gadget->CheckPermission('default');
        $fHTML = $this->gadget->action->loadAdmin('Forum');
        return $fHTML->GetForumUI();
    }

    /**
     * Insert forum
     *
     * @access   public
     * @internal param  int     $gid            group ID
     * @internal param  string  $title          forum title
     * @internal param  string  $description    forum description
     * @internal param  string  $fast_url
     * @internal param  string  $order
     * @internal param  bool    $locked         is locked
     * @internal param  bool    $private        is private
     * @internal param  bool    $published      is published
     * @return   array  Response array (notice or error)
     */
    function InsertForum()
    {
        @list($gid, $title, $description, $fast_url, $order, $locked, $private, $published) = $this->gadget->request->fetchAll('post');
        $fModel = $this->gadget->model->loadAdmin('Forums');
        $res = $fModel->InsertForum($gid, $title, $description, $fast_url, $order, $locked, $private, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
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
     * @access   public
     * @internal param  int     $fid            forum ID
     * @internal param  int     $gid            group ID
     * @internal param  string  $title forum    title
     * @internal param  string  $description    forum description
     * @internal param  string  $fast_url
     * @internal param  string  $order
     * @internal param  bool    $locked
     * @internal param  bool    $private
     * @internal param  bool    $published
     * @return   array  Response array (notice or error)
     */
    function UpdateForum()
    {
        @list($fid, $gid, $title, $description,
            $fast_url, $order, $locked, $private, $published
        ) = $this->gadget->request->fetchAll('post');
        $this->gadget->CheckPermission('ForumManage', $fid);

        $fModel = $this->gadget->model->loadAdmin('Forums');
        $res = $fModel->UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $private, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FORUMS_NOTICE_FORUM_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete a forum
     *
     * @access   public
     * @internal param  int     $fid    Forum ID
     * @return   array  Response array (notice or error)
     */
    function DeleteForum()
    {
        @list($fid) = $this->gadget->request->fetchAll('post');
        $this->gadget->CheckPermission('ForumManage', $fid);
        $fModel = $this->gadget->model->loadAdmin('Forums');
        $res = $fModel->DeleteForum($fid);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        } elseif ($res) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_NOTICE_FORUM_DELETED'),
                RESPONSE_NOTICE
            );
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_ERROR_FORUM_NOT_EMPTY'),
                RESPONSE_ERROR
            );
        }
    }

    /**
     * Insert group
     *
     * @access   public
     * @internal param  string  $title          group title
     * @internal param  string  $description    group description
     * @internal param  string  $fast_url
     * @internal param  string  $order
     * @internal param  bool    $locked
     * @internal param  bool    $published
     * @return   array  Response array (notice or error)
     */
    function InsertGroup()
    {
        @list($title, $description, $fast_url, $order, $locked, $published) = $this->gadget->request->fetchAll('post');
        $gModel = $this->gadget->model->loadAdmin('Groups');
        $gid = $gModel->InsertGroup($title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($gid)) {
            return $GLOBALS['app']->Session->GetResponse($gid->getMessage(), RESPONSE_ERROR);
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
     * @access   public
     * @internal param  int     $gid            group ID
     * @internal param  string  $title          group title
     * @internal param  string  $description    group description
     * @internal param  string  $fast_url
     * @internal param  string  $order
     * @internal param  bool    $locked
     * @internal param  bool    $published
     * @return   array  Response array (notice or error)
     */
    function UpdateGroup()
    {
        @list($gid, $title, $description, $fast_url, $order, $locked, $published) = $this->gadget->request->fetchAll('post');
        $gModel = $this->gadget->model->loadAdmin('Groups');
        $res = $gModel->UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('FORUMS_NOTICE_GROUP_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete a group
     *
     * @access   public
     * @internal param  int     $gid    Group ID
     * @return   array  Response array (notice or error)
     */
    function DeleteGroup()
    {
        @list($gid) = $this->gadget->request->fetchAll('post');
        $gModel = $this->gadget->model->loadAdmin('Groups');
        $res = $gModel->DeleteGroup($gid);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        } elseif ($res) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_NOTICE_GROUP_DELETED'),
                RESPONSE_NOTICE
            );
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                _t('FORUMS_ERROR_GROUP_NOT_EMPTY'),
                RESPONSE_ERROR
            );
        }
    }

}