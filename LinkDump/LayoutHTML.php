<?php
/**
 * LinkDump Gadget (for layout actions)
 *
 * @category   GadgetLayout
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayLayoutParams()
    {
        $result = array();
        $lModel = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $groups = $lModel->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => _t('LINKDUMP_ACTIONS_DISPLAY'),
                'value' => $pgroups
            );
        }

        return $result;
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

        $data = array();
        $data['gid']   = $group['id'];
        $data['title'] = _t('LINKDUMP_NAME');
        $data['name']  = $group['title'];
        $data['lbl_clicks'] = _t('LINKDUMP_LINKS_CLICKS');
        $data['lst_simple'] = $group['link_type'] == 0;
        $target = $this->gadget->registry->fetch('links_target');
        $data['target'] = ($target == 'blank')? '_blank' : '_self';
        $feedname = empty($group['fast_url'])?
                    $GLOBALS['app']->UTF8->str_replace(' ', '-', $group['title']) : $group['fast_url'];
        $feedname = preg_replace('/[@?^=%&:;\/~\+# ]/i', '\1', $feedname);
        $data['linkdump_rdf']  = $GLOBALS['app']->getDataURL("xml/linkdump.$feedname.rdf", false);
        $data['feed'] = _t('LINKDUMP_LINKS_FEED');
        $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
        $data['archive_url'] = $this->gadget->GetURLFor('Archive', array('id' => $gid));
        $data['links'] = $model->GetGroupLinks($group['id'], $group['limit_count'], $group['order_type']);
        if (!Jaws_Error::IsError($data['links'])) {
            foreach ($data['links'] as $indx => $link) {
                if ($group['link_type'] == 2) {
                    $lid = empty($link['fast_url'])? $link['id'] : $link['fast_url'];
                    $data['links'][$indx]['url'] = $this->gadget->GetURLFor('Link', array('id' => $lid));
                } else {
                    $data['links'][$indx]['url'] = $link['url'];
                }
            }
        }

        $tpl = $this->gadget->loadTemplate('LinkDump.html');
        return $tpl->fetch($data);
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

        $tpl = $this->gadget->loadTemplate('Categories.html');
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