<?php
/**
 * Tags AJAX API
 *
 * @category    Ajax
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Tags_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Search for tags and return the data in an array
     *
     * @access  public
     * @return  array   Data array
     */
    function SearchTags()
    {
        $this->gadget->CheckPermission('ManageComments');
        @list($limit, $gadget, $search, $status) = jaws()->request->fetchAll('post');
        $tHTML = $GLOBALS['app']->LoadGadget('Tags', 'AdminHTML', 'Tags');
        return $tHTML->GetDataAsArray($gadget, "javascript:editTag(this, '{id}')", $search, $status, $limit, true);
    }

    /**
     * Get total posts of a tag search
     *
     * @access  public
     * @return  int     Total of posts
     */
    function SizeOfTagsSearch()
    {
        @list($gadget, $search, $status) = jaws()->request->fetchAll('post');
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'Comments');
        return $cModel->GetCommentsCount($gadget, '', '', $search, $status);
    }

    /**
     * Get information of a Tag
     *
     * @access  public
     * @return  array   Tag info array
     */
    function GetTag()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $tag = $model->GetTag($id);
        if (Jaws_Error::IsError($tag)) {
            return false; //we need to handle errors on ajax
        }

        return $tag;
    }

    /**
     * Update comment information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateTag()
    {
        $this->gadget->CheckPermission('ManageTags');
        @list($id, $name) = jaws()->request->fetchAll('post');
        // TODO: Fill permalink In New Versions, Please!!
        $tModel = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $tModel->UpdateTag($id, $name);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TAGS_TAG_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Does a massive delete on comments
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteTags()
    {
        $this->gadget->CheckPermission('ManageTags');
        $ids = jaws()->request->fetchAll('post');
        $tModel = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $tModel->DeleteTags($ids);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TAGS_TAG_DELETED'), RESPONSE_NOTICE);
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
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'EditComments');
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