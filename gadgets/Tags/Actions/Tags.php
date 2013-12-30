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
        $model = $this->gadget->model->load('Tags');
        $gadgets = $model->GetTagableGadgets();
        array_unshift($gadgets, _t('GLOBAL_ALL'));
        $result[] = array(
            'title' => _t('TAGS_GADGET'),
            'value' => $gadgets
        );

        $result[] = array(
            'title' => _t('TAGS_SHOW_TAGS'),
            'value' => array(
                0 => _t('TAGS_GLOBAL_TAGS'),
                1 => _t('TAGS_USER_TAGS'),
            )
        );

        return $result;
    }

    /**
     * Displays Tags Cloud
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   bool    $user   Only show user tags?
     * @return  string  XHTML template content
     */
    function TagCloud($gadget = null, $user = 0)
    {
        if(empty($gadget)) {
            $gadget = jaws()->request->fetch('gname', 'get');
        }

        $user = empty($user)? 0 : (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Tags');
        $res = $model->GenerateTagCloud($gadget, $user);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

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

        $tpl = $this->gadget->template->load('TagCloud.html');
        $tpl->SetBlock('tagcloud');

        if(!empty($gadget)) {
            $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
            Jaws_Translate::getInstance()->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);
            $tpl->SetVariable('title', _t('TAGS_TAG_CLOUD', _t(strtoupper($gadget) . '_TITLE')));
        } else {
            $tpl->SetVariable('title', _t('TAGS_TAG_CLOUD', _t('GLOBAL_ALL')));
        }

        if ($user) {
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
            if($user) {
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
            $tpl->SetVariable('url', $this->gadget->urlMap('ViewTag', array('tag'=>$tag, 'gname'=>$gadget)));
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

        $page = $get['page'];
        if (is_null($page) || !is_numeric($page) || $page <= 0 ) {
            $page = 1;
        }

        $limit = (int)$this->gadget->registry->fetch('tag_results_limit');
        if (empty($limit)) {
            $limit = 10;
        }

        // Fetch tag references count(for paging)
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('count(tags.id):integer');
        $table->join('tags_references', 'tags_references.tag', 'tags.id');
        if(!empty($gadget)) {
            $table->where('gadget', $gadget);
        }
        $table->and()->where('tags.name', $tag)->and()->where('tags_references.published', true);
        if(!empty($get['user'])) {
            $table->and()->where('tags.user', $get['user']);
        } else {
            $table->and()->where('tags.user', 0);
        }
        $referencesCount = $table->fetchOne();
        if (Jaws_Error::IsError($referencesCount)) {
            return false;
        }

        if ($referencesCount > 0) {
            // Fetch tag references
            $table = Jaws_ORM::getInstance()->table('tags');
            $table->select('gadget', 'action', 'reference:integer');
            $table->join('tags_references', 'tags_references.tag', 'tags.id');
            if(!empty($gadget)) {
                $table->where('gadget', $gadget);
            }
            $table->and()->where('tags.name', $tag)->and()->where('tags_references.published', true);
            if(!empty($get['user'])) {
                $table->and()->where('tags.user', $get['user']);
            } else {
                $table->and()->where('tags.user', 0);
            }
            $references = $table->orderBy('insert_time')->limit($limit, ($page - 1) * $limit)->fetchAll();
            if (Jaws_Error::IsError($references)) {
                return false;
            }
        }

        $gadgetReferences = array();
        // grouping references by gadget for one time call hook per gadget
        foreach ($references as $reference) {
            $gadgetReferences[$reference['gadget']][$reference['action']][] = $reference['reference'];
        }

        $tpl = $this->gadget->template->load('Tag.html');
        $tpl->SetBlock('tag');
        $tpl->SetVariable('title', _t('TAGS_VIEW_TAG', $tag));

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'tag',
            $page,
            $limit,
            $referencesCount,
            _t('TAGS_TAG_ITEM_COUNT', $referencesCount),
            'ViewTag',
            array('tag'=>$tag)
        );

        if (count($references) > 2) {
            $tpl->SetBlock('tag/subtitle');
            $tpl->SetVariable('text', _t('SEARCH_RESULTS_SUBTITLE', $referencesCount, $tag));
            $tpl->ParseBlock('tag/subtitle');
        }

        $objDate = Jaws_Date::getInstance();
        $max_result_len = (int)$this->gadget->registry->fetch('max_result_len');
        if (empty($max_result_len)) {
            $max_result_len = 500;
        }

        // call gadget hook
        foreach ($gadgetReferences as $gadget => $references) {
            // load gadget
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }

            // load hook
            $objHook = $objGadget->hook->load('Tags');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            // call execute method
            $result = $objHook->Execute($references);
            if (!Jaws_Error::IsError($result) && !empty($result)) {
                $tpl->SetBlock('tags/gadget');
                $tpl->SetVariable('gadget_result', _t('SEARCH_RESULTS_IN_GADGETS',
                    count($result),
                    'TEST',
                    $objGadget->title));
                $tpl->ParseBlock('tags/gadget');
                foreach ($result as $reference) {
                    $tpl->SetBlock('tag/tag_item');
                    $tpl->SetVariable('title',  $reference['title']);
                    $tpl->SetVariable('url',    $reference['url']);
                    $tpl->SetVariable('target', (@$reference['outer'])? '_blank' : '_self');
                    $tpl->SetVariable('image',  $reference['image']);
                    if (!isset($reference['parse_text']) || $reference['parse_text']) {
                        $reference['snippet'] = $this->gadget->ParseText($reference['snippet'], $gadget);
                    }
                    if (!isset($reference['strip_tags']) || $reference['strip_tags']) {
                        $reference['snippet'] = strip_tags($reference['snippet']);
                    }
                    $reference['snippet'] = Jaws_UTF8::substr($reference['snippet'], 0, $max_result_len);

                    $tpl->SetVariable('snippet', $reference['snippet']);
                    $tpl->SetVariable('date', $objDate->Format($reference['date']));
                    $tpl->ParseBlock('tag/tag_item');
                }
            }
        }

        $tpl->ParseBlock('tag');
        return $tpl->Get();
    }

}