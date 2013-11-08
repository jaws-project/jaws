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
class Tags_Actions_Tags extends Tags_Actions_Default
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
        $model = $this->gadget->model->load('Tags');
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
     * Displays Tags Cloud
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   bool    $global Only show global tags?
     * @return  string  XHTML template content
     */
    function TagCloud($gadget = null, $global = true)
    {
        if(empty($gadget)) {
            $gadget = jaws()->request->fetch('gname', 'get');
        }

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Tags');
        $res = $model->GenerateTagCloud($gadget, $global);
        $sortedTags = $res;
        sort($sortedTags);
        $minTagCount = log((isset($sortedTags[0]) ? $sortedTags[0]['howmany'] : 0));
        $maxTagCount = log(((count($res) != 0)? $sortedTags[count($res) - 1]['howmany'] : 0));
        unset($sortedTags);
        if ($minTagCount == $maxTagCount) {
            $tagCountRange = 1;
        } else {
            $tagCountRange = $maxTagCount - $minTagCount;
        }
        $minFontSize = 1;
        $maxFontSize = 10;
        $fontSizeRange = $maxFontSize - $minFontSize;

        $tpl = $this->gadget->loadTemplate('TagCloud.html');
        $tpl->SetBlock('tagcloud');

        if(!empty($gadget)) {
            $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
            $GLOBALS['app']->Translate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);
            $tpl->SetVariable('title', _t('TAGS_TAG_CLOUD', _t(strtoupper($gadget) . '_NAME')));
        } else {
            $tpl->SetVariable('title', _t('TAGS_TAG_CLOUD', _t('GLOBAL_ALL')));
        }

        if (!$global) {
            $tpl->SetVariable('menubar', $this->MenuBar('ManageTags', array('ManageTags')));
        }

        foreach ($res as $tag) {
            $count  = $tag['howmany'];
            $fsize = $minFontSize + $fontSizeRange * (log($count) - $minTagCount)/$tagCountRange;
            $tpl->SetBlock('tagcloud/tag');
            $tpl->SetVariable('size', (int)$fsize);
            $tpl->SetVariable('tagname',  Jaws_UTF8::strtolower($tag['title']));
            $tpl->SetVariable('frequency', $tag['howmany']);
            if (empty($gadget)) {
                $param = array('tag' => $tag['name']);
            } else {
                $param = array('tag' => $tag['name'], 'gname' => $gadget);
            }
            if(!$global) {
                $param['user'] = $user;
            }
            $tpl->SetVariable('url', $this->gadget->urlMap(
                'ViewTag',
                $param));
            $tpl->ParseBlock('tagcloud/tag');
        }
        $tpl->ParseBlock('tagcloud');

        return $tpl->Get();
    }

    /**
     * Display a Tag Items
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $action         Action name
     * @param   int     $reference      Reference
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @return  string  XHTML template content
     */
    function ViewItemTags($gadget, $action, $reference, &$tpl, $tpl_base_block)
    {
        $model = $this->gadget->model->loadAdmin('Tags');
        $tags = $model->GetItemTags(array('gadget' => $gadget, 'action' => $action, 'reference' => $reference), true);

        $tpl->SetBlock("$tpl_base_block/tags");
        $tpl->SetVariable('lbl_tags', _t('GLOBAL_TAGS'));
        foreach($tags as $tag) {
            $tpl->SetBlock("$tpl_base_block/tags/tag");
            $tpl->SetVariable('name', $tag);
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Tags', 'ViewTag',
                             array('tag'=>$tag, 'gname'=>$gadget)));
            $tpl->ParseBlock("$tpl_base_block/tags/tag");
        }
        $tpl->ParseBlock("$tpl_base_block/tags");

    }


    /**
     * Display a Tag
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTag()
    {
        $get = jaws()->request->fetch(array('tag', 'gname', 'page', 'user'), 'get');
        $tag = $get['tag'];
        $gadget = $get['gname'];
        if (!empty($get['user']) && ($get['user'] != $GLOBALS['app']->Session->GetAttribute('user'))) {
            return Jaws_HTTPError::Get(403);
        }


        $tpl = $this->gadget->loadTemplate('Tag.html');
        $tpl->SetBlock('tag');
        $tpl->SetVariable('title', _t('TAGS_VIEW_TAG', $tag));

        $page = $get['page'];
        if (is_null($page) || !is_numeric($page) || $page <= 0 ) {
            $page = 1;
        }

        $limit = (int)$this->gadget->registry->fetch('tag_results_limit');
        if (empty($limit)) {
            $limit = 10;
        }

        // Detect all items count(for paging)
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('count(tags.id):integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id');
        if(!empty($gadget)) {
            $table->where('gadget', $gadget);
        }
        $table->and()->where('tags.name', $tag)->and()->where('tags_items.published', true);
        $table->and()->openWhere('tags_items.update_time', time(), '<')->or();
        $table->closeWhere('tags_items.update_time', null, 'is' );
        if(!empty($get['user'])) {
            $table->and()->where('tags.user', $get['user']);
        } else {
            $table->and()->where('tags.user', 0);
        }
        $itemsTotal = $table->fetchOne();


        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select(
            'gadget', 'action', 'reference:integer'
        );

        $table->join('tags_items', 'tags_items.tag', 'tags.id');
        if(!empty($gadget)) {
            $table->where('gadget', $gadget);
        }
        $table->and()->where('tags.name', $tag)->and()->where('tags_items.published', true);
        $table->and()->openWhere('tags_items.update_time', time(), '<')->or();
        $table->closeWhere('tags_items.update_time', null, 'is' );
        if(!empty($get['user'])) {
            $table->and()->where('tags.user', $get['user']);
        } else {
            $table->and()->where('tags.user', 0);
        }
        $items = $table->orderBy('insert_time')->limit($limit, ($page - 1) * $limit)->fetchAll();

        $gadgetItems = array();
        foreach ($items as $item) {
            $gadgetItems[$item['gadget']][$item['action']][] = $item['reference'];
        }


        if(empty($gadget)) {
            $model = $this->gadget->model->load('Tags');
            $gadgets = $model->GetTagRelativeGadgets();
        } else {
            $gadgets = array($gadget);
        }
        if (is_array($gadgets) && count($gadgets) > 0 && $itemsTotal>0) {
            foreach ($gadgets as $gadget) {
                $objGadget = Jaws_Gadget::getInstance($gadget);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }
                $objHook = $objGadget->loadHook('Tags');
                if (Jaws_Error::IsError($objHook)) {
                    continue;
                }

                if(!isset($gadgetItems[$gadget])) {
                    continue;
                }

                $result[$gadget] = array();
                $gResult = $objHook->Execute($gadgetItems[$gadget]);
                if (!Jaws_Error::IsError($gResult) || !$gResult) {
                    if (is_array($gResult) && !empty($gResult)) {
                        $result[$gadget] = $gResult;
                    } else {
                        unset($result[$gadget]);
                    }
                }
            }

            reset($result);
        } else {
            $tpl->ParseBlock('tag');
            return $tpl->Get();
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'tag',
            $page,
            $limit,
            $itemsTotal,
            _t('TAGS_TAG_ITEM_COUNT', $itemsTotal),
            'ViewTag',
            array('tag'=>$tag)
        );

        if (count($result) > 2) {
            $tpl->SetBlock('tag/subtitle');
            $tpl->SetVariable('text', _t('SEARCH_RESULTS_SUBTITLE',
                $itemsTotal,
                'TEST'));
            $tpl->ParseBlock('tag/subtitle');
        }

        $date = $GLOBALS['app']->loadDate();
        $max_result_len = (int)$this->gadget->registry->fetch('max_result_len');
        if (empty($max_result_len)) {
            $max_result_len = 500;
        }

        foreach ($result as $gadget => $tags) {
            $tpl->SetBlock('tags/gadget');
            $info = Jaws_Gadget::getInstance($gadget);
            $tpl->SetVariable('gadget_result', _t('SEARCH_RESULTS_IN_GADGETS',
                count($tags),
                'TEST',
                $info->title));
            $tpl->ParseBlock('tags/gadget');
            foreach ($tags as $item) {
                $tpl->SetBlock('tag/tag_item');
                $tpl->SetVariable('title',  $item['title']);
                $tpl->SetVariable('url',    $item['url']);
                $tpl->SetVariable('target', (isset($item['outer']) && $item['outer'])? '_blank' : '_self');
                $tpl->SetVariable('image',  $item['image']);

                if (!isset($item['parse_text']) || $item['parse_text']) {
                    $item['snippet'] = $this->gadget->ParseText($item['snippet'], $gadget);
                }
                if (!isset($item['strip_tags']) || $item['strip_tags']) {
                    $item['snippet'] = strip_tags($item['snippet']);
                }
                $item['snippet'] = $GLOBALS['app']->UTF8->substr($item['snippet'], 0, $max_result_len);

                $tpl->SetVariable('snippet', $item['snippet']);
                $tpl->SetVariable('date', $date->Format($item['date']));
                $tpl->ParseBlock('tag/tag_item');
            }
        }

        $tpl->ParseBlock('tag');
        return $tpl->Get();
    }
}