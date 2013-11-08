<?php
/**
 * Logs Gadget
 *
 * @category   GadgetAdmin
 * @package    Logs
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 */
class Logs_Actions_Admin_Logs extends Logs_Actions_Admin_Default
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Logs()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Logs.html');
        $tpl->SetBlock('Logs');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Logs'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
        $fromDate->showTimePicker(true);
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $fromDate->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', _t('LOGS_FROM_DATE'));

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
        $toDate->showTimePicker(true);
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $toDate->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $tpl->SetVariable('filter_to_date', $toDate->Get());
        $tpl->SetVariable('lbl_filter_to_date', _t('LOGS_TO_DATE'));

        // Gadgets Filter
        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
        $gadgetsCombo->AddOption(_t('LOGS_ALL_GADGETS'), "", false);
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();
        foreach ($gadgetList as $gadget) {
            $gadgetsCombo->AddOption($gadget['title'], $gadget['name']);
        }
        $gadgetsCombo->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $gadgetsCombo->SetDefault(-1);
        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
        $tpl->SetVariable('lbl_filter_gadget', _t('GLOBAL_GADGETS'));

        // Users Filter
        $usersCombo =& Piwi::CreateWidget('Combo', 'filter_user');
        $usersCombo->AddOption(_t('GLOBAL_ALL_USERS'), "", false);
        $userModel = new Jaws_User();
        $users = $userModel->GetUsers();
        if (!Jaws_Error::IsError($users)) {
            foreach ($users as $user) {
                $usersCombo->AddOption($user['username'] . ' - ' . $user['nickname'], $user['id']);
            }
        }
        $usersCombo->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $usersCombo->SetDefault(-1);
        $tpl->SetVariable('filter_user', $usersCombo->Get());
        $tpl->SetVariable('lbl_filter_user', _t('LOGS_USERS'));

        // Priority
        $priorityCombo =& Piwi::CreateWidget('Combo', 'filter_priority');
        $priorityCombo->AddOption(_t('GLOBAL_ALL'), 0, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_INFO'), Logs_Info::LOGS_PRIORITY_INFO, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_NOTICE'), Logs_Info::LOGS_PRIORITY_NOTICE, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_WARNING'), Logs_Info::LOGS_PRIORITY_WARNING, false);
        $priorityCombo->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $priorityCombo->SetDefault(0);
        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
        $tpl->SetVariable('lbl_filter_priority', _t('LOGS_LOG_PRIORITY'));

        // Term
        $filterTerm =& Piwi::CreateWidget('Entry', 'filter_term', '');
        $filterTerm->SetID('filter_term');
        $filterTerm->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $filterTerm->AddEvent(ON_KPRESS, "javascript: OnTermKeypress(this, event);");
        $tpl->SetVariable('lbl_filter_term', _t('LOGS_SEARCH_TERM'));
        $tpl->SetVariable('filter_term', $filterTerm->Get());

        //DataGrid
        $tpl->SetVariable('grid', $this->LogsDataGrid());

        //LogUI
        $tpl->SetVariable('log_ui', $this->LogUI());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('confirmLogsDelete', _t('LOGS_CONFIRM_DELETE'));
        $tpl->SetVariable('legend_title',     _t('LOGS_LOG_DETAILS'));

        $tpl->ParseBlock('Logs');
        return $tpl->Get();
    }

    /**
     * Builds logs datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function LogsDataGrid()
    {
        $model = $this->gadget->model->loadAdmin('Logs');
        $total = $model->TotalOfData('logs');

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('logs_box');
        $gridBox->SetStyle('width: 100%;');

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('logs_datagrid');
        $grid->TotalRows($total);
        $grid->useMultipleSelection();
        $grid->pageBy(12);

        $column0 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $column0->SetStyle('width:100px; white-space:nowrap;');
        $grid->AddColumn($column0);

        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_GADGETS'), null, false);
        $column1->SetStyle('width:100px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('LOGS_ACTION'), null, false);
        $column2->SetStyle('width:72px; white-space:nowrap;');
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'), null, false);
        $column3->SetStyle('width:160px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('LOGS_USER_NICKNAME'), null, false);
        $column4->SetStyle('width:160px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column6 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column6->SetStyle('width:135px; white-space:nowrap;');
        $grid->AddColumn($column6);

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('logs_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');
        $actions =& Piwi::CreateWidget('Combo', 'logs_actions');
        $actions->SetID('logs_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');

        $execute =& Piwi::CreateWidget('Button', 'executeLogsAction', '',
            STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript: logsDGAction(document.getElementById('logs_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($grid);
        $gridBox->Add($gridForm);

        return $gridBox->Get();
    }

    /**
     * Show a form to show a given log
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function LogUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Logs.html');
        $tpl->SetBlock('LogUI');

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_gadget', _t('GLOBAL_GADGETS'));
        $tpl->SetVariable('lbl_action', _t('LOGS_ACTION'));
        $tpl->SetVariable('lbl_backend', _t('LOGS_LOG_SCRIPT'));
        $tpl->SetVariable('lbl_priority', _t('LOGS_LOG_PRIORITY'));
        $tpl->SetVariable('lbl_status', _t('LOGS_LOG_STATUS'));
        $tpl->SetVariable('lbl_apptype', _t('LOGS_LOG_REQUEST_TYPE'));
        $tpl->SetVariable('lbl_username', _t('GLOBAL_USERNAME'));
        $tpl->SetVariable('lbl_nickname', _t('LOGS_USER_NICKNAME'));
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        $tpl->SetVariable('lbl_agent', _t('LOGS_AGENT'));
        $tpl->SetVariable('lbl_date', _t('GLOBAL_DATE'));

        $tpl->ParseBlock('LogUI');
        return $tpl->Get();
    }

    /**
     * Return list of Logs data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLogs()
    {
        $post = jaws()->request->fetch(array('offset', 'filters:array'), 'post');
        $filters = $post['filters'];

        $model = $this->gadget->model->loadAdmin('Logs');
        $logs = $model->GetLogs($filters, 12, $post['offset']);
        if (Jaws_Error::IsError($logs)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $newData = array();
        foreach ($logs as $log) {
            $logData = array();
            $logData['__KEY__'] = $log['id'];

            // Title
            $link =& Piwi::CreateWidget('Link', $log['title'],
                "javascript:viewLog(this, '".$log['id']."');");
            $logData['title'] = $link->get();

            // Gadget
            $logData['gadget'] = $log['gadget'];
            // Action
            $logData['action'] = $log['action'];
            // User - Username
            $logData['username'] = $log['username'];
            // User - Nickname
            $logData['nickname'] = $log['nickname'];
            // Date
            $logData['time'] = $date->Format($log['insert_time'], 'Y-m-d H:i:s');

            $newData[] = $logData;
        }
        return $newData;
    }

    /**
     * Get logs count
     *
     * @access  public
     * @return  int     Total of tags
     */
    function GetLogsCount()
    {
        $filters = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Logs');
        return $model->GetLogsCount($filters);
    }

    /**
     * Return info of selected log
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLog()
    {
        $id = jaws()->request->fetch('id');
        $logModel = $this->gadget->model->loadAdmin('Logs');
        $log = $logModel->GetLog($id);
        if (Jaws_Error::IsError($log)) {
            return array();
        }

        switch ($log['priority']) {
            case Logs_Info::LOGS_PRIORITY_NOTICE :
                $priority = _t('LOGS_PRIORITY_NOTICE');
                break;
            case Logs_Info::LOGS_PRIORITY_WARNING :
                $priority = _t('LOGS_PRIORITY_WARNING');
                break;
            default:
                $priority = _t('LOGS_PRIORITY_INFO');
        }

        $date = $GLOBALS['app']->loadDate();
        $log['insert_time'] = $date->Format($log['insert_time'], 'DN d MN Y H:i:s');
        $log['ip'] = long2ip($log['ip']);
        $log['priority'] = $priority;
        $log['backend'] = $log['backend'] ? _t('LOGS_LOG_SCRIPT_ADMIN') : _t('LOGS_LOG_SCRIPT_SCRIPT');

        // user's profile
        $log['user_url'] = $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'Profile',
                array('user' => $log['username'])
        );

        return $log;
    }

    /**
     * Delete Logs
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteLogs()
    {
        $logsID = jaws()->request->fetchAll();
        $model = $this->gadget->model->loadAdmin('Logs');
        $res = $model->DeleteLogs($logsID);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_ERROR_CANT_DELETE_LOGS'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_LOGS_DELETED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}