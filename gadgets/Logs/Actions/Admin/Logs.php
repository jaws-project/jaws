<?php
/**
 * Logs Gadget
 *
 * @category     GadgetAdmin
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
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
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_5'), JAWS_WARNING, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_6'), JAWS_NOTICE, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_7'), JAWS_INFO, false);
        $priorityCombo->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $priorityCombo->SetDefault(0);
        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
        $tpl->SetVariable('lbl_filter_priority', _t('LOGS_PRIORITY'));

        // Status
        $allStatus = array (200, 301, 302, 401, 403, 404, 410, 500, 503);
        $statusCombo =& Piwi::CreateWidget('Combo', 'filter_status');
        $statusCombo->AddOption(_t('GLOBAL_ALL'), 0, false);
        foreach($allStatus as $status) {
            $statusCombo->AddOption(_t('GLOBAL_HTTP_ERROR_TITLE_' . $status), $status, false);
        }
        $statusCombo->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $statusCombo->SetDefault(0);
        $tpl->SetVariable('filter_status', $statusCombo->Get());
        $tpl->SetVariable('lbl_filter_status', _t('LOGS_LOG_STATUS'));

        //DataGrid
        $tpl->SetVariable('datagrid', $this->LogsDataGrid());

        //LogUI
        $tpl->SetVariable('log_ui', $this->LogUI());

        $actions =& Piwi::CreateWidget('Combo', 'logs_actions');
        $actions->SetID('logs_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'logs_actions');
        $actions->SetID('logs_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        if ($this->gadget->GetPermission('DeleteLogs')) {
            $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
            $actions->AddOption(_t('LOGS_DELETE_ALL'), 'deleteAll');
            $actions->AddOption(_t('LOGS_DELETE_FILTERED'), 'deleteFiltered');
        }
        if ($this->gadget->GetPermission('ExportLogs')) {
            $actions->AddOption(_t('LOGS_EXPORT_ALL'), 'export');
            $actions->AddOption(_t('LOGS_EXPORT_FILTERED'), 'exportFiltered');
        }
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeLogsAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:logsDGAction($('logs_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());


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
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('logs_datagrid');
        $grid->useMultipleSelection();
        $grid->pageBy(15);

        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_GADGETS'), null, false);
        $column1->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('LOGS_ACTION'), null, false);
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'), null, false);
        $column3->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('LOGS_USER_NICKNAME'), null, false);
        $column4->SetStyle('width:128px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column5->SetStyle('width:128px; white-space:nowrap;');
        $grid->AddColumn($column5);

        return $grid->Get();
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

        $tpl->SetVariable('lbl_gadget', _t('GLOBAL_GADGETS'));
        $tpl->SetVariable('lbl_action', _t('LOGS_ACTION'));
        $tpl->SetVariable('lbl_backend', _t('LOGS_LOG_SCRIPT'));
        $tpl->SetVariable('lbl_priority', _t('LOGS_PRIORITY'));
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

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($filters, 15, $post['offset']);
        if (Jaws_Error::IsError($logs)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $newData = array();
        foreach ($logs as $log) {
            $logData = array();
            $logData['__KEY__'] = $log['id'];

            // Gadget
            $logData['gadget'] = _t(strtoupper($log['gadget'].'_TITLE'));
            // Action
            $logData['action'] = $log['action'];
            // Username
            $logData['username'] = $log['username'];
            // Nickname
            $logData['nickname'] = $log['nickname'];
            // Date
            $link =& Piwi::CreateWidget(
                'Link',
                $date->Format($log['insert_time'], 'Y-m-d H:i:s'),
                "javascript:viewLog(this, '".$log['id']."');"
            );
            $logData['time'] = $link->Get();
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
        $filters = jaws()->request->fetch('filters:array', 'post');
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

        $date = Jaws_Date::getInstance();
        $log['insert_time'] = $date->Format($log['insert_time'], 'DN d MN Y H:i:s');
        $log['ip'] = long2ip($log['ip']);
        $log['priority'] = _t('LOGS_PRIORITY_'. $log['priority']);
        $log['status']   = _t('GLOBAL_HTTP_ERROR_TITLE_'. $log['status']);
        $log['backend']  = $log['backend']? _t('LOGS_LOG_SCRIPT_ADMIN') : _t('LOGS_LOG_SCRIPT_INDEX');

        // user's profile link
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
        $this->gadget->CheckPermission('DeleteLogs');
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

    /**
     * Delete Logs Use Selected Filters
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteLogsUseFilters()
    {
        $this->gadget->CheckPermission('DeleteLogs');
        $filters = jaws()->request->fetch('filters:array', 'post');

        $model = $this->gadget->model->loadAdmin('Logs');
        $res = $model->DeleteLogsUseFilters($filters);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_ERROR_CANT_DELETE_LOGS'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_LOGS_DELETED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Export Logs
     *
     * @access  public
     * @return  void
     */
    function ExportLogs()
    {
        $this->gadget->CheckPermission('ExportLogs');

        Jaws_Header::Referrer();
    }

}