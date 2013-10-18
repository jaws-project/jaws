<?php
/**
 * Tags Gadget
 *
 * @category   Gadget
 * @package    Tags
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Tags_Actions_ManageTags extends Tags_HTML
{

    /**
     * Get then TagsCloud action params
     *
     * @access  public
     * @return  array list of the TagsCloud action params
     */
    function TagCloudLayoutParams()
    {
        $result = array();

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $model = $GLOBALS['app']->LoadGadget('Tags', 'Model', 'Tags');
        $gadgets = $model->GetTagRelativeGadgets();
        $tagGadgets = array();
        $tagGadgets[''] = _t('GLOBAL_ALL');
        foreach($gadgets as $gadget) {
            $GLOBALS['app']->Translate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);
            $tagGadgets[$gadget] = _t(strtoupper($gadget) . '_NAME');
        }

        $result[] = array(
            'title' => _t('TAGS_GADGET'),
            'value' => $tagGadgets
        );

        $result[] = array(
            'title' => _t('TAGS_SHOW_TAGS'),
            'value' => array(
                1 => _t('TAGS_GLOBAL_TAGS'),
                0 => _t('TAGS_USER_TAGS'),
            )
        );

        return $result;
    }

    /**
     * Manage User's Tags
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageTags()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe();
        $post = jaws()->request->fetch(array('gadgets_filter', 'term'));
        $filters = array();
        $selected_gadget = "";
        if(!empty($post['gadgets_filter'])) {
            $filters['gadget'] = $post['gadgets_filter'];
            $selected_gadget = $post['gadgets_filter'];
        }

        if(!empty($post['term'])) {
            $filters['name'] = $post['term'];
        }

        $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $tags = $model->GetTags($filters, null, 0, 0, false);

        $tpl = $this->gadget->loadTemplate('ManageTags.html');
        $tpl->SetBlock('tags');
        if ($response = $GLOBALS['app']->Session->PopResponse('Tags.ManageTags')) {
            $tpl->SetBlock('tags/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('tags/response');
        }
        $tpl->SetVariable('txt_term', $post['term']);
        $tpl->SetVariable('lbl_gadgets', _t('GLOBAL_GADGETS'));
        $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $tpl->SetVariable('lbl_tag_title', _t('TAGS_TAG_TITLE'));
        $tpl->SetVariable('lbl_tag_usage_count', _t('TAGS_TAG_USAGE_COUNT'));
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
        $model = $GLOBALS['app']->LoadGadget('Tags', 'Model', 'Tags');
        $gadgets = $model->GetTagRelativeGadgets();
        $tagGadgets = array();
        $tagGadgets[''] = _t('GLOBAL_ALL');
        foreach ($gadgets as $gadget) {
            $tpl->SetBlock('tags/gadget');
            $GLOBALS['app']->Translate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);
            $tpl->SetVariable('selected', '');
            if ($gadget == $selected_gadget) {
                $tpl->SetVariable('selected', 'selected="selected"');
            }
            $tpl->SetVariable('name', $gadget);
            $tpl->SetVariable('title', _t(strtoupper($gadget) . '_NAME'));
            $tpl->ParseBlock('tags/gadget');
        }

        foreach($tags as $tag) {
            $tpl->SetBlock('tags/tag');
            $tpl->SetVariable('id', $tag['id']);
            $tpl->SetVariable('title', $tag['title']);
            $tpl->SetVariable('usage_count', $tag['usage_count']);
            $tpl->ParseBlock('tags/tag');
        }

        $tpl->ParseBlock('tags');
        return $tpl->Get();
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $ids = jaws()->request->fetch('tags_checkbox:array', 'post');
        $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $model->DeleteTags($ids);
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
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
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
        $model = $GLOBALS['app']->LoadGadget('Tags', 'AdminModel', 'Tags');
        $res = $model->MergeTags($ids, $post['new_tag_name'], false);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('TAGS_ERROR_CANT_DELETE_TAG'),
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