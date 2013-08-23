<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_TagCloud extends Jaws_Gadget_HTML
{
    /**
     * Display a Tag Cloud
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ShowTagCloud()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Links');
        $res = $model->CreateTagCloud();
        if (Jaws_Error::IsError($res) || empty($res)) {
            return false;
        }

        $sortedTags = $res;
        sort($sortedTags);
        $minTagCount = log($sortedTags[0]['howmany']);
        $maxTagCount = log($sortedTags[count($res) - 1]['howmany']);
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
        $tpl->SetVariable('title', _t('LINKDUMP_LINKS_TAGCLOUD'));

        foreach ($res as $key => $value) {
            $count  = $value['howmany'];
            $fsize = $minFontSize + $fontSizeRange * (log($count) - $minTagCount)/$tagCountRange;
            $tpl->SetBlock('tagcloud/tag');
            $tpl->SetVariable('size', (int)$fsize);
            $tpl->SetVariable('tagname',  $value['tag']);
            $tpl->SetVariable('frequency', $value['howmany']);
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Tag', array('tag' => $value['tag'])));
            $tpl->SetVariable('category', $value['tag_id']);
            $tpl->ParseBlock('tagcloud/tag');
        }

        $tpl->ParseBlock('tagcloud');
        return $tpl->Get();
    }
}