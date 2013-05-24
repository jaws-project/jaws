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
class Comments_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Comments_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
    }

    /**
     * Search for comments and return the data in an array
     *
     * @access  public
     * @param   int     $limit   Data limit
     * @param   string  $gadget
     * @param   string  $search  Search word
     * @param   int  $status  comment status (approved=1, waiting=2, spam=3)
     * @return  array   Data array
     */
    function SearchComments($limit, $gadget, $search, $status)
    {
        // TODO: Check Permission For Manage Comments
        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHTML->GetDataAsArray($gadget, "javascript:editComment(this, '{id}')", $search, $status, $limit, true);
    }

    /**
     * Get total posts of a comment search
     *
     * @access  public
     * @param   string  $gadget
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved=1, waiting=2, spam=3)
     * @return  int     Total of posts
     */
    function SizeOfCommentsSearch($gadget, $search, $status)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'Comments');
        return $cModel->GetCommentsCount($gadget, '', '', $search, $status);
    }

    /**
     * Get information of a Comment
     *
     * @access  public
     * @param   int     $id Comment ID
     * @return  array   Comment info array
     */
    function GetComment($id)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'Comments');
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
     * @param   string  $gadget
     * @param   int     $id         Comment ID
     * @param   string  $name       Name
     * @param   string  $email      Email address
     * @param   string  $url
     * @param   string  $message    Message content
     * @param   string  $reply
     * @param   $status
     * @return  array   Response array (notice or error)
     */
    function UpdateComment($gadget, $id, $name, $email, $url, $message, $reply, $status)
    {
        // TODO: Check Permission For Manage Comments
        // TODO: Fill permalink In New Versions, Please!!
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'EditComments');
        $res = $cModel->updateComment($gadget, $id, $name, $email, $url, $message, $reply, '', $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Does a massive delete on comments
     *
     * @access  public
     * @param   array   $ids     Comment ids
     * @return  array   Response array (notice or error)
     */
    function DeleteComments($ids)
    {
        // TODO: check permission before delete comments
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'DeleteComments');
        $res = $cModel->DeleteMassiveComment($ids);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Mark as different type a group of ids
     *
     * @access  public
     * @param   string  $gadget
     * @param   array   $ids        Ids of comments
     * @param   string  $status     New status
     * @return  array   Response array (notice or error)
     */
    function MarkAs($gadget, $ids, $status)
    {
        // TODO: Check Permission For Manage Comments
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'EditComments');
        $res = $cModel->MarkAs($gadget, $ids, $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_COMMENT_MARKED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update Settings
     *
     * @access  public
     * @param   string  $allowComments  Allow comments?
     * @param   string  $allowDuplicate Allow duplicated comments?
     * @return  array   Response array (notice or error)
     */
    function SaveSettings($allowComments, $allowDuplicate)
    {
        // TODO: check permission before updating settings
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel', 'Settings');
        $res = $cModel->SaveSettings($allowComments, $allowDuplicate);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMMENTS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}