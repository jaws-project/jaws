<?php
/**
 * SiteActivity Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     SiteActivity
 */
class SiteActivity_Actions_Admin_SiteActivity extends SiteActivity_Actions_Admin_Default
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function SiteActivity()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('SiteActivity.html');
        $tpl->SetBlock('SiteActivity');

        $model = $this->gadget->model->load('SiteActivity');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('SiteActivity'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
        $fromDate->showTimePicker(false);
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d');
        $fromDate->AddEvent(ON_CHANGE, "javascript:searchSiteActivity();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', _t('SITEACTIVITY_FROM_DATE'));

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
        $toDate->showTimePicker(false);
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d');
        $toDate->AddEvent(ON_CHANGE, "javascript:searchSiteActivity();");
        $tpl->SetVariable('filter_to_date', $toDate->Get());
        $tpl->SetVariable('lbl_filter_to_date', _t('SITEACTIVITY_TO_DATE'));

        // Gadgets Filter
        $gadgets = $model->GetSiteActivityGadgets();

        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
        $gadgetsCombo->AddOption(_t('GLOBAL_ALL'), "", false);
        foreach ($gadgets as $name=>$title) {
            $gadgetsCombo->AddOption($title, $name);
        }
        $gadgetsCombo->AddEvent(ON_CHANGE, "javascript:searchSiteActivity();");
        $gadgetsCombo->SetDefault(-1);
        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
        $tpl->SetVariable('lbl_filter_gadget', _t('GLOBAL_GADGETS'));

        // Domains
        $allDomains = $model->GetAllDomains();
        $domainCombo =& Piwi::CreateWidget('Combo', 'filter_domain');
        $domainCombo->AddOption(_t('GLOBAL_ALL'), -1, false);
        foreach ($allDomains as $domain) {
            if (empty($domain)) {
                $domainCombo->AddOption(_t('SITEACTIVITY_MY_DOMAIN'), '', false);
            } else {
                $domainCombo->AddOption($domain, $domain, false);
            }
        }
        $domainCombo->AddEvent(ON_CHANGE, "javascript:searchSiteActivity();");
        $domainCombo->SetDefault('');
        $tpl->SetVariable('filter_domain', $domainCombo->Get());
        $tpl->SetVariable('lbl_filter_domain', _t('SITEACTIVITY_DOMAIN'));

        // Order
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('GLOBAL_DATE'). ' &darr;', 'date');
        $orderType->AddOption(_t('GLOBAL_DATE'). ' &uarr;', 'date desc');
        $orderType->AddOption(_t('SITEACTIVITY_HITS'). ' &darr;', 'hits');
        $orderType->AddOption(_t('SITEACTIVITY_HITS'). ' &uarr;', 'hits desc');
        $orderType->AddEvent(ON_CHANGE, "javascript:searchSiteActivity();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', _t('SITEACTIVITY_ORDER_TYPE'));

        //DataGrid
        $tpl->SetVariable('datagrid', $this->SiteActivityDataGrid());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'sa_actions');
        $actions->SetID('sa_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        if ($this->gadget->GetPermission('DeleteSiteActivity')) {
            $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
            $actions->AddOption(_t('SITEACTIVITY_DELETE_ALL'), 'deleteAll');
        }
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeSiteActivityAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:siteActivityDGAction($('#sa_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());


        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('confirmSiteActivityDelete', _t('GLOBAL_CONFIRM_DELETE'));

        $tpl->ParseBlock('SiteActivity');
        return $tpl->Get();
    }

    /**
     * Builds SiteActivity datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function SiteActivityDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('sa_datagrid');
        $grid->useMultipleSelection();
        $grid->pageBy(15);

        $column1 = Piwi::CreateWidget('Column', _t('SITEACTIVITY_DOMAIN'), null, false);
        $column1->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_GADGETS'), null, false);
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('SITEACTIVITY_HITS'), null, false);
        $column4->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column5->SetStyle('width:128px; white-space:nowrap;');
        $grid->AddColumn($column5);

        return $grid->Get();
    }

    /**
     * Return list of SiteActivity data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetSiteActivities()
    {
        $post = jaws()->request->fetch(array('offset', 'order', 'filters:array'), 'post');
        $filters = $post['filters'];

        $model = $this->gadget->model->load('SiteActivity');
        $activities = $model->GetSiteActivities($filters, 15, $post['offset'], $post['order']);
        if (Jaws_Error::IsError($activities)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $gridData = array();
        foreach ($activities as $activity) {
            $activityData = array();
            $activityData['__KEY__'] = $activity['id'];

            // Domain
            $activityData['domain'] = $activity['domain'];
            // Gadget
            if (!empty($activity['gadget'])) {
                $activityData['gadget'] = _t(strtoupper($activity['gadget'] . '_TITLE'));
            } else {
                $activityData['gadget'] = '';
            }
            // Action
            $activityData['action'] = $activity['action'];
            // Hits
            $activityData['hits'] = $activity['hits'];
            // Date
            $activityData['date'] = $date->Format($activity['date'], 'Y-m-d');
            $gridData[] = $activityData;
        }
        return $gridData;
    }

    /**
     * Get site activities count
     *
     * @access  public
     * @return  int     Total of site activities
     */
    function GetSiteActivitiesCount()
    {
        $filters = jaws()->request->fetch('filters:array', 'post');
        $model = $this->gadget->model->load('SiteActivity');
        return $model->GetSiteActivitiesCount($filters);
    }

    /**
     * Delete site activity
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteSiteActivities()
    {
        $this->gadget->CheckPermission('DeleteSiteActivity');
        $siteActivityID = jaws()->request->fetchAll();
        $model = $this->gadget->model->loadAdmin('SiteActivity');
        $res = $model->DeleteSiteActivities($siteActivityID);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('SITEACTIVITY_ERROR_CANT_DELETE_ACTIVITIES'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('SITEACTIVITY_SITEACTIVITY_DELETED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete all site activity
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteAllSiteActivities()
    {
        $this->gadget->CheckPermission('DeleteSiteActivity');
        $model = $this->gadget->model->loadAdmin('SiteActivity');
        $res = $model->DeleteAllSiteActivities();
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('SITEACTIVITY_ERROR_CANT_DELETE_ACTIVITIES'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('SITEACTIVITY_SITEACTIVITY_DELETED'), RESPONSE_NOTICE);
        }
    }

}