<?php
/**
 * LinkDump Gadget (for layout actions)
 *
 * @category   GadgetLayout
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDumpLayoutHTML
{
    /**
     * Loads layout actions
     *
     * @access private
     * @return  array   actions array
     */
    function LoadLayoutActions()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $groups = $model->GetGroups();

        $actions = array();
        if (!Jaws_Error::isError($groups)) {
            foreach ($groups as $group) {
                $actions['Display(' . $group['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $group['title'],
                    'desc' => _t('LINKDUMP_LAYOUT_DISPLAY_DESCRIPTION')
                );
            }
        }

        return $actions;
    }

    /**
     * Display links
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  string  XHTML template content
     */
    function Display($gid = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group)) {
            return false;
        }

        $target = $GLOBALS['app']->Registry->Get('/gadgets/LinkDump/links_target');
        $target = ($target == 'blank')? '_blank' : '_self';
        $block  = ($group['link_type']==0)? 'list' : 'link';

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('LinkDump.html');
        $tpl->SetBlock('linkdump');

        $tpl->SetVariable('gid',     $group['id']);
        $tpl->SetVariable('title',   _t('LINKDUMP_NAME'));
        $tpl->SetVariable('name',    $group['title']);

        $feedname = empty($group['fast_url']) ?
                    $GLOBALS['app']->UTF8->str_replace(' ', '-', $group['title']) : $group['fast_url'];
        $feedname = preg_replace('/[@?^=%&:;\/~\+# ]/i', '\1', $feedname);

        $tpl->SetVariable('linkdump_rdf', $GLOBALS['app']->getDataURL("xml/linkdump.$feedname.rdf", false));
        $tpl->SetVariable('feed', _t('LINKDUMP_LINKS_FEED'));
        $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
        $tpl->SetVariable('archive_url', $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Archive', array('id' => $gid)));

        $links = $model->GetGroupLinks($group['id'], $group['limit_count'], $group['order_type']);
        if (!Jaws_Error::IsError($links)) {
            foreach ($links as $link) {
                $tpl->SetBlock("linkdump/$block");
                $tpl->SetVariable('target', $target);
                $tpl->SetVariable('title',  $link['title']);
                $tpl->SetVariable('description', $link['description']);
                if ($group['link_type'] == 2) {
                    $tpl->SetVariable('clicks',  $link['clicks']);
                    $tpl->SetVariable('lbl_clicks', _t('LINKDUMP_LINKS_CLICKS'));
                    $lid = empty($link['fast_url'])? $link['id'] : $link['fast_url'];
                    $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Link', array('id' => $lid)));
                } else {
                    $tpl->SetVariable('url', $link['url']);
                }
                $tpl->ParseBlock("linkdump/$block");
            }
        }

        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

    /**
     * Display a Tag Cloud
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ShowTagCloud()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
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

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('TagCloud.html');
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

    /**
     * Display links categories
     *
     * @access  public
     * @return  XHTML template content
     */
    function ShowCategories()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $groups = $model->GetGroups();
        if (Jaws_Error::IsError($group)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('Categories.html');
        $tpl->SetBlock('categories');
        $tpl->SetVariable('title', _t('LINKDUMP_GROUPS'));

        foreach ($groups as $group) {
            $tpl->SetBlock('categories/item');
            $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
            $tpl->SetVariable('url',   $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Group', array('id' => $gid)));
            $tpl->SetVariable('title', $group['title']);
            $tpl->ParseBlock('categories/item');
        }

        $tpl->ParseBlock('categories');
        return $tpl->Get();
   }

}