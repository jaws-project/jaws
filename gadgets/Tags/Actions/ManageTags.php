<?php
/**
 * Tags Gadget
 *
 * @category   Gadget
 * @package    Tags
 */
class Tags_Actions_ManageTags extends Tags_Actions_Default
{

    /**
     * Manage User's Tags
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageTags()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe();
        $post = jaws()->request->fetch(array('gadgets_filter', 'term', 'page', 'page_item'));
        $page = $post['page'];

        $filters = array();
        $selected_gadget = "";
        if(!empty($post['gadgets_filter'])) {
            $filters['gadget'] = $post['gadgets_filter'];
            $selected_gadget = $post['gadgets_filter'];
        }

        if(!empty($post['term'])) {
            $filters['name'] = $post['term'];
        }

        $tpl = $this->gadget->template->load('ManageTags.html');
        $tpl->SetBlock('tags');
        if ($response = $GLOBALS['app']->Session->PopResponse('Tags.ManageTags')) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('ManageTags', array('ManageTags')));
        $tpl->SetVariable('title', _t('TAGS_MANAGE_TAGS'));

        $page = empty($page) ? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = 10;
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->loadAdmin('Tags');
        $tags = $model->GetTags($filters, $limit, ($page - 1) * $limit, 0, $user);
        $tagsTotal = $model->GetTagsCount($filters, $user);

        $tpl->SetVariable('txt_term', $post['term']);
        $tpl->SetVariable('lbl_gadgets', _t('GLOBAL_GADGETS'));
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $tpl->SetVariable('lbl_tag_name', _t('TAGS_TAG_NAME'));
        $tpl->SetVariable('lbl_tag_title', _t('TAGS_TAG_TITLE'));
        $tpl->SetVariable('lbl_tag_usage_count', _t('TAGS_TAG_USAGE_COUNT'));
        $tpl->SetVariable('filter', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_page_item', _t('TAGS_ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_merge', _t('TAGS_MERGE'));
        $tpl->SetVariable('selectMoreThanOneTags',  _t('TAGS_SELECT_MORE_THAN_ONE_TAG_FOR_MERGE'));
        $tpl->SetVariable('enterNewTagName',  _t('TAGS_ENTER_NEW_TAG_NAME'));

        //load other gadget translations
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');

        $tpl->SetBlock('tags/gadgets_filter');
        //Gadgets filter
        $model = $this->gadget->model->load('Tags');
        $gadgets = $model->GetTagableGadgets();
        $tagGadgets = array();
        $tagGadgets[''] = _t('GLOBAL_ALL');
        $objTranslate = Jaws_Translate::getInstance();
        foreach ($gadgets as $gadget => $title) {
            $tpl->SetBlock('tags/gadget');
            $tpl->SetVariable('selected', '');
            if ($gadget == $selected_gadget) {
                $tpl->SetVariable('selected', 'selected="selected"');
            }
            $tpl->SetVariable('name', $gadget);
            $tpl->SetVariable('title', $title);
            $tpl->ParseBlock('tags/gadget');
        }

        foreach($tags as $tag) {
            $tpl->SetBlock('tags/tag');
            $tpl->SetVariable('id', $tag['id']);
            $tpl->SetVariable('name', $tag['name']);
            $tpl->SetVariable('title', $tag['title']);
            $tpl->SetVariable('usage_count', $tag['usage_count']);
            $tpl->SetVariable('tag_url', $this->gadget->urlMap('EditTagUI', array('tag'=>$tag['id'])));
            $tpl->ParseBlock('tags/tag');
        }

        $params = array();
        if(!empty($post['gadgets_filter'])) {
            $params['gadgets_filter'] = $post['gadgets_filter'];
        }
        if(!empty($post['term'])) {
            $params['term'] = $post['term'];
        }
        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'tags',
            $page,
            $limit,
            $tagsTotal,
            _t('TAGS_TAG_COUNT', $tagsTotal),
            'ManageTags',
            $params
        );

        $tpl->ParseBlock('tags');
        return $tpl->Get();
    }

    /**
     * Edit Tag UI
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function EditTagUI()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');

        $tag_id = jaws()->request->fetch('tag', 'get');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->loadAdmin('Tags');
        $tag = $model->GetTag($tag_id);
        if ($tag['user'] != $user) {
            return Jaws_HTTPError::Get(403);
        }

        // Load the template
        $tpl = $this->gadget->template->load('ManageTags.html');
        $tpl->SetBlock('edit_tag');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('tid', $tag_id);
        $tpl->SetVariable('name', $tag['name']);
        $tpl->SetVariable('tag_title', $tag['title']);
        $tpl->SetVariable('description', $tag['description']);

        $tpl->SetVariable('title', _t('TAGS_EDIT_TAG'));
        $tpl->SetVariable('menubar', $this->MenuBar('ViewTags',
                                     array('ManageTags', 'ViewTag'),
                                     array('tag' => $tag['name'], 'user' => $user)));

        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('save', _t('GLOBAL_SAVE'));

        $tpl->ParseBlock('edit_tag');
        return $tpl->Get();
    }

    /**
     * Update a Tag
     *
     * @access  public
     * @return  void
     */
    function UpdateTag()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('tid', 'name', 'title', 'description'), 'post');
        $id = $post['tid'];
        unset($post['tid']);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->loadAdmin('Tags');
        $tag = $model->GetTag($id);
        if ($tag['user'] != $user) {
            return Jaws_HTTPError::Get(403);
        }
        $res = $model->UpdateTag($id, $post, $user);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_ERROR_CANT_UPDATE_TAG'),
                'Tags.ManageTags',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_TAG_UPDATED'),
                'Tags.ManageTags',
                RESPONSE_NOTICE
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

    /**
     * Delete Tags
     *
     * @access  public
     * @return  void
     */
    function DeleteTags()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $ids = jaws()->request->fetch('tags_checkbox:array', 'post');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->loadAdmin('Tags');
        $res = $model->DeleteTags($ids, $user);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_ERROR_CANT_DELETE_TAG'),
                'Tags.ManageTags',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_TAG_DELETED'),
                'Tags.ManageTags',
                RESPONSE_NOTICE
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

    /**
     * Merge Tags
     *
     * @access  public
     * @return  void
     */
    function MergeTags()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('tags_checkbox:array', 'new_tag_name'), 'post');
        $ids = $post['tags_checkbox'];
        if (count($ids) < 3) {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_SELECT_MORE_THAN_ONE_TAG_FOR_MERGE'),
                'Tags.ManageTags',
                RESPONSE_ERROR
            );
        }
        if (empty($post['new_tag_name'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_ERROR_ENTER_NEW_TAG_NAME'),
                'Tags.ManageTags',
                RESPONSE_ERROR
            );
        }
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->loadAdmin('Tags');
        $res = $model->MergeTags($ids, $post['new_tag_name'], $user);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'Tags.ManageTags',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_TAGS_MERGED'),
                'Tags.ManageTags',
                RESPONSE_NOTICE
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

}