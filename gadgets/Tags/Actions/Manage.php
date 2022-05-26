<?php
/**
 * Tags Gadget
 *
 * @category   Gadget
 * @package    Tags
 */
class Tags_Actions_Manage extends Jaws_Gadget_Action
{

    /**
     * Manage User's Tags
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageTags()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe();
        $post = $this->gadget->request->fetch(array('gadgets_filter', 'term', 'page', 'page_item'));
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
        if ($response = $this->gadget->session->pop('ManageTags')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->SetVariable('title', $this::t('MANAGE_TAGS'));
        if ($this->app->session->user->logged) {
            // Menu navigation
            $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        }

        $page = empty($page) ? 1 : (int)$page;
        if (empty($post['page_item'])) {
            $limit = 10;
        } else {
            $limit = $post['page_item'];
        }
        $tpl->SetVariable('opt_page_item_' . $limit, 'selected="selected"');

        $user = (int)$this->app->session->user->id;
        $model = $this->gadget->model->loadAdmin('Tags');
        $tags = $model->GetTags($filters, $limit, ($page - 1) * $limit, $user);
        $tagsTotal = $model->GetTagsCount($filters, $user);

        $tpl->SetVariable('txt_term', $post['term']);
        $tpl->SetVariable('lbl_gadgets', Jaws::t('GADGETS'));
        $tpl->SetVariable('lbl_all', Jaws::t('ALL'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $tpl->SetVariable('lbl_tag_name', $this::t('TAG_NAME'));
        $tpl->SetVariable('lbl_tag_title', $this::t('TAG_TITLE'));
        $tpl->SetVariable('lbl_tag_usage_count', $this::t('TAG_USAGE_COUNT'));
        $tpl->SetVariable('filter', Jaws::t('SEARCH'));
        $tpl->SetVariable('lbl_page_item', $this::t('ITEMS_PER_PAGE'));
        $tpl->SetVariable('lbl_actions', Jaws::t('ACTIONS'));
        $tpl->SetVariable('lbl_no_action', Jaws::t('NO_ACTION'));
        $tpl->SetVariable('lbl_delete', Jaws::t('DELETE'));
        $tpl->SetVariable('lbl_merge', $this::t('MERGE'));
        $tpl->SetVariable('selectMoreThanOneTags',  $this::t('SELECT_MORE_THAN_ONE_TAG_FOR_MERGE'));
        $tpl->SetVariable('enterNewTagName',  $this::t('ENTER_NEW_TAG_NAME'));

        //load other gadget translations
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');

        $tpl->SetBlock('tags/gadgets_filter');
        //Gadgets filter
        $model = $this->gadget->model->load('Tags');
        $gadgets = $model->GetTagableGadgets();
        $tagGadgets = array();
        $tagGadgets[''] = Jaws::t('ALL');
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
        // pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $limit,
            $tagsTotal,
            'ManageTags',
            $params,
            $this::t('TAG_COUNT', $tagsTotal)
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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('index.js');

        $tag_id = $this->gadget->request->fetch('tag', 'get');
        $user = (int)$this->app->session->user->id;
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

        $tpl->SetVariable('title', $this::t('EDIT_TAG'));
        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('save', Jaws::t('SAVE'));

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
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(array('tid', 'name', 'title', 'description'), 'post');
        $id = $post['tid'];
        unset($post['tid']);
        $user = (int)$this->app->session->user->id;
        $model = $this->gadget->model->loadAdmin('Tags');
        $tag = $model->GetTag($id);
        if ($tag['user'] != $user) {
            return Jaws_HTTPError::Get(403);
        }
        $res = $model->UpdateTag($id, $post, $user);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(
                $this::t('ERROR_CANT_UPDATE_TAG'),
                RESPONSE_ERROR,
                'ManageTags'
            );
        } else {
            $this->gadget->session->push(
                $this::t('TAG_UPDATED'),
                RESPONSE_NOTICE,
                'ManageTags'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

    /**
     * Delete Tags
     *
     * @access  public
     * @return  void
     */
    function DeleteTags()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $ids = $this->gadget->request->fetch('tags_checkbox:array', 'post');
        $user = (int)$this->app->session->user->id;
        $model = $this->gadget->model->loadAdmin('Tags');
        $res = $model->DeleteTags($ids, $user);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(
                $this::t('ERROR_CANT_DELETE_TAG'),
                RESPONSE_ERROR,
                'ManageTags'
            );
        } else {
            $this->gadget->session->push(
                $this::t('TAG_DELETED'),
                RESPONSE_NOTICE,
                'ManageTags'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

    /**
     * Merge Tags
     *
     * @access  public
     * @return  void
     */
    function MergeTags()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $post = $this->gadget->request->fetch(array('tags_checkbox:array', 'new_tag_name'), 'post');
        $ids = $post['tags_checkbox'];
        if (count($ids) < 3) {
            $this->gadget->session->push(
                $this::t('SELECT_MORE_THAN_ONE_TAG_FOR_MERGE'),
                RESPONSE_ERROR,
                'ManageTags'
            );
        }
        if (empty($post['new_tag_name'])) {
            $this->gadget->session->push(
                $this::t('ERROR_ENTER_NEW_TAG_NAME'),
                RESPONSE_ERROR,
                'ManageTags'
            );
        }
        $user = (int)$this->app->session->user->id;
        $model = $this->gadget->model->loadAdmin('Tags');
        $res = $model->MergeTags($ids, $post['new_tag_name'], $user);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR,
                'ManageTags'
            );
        } else {
            $this->gadget->session->push(
                $this::t('TAGS_MERGED'),
                RESPONSE_NOTICE,
                'ManageTags'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('ManageTags'));
    }

}