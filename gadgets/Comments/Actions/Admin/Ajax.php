<?php
/**
 * Comments AJAX API
 *
 * @category    Ajax
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Search for comments and return the data in an array
     *
     * @access  public
     * @return  array   Data array
     */
    function SearchComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        @list($requester, $gadget, $search, $status, $offset) = jaws()->request->fetchAll('post');
        $cHTML = $this->gadget->action->loadAdmin('Comments');
        return $cHTML->GetDataAsArray($requester, $gadget, $search, $status, $offset, true);
    }

    /**
     * Get total posts of a comment search
     *
     * @access  public
     * @return  int     Total of posts
     */
    function SizeOfCommentsSearch()
    {
        @list($gadget, $search, $status) = jaws()->request->fetchAll('post');
        $cModel = $this->gadget->model->load('Comments');
        return $cModel->GetCommentsCount($gadget, '', '', $search, $status);
    }

    /**
     * Get information of a Comment
     *
     * @access  public
     * @return  array   Comment info array
     */
    function GetComment()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $cModel = $this->gadget->model->load('Comments');
        $comment = $cModel->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            return false; //we need to handle errors on ajax
        }

        return $comment;
    }

    /**
     * Update comment information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        @list($gadget, $id, $name, $email, $url, $message, $reply, $status, $sendEmail) = jaws()->request->fetchAll('post');
        // TODO: Fill permalink In New Versions, Please!!
        $cModel = $this->gadget->model->load('EditComments');
        $res = $cModel->updateComment($gadget, $id, $name, $email, $url, $message, $reply, '', $status, $sendEmail);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_COMMENT_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Does a massive delete on comments
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        $ids = jaws()->request->fetchAll('post');
        $cModel = $this->gadget->model->load('DeleteComments');
        $res = $cModel->DeleteMassiveComment($ids);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_COMMENT_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MarkAs()
    {
        $this->gadget->CheckPermission('ManageComments');
        @list($gadget, $ids, $status) = jaws()->request->fetch(array('0', '1:array', '2'), 'post');
        $cModel = $this->gadget->model->load('EditComments');
        $res = $cModel->MarkAs($gadget, $ids, $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_COMMENT_MARKED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update Settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveSettings()
    {
        $this->gadget->CheckPermission('Settings');
        @list($allowComments, $allowDuplicate) = jaws()->request->fetchAll('post');
        $cModel = $this->gadget->model->loadAdmin('Settings');
        $res = $cModel->SaveSettings($allowComments, $allowDuplicate);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}