<?php
/**
 * Tags AJAX API
 *
 * @category    Ajax
 * @package     Tags
 */
class Tags_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Search for tags and return the data in an array
     *
     * @access  public
     * @return  array   Data array
     */
    function SearchTags()
    {
        $filters = $this->gadget->request->fetchAll('post');
        $filters['gadget'] = $filters['gadgets_filter'];
        unset($filters['gadgets_filter']);
        $offset = $filters['offset'];
        unset($filters['offset']);
        $tHTML = $this->gadget->action->loadAdmin('Tags');
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
        $filters = $this->gadget->request->fetchAll('post');
        $filters['gadget'] = $filters['gadgets_filter'];
        unset($filters['gadgets_filter']);
        $tModel = $this->gadget->model->loadAdmin('Tags');
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
        $gadget = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Tags');
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
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Tags');
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
        $data = $this->gadget->request->fetchAll('post');
        $tModel = $this->gadget->model->loadAdmin('Tags');
        $res = $tModel->AddTag($data);
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
        $data = $this->gadget->request->fetchAll('post');
        $id = $data['id'];
        unset($data['id']);
        $tModel = $this->gadget->model->loadAdmin('Tags');
        $res = $tModel->UpdateTag($id, $data);
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
        $ids = $this->gadget->request->fetchAll('post');
        $tModel = $this->gadget->model->loadAdmin('Tags');
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
        @list($ids, $newName) = $this->gadget->request->fetchAll('post');
        $ids = $this->gadget->request->fetch('0:array', 'post');

        $tModel = $this->gadget->model->loadAdmin('Tags');
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
        @list($tagResultLimit) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $res = $model->SaveSettings($tagResultLimit);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TAGS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}