<?php
/**
 * StaticPage Gadget
 *
 * @category   Gadget
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Group extends Jaws_Gadget_Action
{
    /**
     * Get GroupPages action params
     *
     * @access  public
     * @return  array list of GroupPages action params
     */
    function GroupPagesLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Group');
        $groups = $model->GetGroups(true);
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => Jaws::t('GROUP'),
                'value' => $pgroups
            );

            $result[] = array(
                'title' => Jaws::t('ORDERBY'),
                'value' => array(
                    0 => Jaws::t('CREATETIME'). ' &uarr;',
                    1 => Jaws::t('CREATETIME'). ' &darr;',
                    2 => Jaws::t('TITLE'). ' &uarr;',
                    3 => Jaws::t('TITLE'). ' &darr;',
                    4 => Jaws::t('UPDATETIME'). ' &uarr;',
                    5 => Jaws::t('UPDATETIME'). ' &darr;',
                )
            );

            $result[] = array(
                'title' => Jaws::t('COUNT'),
                'value' => $this->gadget->registry->fetch('group_pages_limit')
            );

        }

        return $result;
    }

    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   mixed   $gid    ID or fast_url of the group (int/string)
     * @param   int     $orderBy
     * @param   int     $limit    limit show pages
     * @return  string  XHTML content
     */
    function GroupPages($gid = 0, $orderBy = 1, $limit = 0)
    {
        if (empty($gid)) {
            $get = $this->gadget->request->fetch(array('gid', 'order'), 'get');
            $gid = Jaws_XSS::defilter($get['gid']);
            $orderBy = @$get['order'];
        }

        $pModel = $this->gadget->model->load('Page');
        $gModel = $this->gadget->model->load('Group');
        $group = $gModel->GetGroup($gid);
        if (Jaws_Error::IsError($group) || $group == null) {
            return false;
        }
        if (!$this->gadget->GetPermission('AccessGroup', $group['id'])) {
            return Jaws_HTTPError::Get(403);
        }

        $this->app->layout->SetTitle($group['title']);
        $this->app->layout->AddToMetaKeywords($group['meta_keywords']);
        $this->app->layout->SetDescription($group['meta_description']);

        if (!is_numeric($gid)) {
            $gid = $group['id'];
        }

        $pages = $pModel->GetPages($gid, $limit, $orderBy, null, true);
        if (Jaws_Error::IsError($pages)) {
            return false;
        }

        $tpl = $this->gadget->template->load('StaticPage.html');
        $tpl->SetBlock('group_pages');
        $tpl->SetVariable('title', $group['title']);
        foreach ($pages as $page) {
            $param = array('gid' => empty($group['fast_url'])? $group['id'] : $group['fast_url'],
                'pid' => empty($page['fast_url']) ? $page['base_id'] : $page['fast_url']);
            $link = $this->gadget->urlMap('Pages', $param);
            $tpl->SetBlock('group_pages/item');
            $tpl->SetVariable('page', $page['title']);
            $tpl->SetVariable('link',  $link);
            $tpl->ParseBlock('group_pages/item');
        }

        // parsing read-more block if required
        if (!empty($limit) &&
            count($pages) >= $limit &&
            $this->app->requestedActionMode == ACTION_MODE_LAYOUT
        ) {
            $urlParam = array(
                'gid'   => empty($group['fast_url'])? $group['id'] : $group['fast_url'],
                'order' => $orderBy
            );
            // prevent duplicate content via two different url
            if ($orderBy == 1) {
                unset($urlParam['order']);
            }

            $tpl->SetBlock('group_pages/read-more');
            $tpl->SetVariable('url', $this->gadget->urlMap('GroupPages',$urlParam));
            $tpl->SetVariable('read_more', $this::t('GROUP_PAGES_LIST', $group['title']));
            $tpl->ParseBlock('group_pages/read-more');
        }

        $tpl->ParseBlock('group_pages');
        return $tpl->Get();
    }

    /**
     * Displays a block of groups
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GroupsList()
    {
        $model = $this->gadget->model->load('Group');
        $groups = $model->GetGroups(true);
        if (Jaws_Error::IsError($groups)) {
            return false;
        }

        $tpl = $this->gadget->template->load('StaticPage.html');
        $tpl->SetBlock('group_index');
        $tpl->SetVariable('title', $this::t('GROUPS_LIST'));
        foreach ($groups as $group) {
            if (!$this->gadget->GetPermission('AccessGroup', $group['id'])) {
                continue;
            }
            $gid = empty($group['fast_url'])? $group['id'] : $group['fast_url'];
            $link = $this->gadget->urlMap('GroupPages', array('gid' => $gid));
            $tpl->SetBlock('group_index/item');
            $tpl->SetVariable('group', $group['title']);
            $tpl->SetVariable('link',  $link);
            $tpl->ParseBlock('group_index/item');
        }
        $tpl->ParseBlock('group_index');

        return $tpl->Get();
    }
}