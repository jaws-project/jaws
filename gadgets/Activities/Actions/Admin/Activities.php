<?php
/**
 * Activities Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Activities
 */
class Activities_Actions_Admin_Activities extends Activities_Actions_Admin_Default
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Activities()
    {
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Activities.html');
        $tpl->SetBlock('Activities');

        $model = $this->gadget->model->load('Activities');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Activities'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
        $fromDate->showTimePicker(false);
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d');
        $fromDate->AddEvent(ON_CHANGE, "javascript:searchActivities();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', $this::t('FROM_DATE'));

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
        $toDate->showTimePicker(false);
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d');
        $toDate->AddEvent(ON_CHANGE, "javascript:searchActivities();");
        $tpl->SetVariable('filter_to_date', $toDate->Get());
        $tpl->SetVariable('lbl_filter_to_date', $this::t('TO_DATE'));

        // Gadgets Filter
        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
        $gadgetsCombo->AddOption(Jaws::t('ALL'), "", false);
        $gadgets = $model->GetHookedGadgets();
        foreach ($gadgets as $name => $title) {
            $gadgetsCombo->AddOption($title, $name);
        }
        $gadgetsCombo->AddEvent(ON_CHANGE, "javascript:searchActivities();");
        $gadgetsCombo->SetDefault(-1);
        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
        $tpl->SetVariable('lbl_filter_gadget', Jaws::t('GADGETS'));

        // Domains
        $allDomains = $model->GetAllDomains();
        $domainCombo =& Piwi::CreateWidget('Combo', 'filter_domain');
        $domainCombo->AddOption(Jaws::t('ALL'), -1, false);
        foreach ($allDomains as $domain) {
            if (empty($domain)) {
                $domainCombo->AddOption($this::t('MY_DOMAIN'), '', false);
            } else {
                $domainCombo->AddOption($domain, $domain, false);
            }
        }
        $domainCombo->AddEvent(ON_CHANGE, "javascript:searchActivities();");
        $domainCombo->SetDefault('');
        $tpl->SetVariable('filter_domain', $domainCombo->Get());
        $tpl->SetVariable('lbl_filter_domain', $this::t('DOMAIN'));

        // Order
        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(Jaws::t('DATE'). ' &darr;', 'date');
        $orderType->AddOption(Jaws::t('DATE'). ' &uarr;', 'date desc');
        $orderType->AddOption($this::t('HITS'). ' &darr;', 'hits');
        $orderType->AddOption($this::t('HITS'). ' &uarr;', 'hits desc');
        $orderType->AddEvent(ON_CHANGE, "javascript:searchActivities();");
        $orderType->SetDefault(-1);
        $tpl->SetVariable('order_type', $orderType->Get());
        $tpl->SetVariable('lbl_order_type', $this::t('ORDER_TYPE'));

        //DataGrid
        $tpl->SetVariable('datagrid', $this->ActivitiesDataGrid());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'sa_actions');
        $actions->SetID('sa_actions_combo');
        $actions->SetTitle(Jaws::t('ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        if ($this->gadget->GetPermission('DeleteActivities')) {
            $actions->AddOption(Jaws::t('DELETE'), 'delete');
            $actions->AddOption($this::t('DELETE_ALL'), 'deleteAll');
        }
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeActivitiesAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:activitiesDGAction($('#sa_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());


        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $this->gadget->define('confirmActivitiesDelete', Jaws::t('CONFIRM_DELETE'));

        $tpl->ParseBlock('Activities');
        return $tpl->Get();
    }

    /**
     * Builds Activities datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function ActivitiesDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('datagrid');
        $grid->useMultipleSelection();
        $grid->pageBy(15);

        $column1 = Piwi::CreateWidget('Column', $this::t('DOMAIN'), null, false);
        $column1->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', Jaws::t('GADGETS'), null, false);
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column3->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', $this::t('HITS'), null, false);
        $column4->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', Jaws::t('DATE'), null, false);
        $column5->SetStyle('width:128px; white-space:nowrap;');
        $grid->AddColumn($column5);

        return $grid->Get();
    }

    /**
     * Return list of Activities data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetActivities()
    {
        $post = $this->gadget->request->fetch(array('offset', 'order', 'filters:array'), 'post');
        $filters = $post['filters'];

        $model = $this->gadget->model->load('Activities');
        $activities = $model->GetActivities($filters, 15, $post['offset'], $post['order']);
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
     * Get activities count
     *
     * @access  public
     * @return  int     Total of activities
     */
    function GetActivitiesCount()
    {
        $filters = $this->gadget->request->fetch('filters:array', 'post');
        $model = $this->gadget->model->load('Activities');
        return $model->GetActivitiesCount($filters);
    }

    /**
     * Delete activity
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteActivities()
    {
        $this->gadget->CheckPermission('DeleteActivities');
        $activity = $this->gadget->request->fetchAll();
        $model = $this->gadget->model->loadAdmin('Activities');
        $res = $model->DeleteActivities($activity);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CANT_DELETE_ACTIVITIES'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('ACTIVITIES_DELETED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete all activity
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteAllActivities()
    {
        $this->gadget->CheckPermission('DeleteActivities');
        $model = $this->gadget->model->loadAdmin('Activities');
        $res = $model->DeleteAllActivities();
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CANT_DELETE_ACTIVITIES'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('ACTIVITIES_DELETED'), RESPONSE_NOTICE);
        }
    }

}