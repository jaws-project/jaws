<?php
/**
 * Banner Gadget
 *
 * @category   Gadget
 * @package    Banner
 */
class Banner_Actions_Banners extends Jaws_Gadget_Action
{
    /**
     * Get then Banners action params
     *
     * @access  public
     * @return  array list of the Banners action params
     */
    function BannersLayoutParams()
    {
        $result = array();
        $bModel = $this->gadget->model->load('Groups');
        $groups = $bModel->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => _t('BANNER_GROUPS_GROUP'),
                'value' => $pgroups
            );
        }

        // domains
        if ($this->gadget->registry->fetch('multi_domain', 'Users') == 'true') {
            $domains = Jaws_Gadget::getInstance('Users')->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                $pdomains = array();
                $pdomains[-1] = _t('USERS_ALLDOMAIN');
                $pdomains[0]  = _t('USERS_NODOMAIN');
                foreach ($domains as $domain) {
                    $pdomains[$domain['id']] = $domain['title'];
                }

                $result[] = array(
                    'title' => _t('GLOBAL_DOMAIN'),
                    'value' => $pdomains
                );
            }
        }

        return $result;
    }

    /**
     * Displays banners(all-time visibles and random ones)
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   int     $domain     Domain ID
     * @return  string  XHTML template content
     */
    function Banners($gid = 0, $domain = -1)
    {
        $get = $this->gadget->request->fetch(array('group', 'domain'), 'get');
        $abs_url = false;

        if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
            $get['domain'] = 0;
        }

        if(!empty($get['group'])) {
            $gid = (int)$get['group'];
            header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 200 OK");
            $abs_url = true;
        }

        if(!is_null($get['domain'])) {
            $domain = (int)$get['domain'];
        }

        $groupModel = $this->gadget->model->load('Groups');
        $group = $groupModel->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $bannerModel = $this->gadget->model->load('Banners');
        $banners = $bannerModel->GetVisibleBanners($gid, $domain, $group['limit_count']);
        if (Jaws_Error::IsError($banners) || empty($banners)) {
            return false;
        }

        $tpl = $this->gadget->template->load('Banners.html');
        switch ($group['show_type']) {
            case 1:
            case 2:
                $type_block = 'banners_type_'. $group['show_type'];
                break;
            default:
                $type_block = 'banners';
        }

        $tpl->SetBlock($type_block);
        $tpl->SetVariable('gid', $gid);
        if ($group['show_title']) {
            $tpl->SetBlock("$type_block/title");
            $tpl->SetVariable('title', _t('BANNER_ACTIONS_BANNERS_TITLE', $group['title']));
            $tpl->ParseBlock("$type_block/title");
        }

        foreach ($banners as $banner) {
            $tpl->SetBlock("$type_block/banner");
            $tpl_template = new Jaws_Template();
            $tpl_template->LoadFromString('<!-- BEGIN x -->'.$banner['template'].'<!-- END x -->');
            $tpl_template->SetBlock('x');
            $tpl_template->SetVariable('title',  $banner['title']);
            if (file_exists(JAWS_DATA . $this->gadget->DataDirectory . $banner['banner'])) {
                $tpl_template->SetVariable(
                    'banner',
                    $GLOBALS['app']->getDataURL($this->gadget->DataDirectory . $banner['banner'])
                );
            } else {
                $tpl_template->SetVariable('banner', $banner['banner']);
            }

            if (empty($banner['url'])) {
                $tpl_template->SetVariable('link', 'javascript:void(0);');
                $tpl_template->SetVariable('target', '_self');
            } else {
                $tpl_template->SetVariable(
                    'link',
                    $this->gadget->urlMap(
                        'Click',
                        array('id' => $banner['id']),
                        array('absolute' => $abs_url)
                    )
                );
                $tpl_template->SetVariable('target', '_blank');
            }
            $tpl_template->ParseBlock('x');
            $tpl->SetVariable('template', $tpl_template->Get());
            unset($tpl_template);
            $tpl->ParseBlock("$type_block/banner");
            $bannerModel->ViewBanner($banner['id']);
        }

        $tpl->ParseBlock($type_block);
        return $tpl->Get();
    }

    /**
     * Redirects request to banner's target
     *
     * @access  public
     * @return  mixed    Void if Success, 404  XHTML template content on Failure
     */
    function Click()
    {
        $model = $this->gadget->model->load('Banners');
        $id = (int)$this->gadget->request->fetch('id', 'get');
        $banner = $model->GetBanners($id);
        if (!Jaws_Error::IsError($banner) && !empty($banner)) {
            $click = $model->ClickBanner($banner[0]['id']);
            if (!Jaws_Error::IsError($click)) {
                $link = implode('/', array_map('rawurlencode', explode('/', $banner[0]['url'])));
                return Jaws_Header::Location($link);
            }
        } else {
            return Jaws_HTTPError::Get(404);
        }
    }

}