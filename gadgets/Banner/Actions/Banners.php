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
                'title' => $this::t('GROUPS_GROUP'),
                'value' => $pgroups
            );
        }

        // domains
        if ($this->gadget->registry->fetch('multi_domain', 'Users') == 'true') {
            $domains = Jaws_Gadget::getInstance('Users')->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                $pdomains = array();
                $pdomains[-1] = $this::t('USERS.ALLDOMAIN');
                $pdomains[0]  = $this::t('USERS.NODOMAIN');
                foreach ($domains as $domain) {
                    $pdomains[$domain['id']] = $domain['title'];
                }

                $result[] = array(
                    'title' => Jaws::t('DOMAIN'),
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

        $assigns = array();
        $assigns['group'] = $group;
        $assigns['banners'] = $banners;

        $tFilename = 'Banners'. (in_array($group['show_type'], [1, 2, 3])? $group['show_type'] : 1) . '.html';
        return $this->gadget->template->xLoad($tFilename)->render($assigns);
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
                return Jaws_Header::Location($banner[0]['url']);
            }
        } else {
            return Jaws_HTTPError::Get(404);
        }
    }

}