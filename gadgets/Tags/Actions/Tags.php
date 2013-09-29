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
class Tags_Actions_Tags extends Jaws_Gadget_HTML
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
        $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('LinkDump', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => _t('TAGS_GADGET'),
            'value' => array(
                '' => _t('GLOBAL_ALL') ,
                'Blog' => _t('BLOG_NAME') ,
                'LinkDump' => _t('LINKDUMP_NAME') ,
            )
        );

        return $result;
    }

    /**
     * Displays Tags Cloud
     *
     * @access  public
     * @param   string  $gadget gadget name
     * @return  string  XHTML template content
     */
    function TagCloud($gadget)
    {
        $model = $GLOBALS['app']->LoadGadget('Tags', 'Model', 'Tags');
        $res = $model->GenerateTagCloud($gadget);
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

        foreach ($res as $tag) {
            $count  = $tag['howmany'];
            $fsize = $minFontSize + $fontSizeRange * (log($count) - $minTagCount)/$tagCountRange;
            $tpl->SetBlock('tagcloud/tag');
            $tpl->SetVariable('size', (int)$fsize);
            $tpl->SetVariable('tagname',  Jaws_UTF8::strtolower($tag['name']));
            $tpl->SetVariable('frequency', $tag['howmany']);
            $tpl->SetVariable('url', $this->gadget->urlMap(
                'ViewTag',
                array('id' => $tag['id'], 'name' => $tag['name'])));
            $tpl->ParseBlock('tagcloud/tag');
        }
        $tpl->ParseBlock('tagcloud');

        return $tpl->Get();
    }

    /**
     * Display a Tag
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTag()
    {
        $get = jaws()->request->fetch(array('name', 'id'), 'get');
        $model = $GLOBALS['app']->LoadGadget('Tags', 'Model', 'Tags');
        $tagItems = $model->GetTagItems($get['id']);

        $tpl = $this->gadget->loadTemplate('Tag.html');
        $tpl->SetBlock('tag');
        $tpl->SetVariable('title', _t('TAGS_VIEW_TAG', $get['name']));

        foreach ($tagItems as $item) {
            $tpl->SetBlock('tag/item');
            $tpl->SetVariable('content_title',  $item['gadget']);
            $tpl->SetVariable('content_url', '');
            $tpl->ParseBlock('tag/item');
        }
        $tpl->ParseBlock('tag');

        return $tpl->Get();
    }

}