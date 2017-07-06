<?php
/**
 * Comments AJAX API
 *
 * @category    Ajax
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2012-2015 Jaws Development Group
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
        @list($requester, $gadget, $search, $status, $offset, $orderBy) = $this->gadget->request->fetchAll('post');
        $cHTML = $this->gadget->action->loadAdmin('Comments');
        return $cHTML->GetDataAsArray($requester, $gadget, $search, $status, $offset, $orderBy);
    }

    /**
     * Get total posts of a comment search
     *
     * @access  public
     * @return  int     Total of posts
     */
    function SizeOfCommentsSearch()
    {
        @list($gadget, $search, $status) = $this->gadget->request->fetchAll('post');
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
        @list($id) = $this->gadget->request->fetchAll('post');
        $cModel = $this->gadget->model->load('Comments');
        $comment = $cModel->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            return false; //we need to handle errors on ajax
        }

        $date = Jaws_Date::getInstance();
        $comment['insert_time'] = $date->Format($comment['insert_time'], 'Y-m-d H:i:s');

        $comment['reference_title'] = '';
        $comment['reference_url'] = '';
        $objGadget = Jaws_Gadget::getInstance($comment['gadget']);
        if (!Jaws_Error::IsError($objGadget)) {
            $objHook = $objGadget->hook->load('Comments');
            if (!Jaws_Error::IsError($objHook)) {
                $referenceInfo = $objHook->Execute($comment['action'], $comment['reference']);
                $comment['reference_title'] = $referenceInfo['title'];
                $comment['reference_url'] = $referenceInfo['url'];
            }
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
        @list($gadget, $id, $name, $email, $url, $message, $reply, $status, $sendEmail) = $this->gadget->request->fetchAll('post');
        // TODO: Fill permalink In New Versions, Please!!
        $cModel = $this->gadget->model->loadAdmin('Comments');
        $res = $cModel->UpdateComment($gadget, $id, $name, $email, $url, $message, $reply, '', $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            if (!empty($reply) && !empty($email) && $sendEmail) {
                $cHTML = $this->gadget->action->load('Comments');
                $result = $cHTML->EmailReply(
                    $email,
                    $message,
                    $reply,
                    $GLOBALS['app']->Session->GetAttribute('nickname')
                );
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushLastResponse($result->GetMessage(), RESPONSE_ERROR);
                }
            }
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
        $ids = $this->gadget->request->fetchAll('post');
        $cModel = $this->gadget->model->loadAdmin('Comments');
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
        $post = $this->gadget->request->fetch(array('ids:array', 'status'), 'post');
        $cModel = $this->gadget->model->loadAdmin('Comments');
        $res = $cModel->MarkAs($post['ids'], $post['status']);
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
        @list($allowComments, $defaultStatus, $orderType) = $this->gadget->request->fetchAll('post');
        $cModel = $this->gadget->model->loadAdmin('Settings');
        $res = $cModel->SaveSettings($allowComments, $defaultStatus, $orderType);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}