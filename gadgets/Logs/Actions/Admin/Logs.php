<?php
/**
 * Logs Gadget
 *
 * @category     GadgetAdmin
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2020 Jaws Development Group
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
        $this->gadget->define('confirmLogsDelete', _t('LOGS_CONFIRM_DELETE'));
        $this->gadget->define('msgNoMatches', _t('LOGS_COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('lbl_all_users', Jaws::t('ALL_USERS'));

        $tpl = $this->gadget->template->loadAdmin('Logs.html');
        $tpl->SetBlock('Logs');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Logs'));

        $tpl->SetVariable('lbl_all_users', Jaws::t('ALL_USERS'));
        $tpl->SetVariable('lbl_filter_user', _t('LOGS_USERS'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $fromDate->AddEvent(ON_CHANGE, "javascript:searchLogs();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', _t('LOGS_FROM_DATE'));

        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $toDate->AddEvent(ON_CHANGE, "javascript:searchLogs();");
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
        $gadgetsCombo->AddEvent(ON_CHANGE, "searchLogs();");
        $gadgetsCombo->SetDefault(0);
        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
        $tpl->SetVariable('lbl_filter_gadget', Jaws::t('GADGETS'));

        // Result Filter
        $filterResult =& Piwi::CreateWidget('Entry', 'filter_result', '');
        $filterResult->SetID('filter_result');
        $filterResult->AddEvent(ON_CHANGE, "searchLogs();");
        $tpl->SetVariable('lbl_filter_result', _t('LOGS_RESULT'));
        $tpl->SetVariable('filter_result', $filterResult->Get());

        // Priority
        $priorityCombo =& Piwi::CreateWidget('Combo', 'filter_priority');
        $priorityCombo->AddOption(Jaws::t('ALL'), 0, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_5'), JAWS_WARNING, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_6'), JAWS_NOTICE, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_7'), JAWS_INFO, false);
        $priorityCombo->AddEvent(ON_CHANGE, "javascript:searchLogs();");
        $priorityCombo->SetDefault(0);
        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
        $tpl->SetVariable('lbl_filter_priority', _t('LOGS_PRIORITY'));

        // Status
        $allStatus = array (1, 2);
        $statusCombo =& Piwi::CreateWidget('Combo', 'filter_status');
        $statusCombo->AddOption(Jaws::t('ALL'), 0, false);
        foreach($allStatus as $status) {
            $statusCombo->AddOption(_t('LOGS_LOG_STATUS_' . $status), $status, false);
        }
        $statusCombo->AddEvent(ON_CHANGE, "javascript:searchLogs();");
        $statusCombo->SetDefault(0);
        $tpl->SetVariable('filter_status', $statusCombo->Get());
        $tpl->SetVariable('lbl_filter_status', _t('LOGS_LOG_STATUS'));

        //DataGrid
        $tpl->SetVariable('datagrid', $this->LogsDataGrid());

        //LogUI
        $tpl->SetVariable('log_ui', $this->LogUI());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'logs_actions');
        $actions->SetID('logs_actions_combo');
        $actions->SetTitle(Jaws::t('ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        if ($this->gadget->GetPermission('DeleteLogs')) {
            $actions->AddOption(Jaws::t('DELETE'), 'delete');
            $actions->AddOption(_t('LOGS_DELETE_ALL'), 'deleteAll');
            $actions->AddOption(_t('LOGS_DELETE_FILTERED'), 'deleteFiltered');
        }
        if ($this->gadget->GetPermission('ExportLogs')) {
            $actions->AddOption(_t('LOGS_EXPORT_ALL'), 'export');
            $actions->AddOption(_t('LOGS_EXPORT_FILTERED'), 'exportFiltered');
        }
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeLogsAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:logsDGAction($('#logs_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());


        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());
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

        $column1 = Piwi::CreateWidget('Column', Jaws::t('GADGETS'), null, false);
        $column1->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('LOGS_ACTION'), null, false);
        $column2->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', Jaws::t('AUTHTYPE'), null, false);
        $column3->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', Jaws::t('USERNAME'), null, false);
        $column4->SetStyle('width:96px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', Jaws::t('DATE'), null, false);
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

        $tpl->SetVariable('lbl_gadget', Jaws::t('GADGETS'));
        $tpl->SetVariable('lbl_action', _t('LOGS_ACTION'));
        $tpl->SetVariable('lbl_backend', _t('LOGS_LOG_SCRIPT'));
        $tpl->SetVariable('lbl_priority', _t('LOGS_PRIORITY'));
        $tpl->SetVariable('lbl_result', _t('LOGS_LOG_RESULT'));
        $tpl->SetVariable('lbl_status', _t('LOGS_LOG_STATUS'));
        $tpl->SetVariable('lbl_apptype', _t('LOGS_LOG_REQUEST_TYPE'));
        $tpl->SetVariable('lbl_auth', Jaws::t('AUTHTYPE'));
        $tpl->SetVariable('lbl_username', Jaws::t('USERNAME'));
        $tpl->SetVariable('lbl_ip', Jaws::t('IP'));
        $tpl->SetVariable('lbl_agent', _t('LOGS_AGENT'));
        $tpl->SetVariable('lbl_date', Jaws::t('DATE'));

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
        $post = $this->gadget->request->fetch(array('offset', 'filters:array'), 'post');
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
            if (!empty($log['gadget'])) {
                $logData['gadget'] = _t(strtoupper($log['gadget'] . '_TITLE'));
            } else {
                $logData['gadget'] = '';
            }
            // Action
            $logData['action'] = $log['action'];
            // auth
            $logData['auth'] = $log['auth'];
            // Username
            $logData['username'] = $log['username'];
            // Date
            $link =& Piwi::CreateWidget(
                'Link',
                $date->Format($log['time'], 'Y-m-d H:i:s'),
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
     * @return  int     Total of logs
     */
    function GetLogsCount()
    {
        $filters = $this->gadget->request->fetch('filters:array', 'post');
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
        $id = $this->gadget->request->fetch('id');
        $logModel = $this->gadget->model->loadAdmin('Logs');
        $log = $logModel->GetLog($id);
        if (Jaws_Error::IsError($log)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $log['time'] = $date->Format($log['time'], 'DN d MN Y H:i:s');
        $log['ip'] = long2ip($log['ip']);
        $log['priority'] = _t('LOGS_PRIORITY_'. $log['priority']);
        $log['status']   = _t('LOGS_LOG_STATUS_'. $log['status']);
        $log['backend']  = $log['backend']? _t('LOGS_LOG_SCRIPT_ADMIN') : _t('LOGS_LOG_SCRIPT_INDEX');

        // user's profile link
        $log['user_url'] = $this->app->map->GetMappedURL(
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
        $logsID = $this->gadget->request->fetchAll();
        $model = $this->gadget->model->loadAdmin('Logs');
        $res = $model->DeleteLogs($logsID);
        if (Jaws_Error::IsError($res) || $res === false) {
            $this->gadget->session->push(_t('LOGS_ERROR_CANT_DELETE_LOGS'),
                RESPONSE_ERROR);
        } else {
            $this->gadget->session->push(_t('LOGS_LOGS_DELETED'),
                RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
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
        $filters = $this->gadget->request->fetch('filters:array', 'post');

        $model = $this->gadget->model->loadAdmin('Logs');
        $res = $model->DeleteLogsUseFilters($filters);
        if (Jaws_Error::IsError($res) || $res === false) {
            $this->gadget->session->push(_t('LOGS_ERROR_CANT_DELETE_LOGS'),
                RESPONSE_ERROR);
        } else {
            $this->gadget->session->push(_t('LOGS_LOGS_DELETED'),
                RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
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

        $filters = $this->gadget->request->fetch(
            array('from_date', 'to_date', 'gname', 'user', 'priority', 'result', 'status'),
            'get'
        );
        $filters['gadget'] = $filters['gname'];
        unset( $filters['gname']);

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($filters);
        if (Jaws_Error::IsError($logs) || count($logs) < 1) {
            return;
        }

        $tmpDir = sys_get_temp_dir();
        $tmpCSVFileName = uniqid(rand(), true) . '.csv';
        $fp = fopen($tmpDir . DIRECTORY_SEPARATOR . $tmpCSVFileName, 'w');

        $date = Jaws_Date::getInstance();
        foreach ($logs as $log) {
            $exportData = '';
            $exportData .= $log['id'] . ',';
            $exportData .= $log['username'] . ',';
            $exportData .= $log['gadget'] . ',';
            $exportData .= $log['action'] . ',';
            $exportData .= $log['priority'] . ',';
            $exportData .= $log['apptype'] . ',';
            $exportData .= $log['backend'] . ',';
            $exportData .= long2ip($log['ip']) . ',';
            $exportData .= $log['result'] . ',';
            $exportData .= $log['status'] . ',';
            $exportData .= $date->Format($log['time'], 'Y-m-d H:i:s');
            $exportData .= PHP_EOL;
            fwrite($fp, $exportData);
        }
        fclose($fp);

        require_once PEAR_PATH. 'File/Archive.php';
        $tmpFileName = uniqid(rand(), true) . '.tar.gz';
        $tmpArchiveName = $tmpDir. DIRECTORY_SEPARATOR. $tmpFileName;
        $writerObj = File_Archive::toFiles();
        $src = File_Archive::read($tmpDir . DIRECTORY_SEPARATOR . $tmpCSVFileName);
        $dst = File_Archive::toArchive($tmpArchiveName, $writerObj);
        $res = File_Archive::extract($src, $dst);
        if (!PEAR::isError($res)) {
            return $this->app->fileManagement::download($tmpArchiveName, $tmpFileName);
        }

        Jaws_Header::Referrer();
    }

    /**
     * Search user (used in pillbox)
     *
     * @access  public
     * @return  void
     */
    function GetUser()
    {
        $term = $this->gadget->request->fetch('username', 'post');
        $userModel = new Jaws_User();
        $user = $userModel->GetUserByTerm(0, $term);
        if (Jaws_Error::IsError($user)) {
            return $this->gadget->session->response(
                $user->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $user
        );
    }

}