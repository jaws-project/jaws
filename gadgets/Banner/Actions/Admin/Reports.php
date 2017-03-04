<?php
/**
 * Banner Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Banner
 */
class Banner_Actions_Admin_Reports extends Banner_Actions_Admin_Default
{
    /**
     * Get all the data
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $offset
     * @return  array   Data array
     */
    function GetReportBanners($gid, $offset = null)
    {
        $model = $this->gadget->model->load('Banners');
        $banners = $model->GetBanners(-1, $gid, 18, $offset);
        if (Jaws_Error::IsError($banners)) {
            return array();
        }

        $new_banners = array();
        $objDate = Jaws_Date::getInstance();
        foreach ($banners as $banner) {
            $item = array();
            $item['title']  = '<span><a href="'.$banner['url'].'" title="'.$banner['url'];
            $item['title'] .= '" target="_blank" style="text-decoration: none;">'.$banner['title'].'</a></span>';
            $item['views']  = $banner['views']. '/'.
                ($banner['views_limitation']==0? '&#8734;' : $banner['views_limitation']);
            $item['clicks'] = $banner['clicks']. '/'.
                ($banner['clicks_limitation']==0? '&#8734;' : $banner['clicks_limitation']);
            $item['start']  = '-';
            $item['stop']   = '-';
            if (!empty($banner['start_time'])) {
                $item['start'] = $objDate->Format($banner['start_time'], 'Y-m-d');
            }
            if (!empty($banner['stop_time'])) {
                $item['stop'] = $objDate->Format($banner['stop_time'], 'Y-m-d');
            }
            $item['status'] = (($banner['random']==1)?
                _t('BANNER_REPORTS_BANNERS_STATUS_RANDOM'):
                _t('BANNER_REPORTS_BANNERS_STATUS_ALWAYS')) . '/';
            $item['status'].= $banner['published']? _t('BANNER_REPORTS_BANNERS_STATUS_VISIBLE') :
                _t('BANNER_REPORTS_BANNERS_STATUS_INVISIBLE');

            $actions = '';
            if ($this->gadget->GetPermission('ManageBanners')) {
                $link =& Piwi::CreateWidget('Link', _t('BANNER_BANNERS_RESET_VIEWS'),
                    "javascript:resetViews('".$banner['id']."');",
                    STOCK_REFRESH);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('BANNER_BANNERS_RESET_CLICKS'),
                    "javascript:resetClicks('".$banner['id']."');",
                    STOCK_RESET);
                $actions.= $link->Get().'&nbsp;';
            }
            $item['actions']= $actions;

            $new_banners[]  = $item;
        }
        return $new_banners;
    }

    /**
     * View report
     *
     * @access  public
     * @return  string     XHTML template content
     */
    function Reports()
    {
        $this->gadget->CheckPermission('ViewReports');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('BannerReports.html');
        $tpl->SetBlock('Reports');

        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('banners', 'id');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(18);
        $datagrid->SetID('reports_datagrid');

        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('BANNER_BANNERS_VIEWS'), null, false);
        $column2->SetStyle('width: 64px; white-space:nowrap;');
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('BANNER_BANNERS_CLICKS'), null, false);
        $column3->SetStyle('width: 64px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_START_TIME'), null, false);
        $column4->SetStyle('width: 80px; white-space:nowrap;');
        $datagrid->AddColumn($column4);
        $column5 = Piwi::CreateWidget('Column', _t('GLOBAL_STOP_TIME'), null, false);
        $column5->SetStyle('width: 80px; white-space:nowrap;');
        $datagrid->AddColumn($column5);
        $column6 = Piwi::CreateWidget('Column', _t('GLOBAL_STATUS'), null, false);
        $column6->SetStyle('width: 120px; white-space:nowrap;');
        $datagrid->AddColumn($column6);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');
        $column7 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column7->SetStyle('width: 60px; white-space:nowrap;');
        $datagrid->AddColumn($column7);

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));
        $tpl->SetVariable('datagrid', $datagrid->Get());

        //Group filter
        $bGroup =& Piwi::CreateWidget('Combo', 'bgroup_filter');
        $bGroup->setStyle('min-width:200px;');
        $bGroup->AddEvent(ON_CHANGE, "getBannersDataGrid('reports_datagrid', 0, true)");
        $bGroup->AddOption('&nbsp;', -1);
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $bGroup->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('bgroup_filter', $bGroup->Get());
        $tpl->SetVariable('lbl_bgroup', _t('BANNER_GROUPS_GROUP'));

        $this->gadget->define('confirmResetBannerViews',  _t('BANNER_BANNERS_CONFIRM_RESET_VIEWS'));
        $this->gadget->define('confirmResetBannerClicks', _t('BANNER_BANNERS_CONFIRM_RESET_CLICKS'));

        $tpl->ParseBlock('Reports');
        return $tpl->Get();

    }
}