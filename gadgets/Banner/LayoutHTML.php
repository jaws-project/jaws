<?php
/**
 * Banner Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Banner
 */
class Banner_LayoutHTML extends Jaws_Gadget_HTML
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
        $bModel = $GLOBALS['app']->LoadGadget('Banner', 'Model');
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

        return $result;
    }

    /**
     * Displays banners(all-time visibles and random ones)
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   bool    $abs_url    Absolute or relative URL
     * @return  string  XHTML template content
     */
    function Display($gid = 0, $abs_url = false)
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'Model');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $banners = $model->GetVisibleBanners($gid, $group['limit_count']);
        if (Jaws_Error::IsError($banners) || empty($banners)) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('Banners.html');
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
            $tpl->SetVariable('title', _t('BANNER_ACTIONS_DISPLAY_TITLE', $group['title']));
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
                $tpl_template->SetVariable('link',
                                           $GLOBALS['app']->Map->GetURLFor('Banner', 'Click',
                                                                           array('id' => $banner['id']),
                                                                           $abs_url));
                $tpl_template->SetVariable('target', '_blank');
            }
            $tpl_template->ParseBlock('x');
            $tpl->SetVariable('template', $tpl_template->Get());
            unset($tpl_template);
            $tpl->ParseBlock("$type_block/banner");
            $model->ViewBanner($banner['id']);
        }

        $tpl->ParseBlock($type_block);
        return $tpl->Get();
    }

}