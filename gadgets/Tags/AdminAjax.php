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
        @list($filters, $offset) = jaws()->request->fetchAll('post');
        $filters = jaws()->request->fetch('0:array', 'post');
        $tHTML = $GLOBALS['app']->LoadGadget('Tags', 'AdminHTML', 'Tags');
        return $tHTML->GetDataAsArray("javascript:editTag(this, '{id}')", $filters, $offset);
    }

    /**
     * Get total tags of a tag search
     *
     * @access  public
     * @return  int     Total of tags
     */
    function SizeOfTagsSearch()
    {
        $filters = jaws()->request->fetchAll('post');
        $tModel = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        return $tModel->GetTagsCount($filters);
    }

    /**
     * Get a gadget available actions
     *
     * @access   public
     * @internal param   string $gadget Gadget name
     * @return   array   gadget actions
     */
    function GetGadgetActions()
    {
        $gadget = jaws()->request->fetchAll('post');
        $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $actions = $model->GetGadgetActions($gadget);
        if (Jaws_Error::IsError($actions)) {
            return false; //we need to handle errors on ajax
        }

        return $actions;
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
     * Add an new tag
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddTag()
    {
        $this->gadget->CheckPermission('AddTags');
        $name = jaws()->request->fetchAll('post');
        $tModel = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $tModel->AddTag($name);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TAGS_TAG_ADDED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update tag information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateTag()
    {
        $this->gadget->CheckPermission('ManageTags');
        @list($id, $name) = jaws()->request->fetchAll('post');
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
     * Does a massive delete on tags
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
     * Merge tags
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MergeTags()
    {
        $this->gadget->CheckPermission('MergeTags');
        @list($ids, $newName) = jaws()->request->fetchAll('post');
        $ids = jaws()->request->fetch('0:array', 'post');

        $tModel = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $tModel->MergeTags($ids, $newName);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TAGS_TAGS_MERGED'), RESPONSE_NOTICE);
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