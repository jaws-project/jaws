<?php
/**
 * Tags Gadget
 *
 * @category   Gadget
 * @package    Tags
 */
class Tags_Actions_Tags extends Jaws_Gadget_Action
{
    /**
     * Main request reference
     *
     * @var     array
     * @access  private
     */
    static private $mainRequestReference = array();

    /**
     * Main request tags
     *
     * @var     array
     * @access  private
     */
    static private $mainRequestTags = array();

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
        array_unshift($gadgets, Jaws::t('ALL'));
        $result[] = array(
            'title' => $this::t('GADGET'),
            'value' => $gadgets
        );

        $result[] = array(
            'title' => $this::t('SHOW_TAGS'),
            'value' => array(
                0 => $this::t('GLOBAL_TAGS'),
                1 => $this::t('USER_TAGS'),
            )
        );

        return $result;
    }

    /**
     * Displays Tags Cloud
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   int     $user   Only show user tags?
     * @return  string  XHTML template content
     */
    function TagCloud($gadget = null, $user = 0)
    {
        if (!empty($user) && !$this->app->session->user->logged) {
            return false;
        }

        if ($this->app->requestedActionMode == ACTION_MODE_NORMAL) {
            $gadget = $this->gadget->request->fetch('tagged_gadget', 'get');
        }

        $user = empty($user)? 0 : (int)$this->app->session->user->id;
        $model = $this->gadget->model->load('Tags');
        $tags = $model->GenerateTagCloud($gadget, $user);
        if (Jaws_Error::IsError($tags) || empty($tags)) {
            return false;
        }

        // find minimum/maximum frequencies
        $frequencies = array_column($tags, 'howmany');
        sort($frequencies);
        $minTagCount = log($frequencies[0]);
        $maxTagCount = log(end($frequencies));
        unset($frequencies);

        // calculate font-size step
        if ($minTagCount == $maxTagCount) {
            $tagCountRange = 1;
        } else {
            $tagCountRange = $maxTagCount - $minTagCount;
        }
        $minFontSize = 0;
        $maxFontSize = 9;
        $fontSizeRange = $maxFontSize - $minFontSize;

        $tpl = $this->gadget->template->load('TagCloud.html');
        $tpl->SetBlock('tagcloud');

        if(!empty($gadget)) {
            $tpl->SetVariable('title', $this::t('TAG_CLOUD', _t(strtoupper($gadget) . '_TITLE')));
        } else {
            $tpl->SetVariable('title', $this::t('TAG_CLOUD', Jaws::t('ALL')));
        }

        if ($this->app->session->user->logged &&
            $this->app->requestedActionMode == ACTION_MODE_NORMAL
        ) {
            // Menu navigation
            $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        }

        foreach ($tags as $tag) {
            $count  = $tag['howmany'];
            $fsize = $minFontSize + $fontSizeRange * (log($count) - $minTagCount)/$tagCountRange;
            $tpl->SetBlock('tagcloud/tag');
            $tpl->SetVariable('size', (int)$fsize);
            $tpl->SetVariable('tagname',  Jaws_UTF8::strtolower($tag['title']));
            $tpl->SetVariable('frequency', $tag['howmany']);
            if (empty($gadget)) {
                $param = array('tag' => $tag['name']);
            } else {
                $param = array('tag' => $tag['name'], 'tagged_gadget' => $gadget);
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
     * Get reference tags
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $action         Action name
     * @param   int     $reference      Reference ID
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @param   int     $user           User owner of tag(0: for global tags)
     * @return  void
     */
    function loadReferenceTags($gadget, $action, $reference, &$tpl, $tpl_base_block, $user = 0)
    {
        $tagsModel = $this->gadget->model->load('Tags');
        $tags = $tagsModel->GetReferenceTags($gadget, $action, $reference, $user);
        if (Jaws_Error::IsError($tags)) {
            return false;
        }

        if (!empty($tags)) {
            // store tags of main request for later use
            if ($this->app->inMainRequest) {
                self::$mainRequestReference = array(
                    'gadget' => $gadget,
                    'action' => $action,
                    'reference' => $reference
                );
                self::$mainRequestTags = array_column($tags, 'id');
            }

            $tpl->SetBlock("$tpl_base_block/tags");
            $tpl->SetVariable('lbl_tags', Jaws::t('TAGS'));
            foreach($tags as $tag) {
                $tpl->SetBlock("$tpl_base_block/tags/tag");
                $tpl->SetVariable('name', $tag['name']);
                $tpl->SetVariable('title', $tag['title']);
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap('ViewTag', array('tag'=>$tag['name'], 'tagged_gadget'=>$gadget))
                );
                $tpl->ParseBlock("$tpl_base_block/tags/tag");
            }
            $tpl->ParseBlock("$tpl_base_block/tags");
        }

    }

    /**
     * Get reference tags
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(reference, pagination_data, user, per_page, order_by, ...)
     * @return  array   Tag's template variables
     */
    function xloadReferenceTags($interface = array(), $options = array())
    {
        $defaultOptions = array(
            'user'       => 0,
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);

        $tags = $this->gadget->model->load('Tags')->GetReferenceTags(
            $interface['gadget'],
            $interface['action'],
            $interface['reference'],
            $options['user']
        );
        if (Jaws_Error::IsError($tags)) {
            $tags = array();
        }

        // initiate assign with option array
        $assigns = array();
        $assigns['gadget'] = $interface['gadget'];
        $assigns['action'] = $interface['action'];
        $assigns['reference'] = $interface['reference'];
        $assigns['tags'] = $tags;

        if (!empty($tags)) {
            // store tags of main request for later use
            if ($this->app->inMainRequest) {
                self::$mainRequestReference = array(
                    'gadget' => $interface['gadget'],
                    'action' => $interface['action'],
                    'reference' => $interface['reference']
                );
                self::$mainRequestTags = array_column($tags, 'id');
            }
        }

        return $assigns;
    }

    /**
     * Display a Tag
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTag()
    {
        $get = $this->gadget->request->fetch(array('tag', 'tagged_gadget', 'page', 'user'), 'get');
        $tag = $get['tag'];
        $gadget = $get['tagged_gadget'];
        $user = (int)$get['user'];
        if (!empty($user) && ($user != $this->app->session->user->id)) {
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

        $tagsModel = $this->gadget->model->load('Tags');
        $tagInfo = $tagsModel->GetTagByName($tag);

        // Fetch tag references count(for paging)
        $referencesCount = $tagsModel->GetTagsCount($gadget, $tag, $user);
        $references = array();
        if ($referencesCount > 0) {
            // Fetch tag references
            $references = $tagsModel->GetTags($gadget, $tag, $user, $limit, ($page - 1) * $limit);
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
        $tpl->SetVariable('title', $this::t('VIEW_TAG', $tag));
        if ($this->app->session->user->logged) {
            // Menu navigation
            $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        }
        $this->SetTitle($this::t('VIEW_TAG', $tag));
        $this->AddToMetaKeywords($tagInfo['meta_keywords']);
        $this->SetDescription($tagInfo['meta_description']);

        // pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $limit,
            $referencesCount,
            'ViewTag',
            array('tag'=>$tag),
            $this::t('TAG_ITEM_COUNT', $referencesCount)
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
        foreach ($gadgetReferences as $gadget => $action_references) {
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

            foreach ($action_references as $action => $references) {
                // call execute method
                $result = $objHook->Execute($action, $references);
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
                            $reference['snippet'] = $this->gadget->plugin->parse(
                                $reference['snippet'],
                                Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
                                0,
                                '',
                                $gadget
                            );
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
        }

        $tpl->ParseBlock('tag');
        return $tpl->Get();
    }

    /**
     * Get then Similarity action params
     *
     * @access  public
     * @return  array list of the Similarity action params
     */
    function SimilarityLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Tags');
        $gadgets = $model->GetTagableGadgets();
        array_unshift($gadgets, Jaws::t('ALL'));
        $result[] = array(
            'title' => $this::t('GADGET'),
            'value' => $gadgets
        );

        $result[] = array(
            'title' => $this::t('SHOW_TAGS'),
            'value' => array(
                0 => $this::t('GLOBAL_TAGS'),
                1 => $this::t('USER_TAGS'),
            )
        );

        return $result;
    }

    /**
     * Displays entries by tags similarity
     *
     * @access  public
     * @param   string  $reqGadget  Gadget name
     * @param   int     $user       Only show user tags?
     * @return  string  XHTML template content
     */
    function Similarity($reqGadget, $user)
    {
        if (empty(self::$mainRequestTags)) {
            return false;
        }

        $model = $this->gadget->model->load('Tags');
        $references = $model->GetSimilartyTags(self::$mainRequestTags, $reqGadget, $user);
        if (Jaws_Error::IsError($references) || empty($references)) {
            return false;
        }

        $gadgetReferences = array();
        // grouping references by gadget/action for one time call hook per gadget
        foreach ($references as $reference) {
            if ($reference['gadget'] == self::$mainRequestReference['gadget'] &&
                $reference['action'] == self::$mainRequestReference['action'] &&
                $reference['reference'] == self::$mainRequestReference['reference']
             ) {
                continue;
            }

            $gadgetReferences[$reference['gadget']][$reference['action']][] = $reference['reference'];
        }

        // call gadget hook
        foreach ($gadgetReferences as $gadget => $actions) {
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

            // communicate with gadget Tags hook 
            foreach ($actions as $action => $action_references) {
                // call execute method
                $result = $objHook->Execute($action, $action_references);
                if (!Jaws_Error::IsError($result) && !empty($result)) {
                    $gadgetReferences[$gadget][$action] = $result;
                } else {
                    $gadgetReferences[$gadget][$action] = array();
                }
            }
        }

        $objDate = Jaws_Date::getInstance();
        $max_result_len = (int)$this->gadget->registry->fetch('max_result_len');
        if (empty($max_result_len)) {
            $max_result_len = 500;
        }

        $tpl = $this->gadget->template->load('Similarity.html');
        $tpl->SetBlock('similarity');
        $tpl->SetVariable('title', $this::t('SIMILARITY'));
        if (!empty($reqGadget)) {
            $objGadget = Jaws_Gadget::getInstance($reqGadget);
            if (!Jaws_Error::IsError($objGadget)) {
                $tpl->SetVariable('title', $this::t('SIMILARITY_IN', $objGadget->title));
            }
        }

        // provide return result
        foreach ($references as $reference) {
            if (!@array_key_exists(
                $reference['reference'],
                $gadgetReferences[$reference['gadget']][$reference['action']]
                )
            ) {
                continue;
            }

            $reference = $gadgetReferences[$reference['gadget']][$reference['action']][$reference['reference']];
            $tpl->SetBlock('similarity/reference');
            $tpl->SetVariable('title',  $reference['title']);
            $tpl->SetVariable('url',    $reference['url']);
            $tpl->SetVariable('target', (@$reference['outer'])? '_blank' : '_self');
            $tpl->SetVariable('image',  $reference['image']);
            if (!isset($reference['parse_text']) || $reference['parse_text']) {
                $reference['snippet'] = $this->gadget->plugin->parse(
                    $reference['snippet'],
                    Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
                    0,
                    '',
                    $gadget
                );
            }
            if (!isset($reference['strip_tags']) || $reference['strip_tags']) {
                $reference['snippet'] = strip_tags($reference['snippet']);
            }
            $reference['snippet'] = Jaws_UTF8::substr($reference['snippet'], 0, $max_result_len);

            $tpl->SetVariable('snippet', $reference['snippet']);
            $tpl->SetVariable('date', $objDate->Format($reference['date']));
            $tpl->ParseBlock('similarity/reference');
        }

        $tpl->ParseBlock('similarity');
        return $tpl->Get();
    }

}