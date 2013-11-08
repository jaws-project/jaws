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
class LinkDump_Actions_Groups extends Jaws_Gadget_Action
{
    /**
     * Get Category action params
     *
     * @access  public
     * @return  array list of Category action params
     */
    function CategoryLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => _t('GLOBAL_CATEGORY'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Show links of the category
     *
     * @access  public
     * @return  mixed  XHTML template content or false on error
     */
    function Category($gid = 0)
    {
        if (empty($gid)) {
            $gid = jaws()->request->fetch('id', 'get');
            $gid = Jaws_XSS::defilter($gid, true);
            $limit_count = null;
            $tplFile = 'Category.html';
        } else {
            $limit_count = 10;
            $tplFile = 'LinkDump.html';
        }

        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group)) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate($tplFile);
        $tpl->SetBlock('category');

        $tpl->SetVariable('gid',   $group['id']);
        $tpl->SetVariable('title', _t('LINKDUMP_NAME'));
        $tpl->SetVariable('name', $group['title']);
        $tpl->SetVariable('feed', _t('LINKDUMP_LINKS_FEED'));

        $gid = empty($group['fast_url'])? $group['id'] : $group['fast_url'];
        $tpl->SetVariable('url_category', $this->gadget->urlMap('Category', array('id' => $gid)));

        $group_id = empty($group['fast_url'])? $group['id'] : $group['fast_url'];
        $tpl->SetVariable('linkdump_rss', $this->gadget->urlMap('RSS', array('id' => $group_id)));

        $target = $this->gadget->registry->fetch('links_target');
        $target = ($target == 'blank') ? '_blank' : '_self';
        $block = ($group['link_type'] == 0) ? 'list' : 'link';

        $links = $model->GetGroupLinks(
            $group['id'],
            empty($limit_count)? null : $group['limit_count'],
            $group['order_type']
        );
        if (!Jaws_Error::IsError($links)) {
            foreach ($links as $link) {
                $tpl->SetBlock("category/$block");
                $tpl->SetVariable('target',      $target);
                $tpl->SetVariable('title',       $link['title']);
                $tpl->SetVariable('description', $link['description']);
                $tpl->SetVariable('url',         $link['url']);
                $tpl->SetVariable('clicks',      $link['clicks']);
                $tpl->SetVariable('lbl_clicks',  _t('LINKDUMP_LINKS_CLICKS'));
                if ($group['link_type'] == 2) {
                    $lid = empty($link['fast_url'])? $link['id'] : $link['fast_url'];
                    $tpl->SetVariable('visit_url', $this->gadget->urlMap('Link', array('id' => $lid)));
                } else {
                    $tpl->SetVariable('visit_url', $link['url']);
                }
                $tpl->ParseBlock("category/$block");
            }
        }

        $tpl->ParseBlock('category');
        return $tpl->Get();
    }

    /**
     * Display links categories
     *
     * @access  public
     * @return  XHTML template content
     */
    function Categories()
    {
        $model = $this->gadget->model->load('Groups');
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
            $tpl->SetVariable('url',   $this->gadget->urlMap('Category', array('id' => $gid)));
            $tpl->SetVariable('title', $group['title']);
            $tpl->ParseBlock('categories/item');
        }

        $tpl->ParseBlock('categories');
        return $tpl->Get();
   }
}