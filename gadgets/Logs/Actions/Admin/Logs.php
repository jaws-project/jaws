<?php
/**
 * Logs Gadget
 *
 * @category     GadgetAdmin
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
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
        $this->gadget->define('confirmLogsDelete', _t('LOGS_CONFIRM_DELETE'));
        $this->gadget->define('msgNoMatches', _t('LOGS_COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('noMatchesMessage', Jaws::t('COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));

        $this->gadget->define('LANGUAGE', array(
            'gadget'=> Jaws::t('GADGETS'),
            'action'=> _t('LOGS_ACTION'),
            'auth'=> Jaws::t('AUTHTYPE'),
            'username'=> Jaws::t('USERNAME'),
            'time'=> Jaws::t('DATE'),
            'view'=> Jaws::t('VIEW'),
            'delete'=> Jaws::t('DELETE')
        ));

        $gadgetList = Jaws_Gadget::getInstance('Components')->model->load('Gadgets')->GetGadgetsList();
        $gadgetList = Jaws_Error::IsError($gadgetList) ? array() : $gadgetList;
        $this->gadget->define('gadgetList', array_column($gadgetList, 'title', 'name'));

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Logs') : $menubar;
        $assigns['gadgets'] = $gadgetList;
        $assigns['from_date'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'from_date'));
        $assigns['to_date'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'to_date'));
        $assigns['priorityItems'] = array(
            JAWS_WARNING => _t('LOGS_PRIORITY_5'),
            JAWS_NOTICE => _t('LOGS_PRIORITY_6'),
            JAWS_INFO => _t('LOGS_PRIORITY_7'),
        );
        $assigns['statusItems'] = array(
            1 => _t('LOGS_LOG_STATUS_1'),
            2 => _t('LOGS_LOG_STATUS_2'),
        );
        return $this->gadget->template->xLoadAdmin('Logs.html')->render($assigns);




//        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
//        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
//        if ($calType != 'gregorian') {
//            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
//        }
//        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
//        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
//        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
//        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');
//
//        $this->AjaxMe('script.js');
//        $this->gadget->define('confirmLogsDelete', _t('LOGS_CONFIRM_DELETE'));
//        $this->gadget->define('msgNoMatches', _t('LOGS_COMBO_NO_MATCH_MESSAGE'));
//        $this->gadget->define('lbl_all_users', Jaws::t('ALL_USERS'));
//
//        $tpl = $this->gadget->template->loadAdmin('Logs.html');
//        $tpl->SetBlock('Logs');
//
//        //Menu bar
//        $tpl->SetVariable('menubar', $this->MenuBar('Logs'));
//
//        $tpl->SetVariable('lbl_all_users', Jaws::t('ALL_USERS'));
//        $tpl->SetVariable('lbl_filter_user', _t('LOGS_USERS'));
//
//        // From Date Filter
//        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
//        $fromDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
//        $fromDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
//        $fromDate->setDateFormat('%Y-%m-%d %H:%M:%S');
//        $fromDate->AddEvent(ON_CHANGE, "javascript:searchLogs();");
//        $tpl->SetVariable('filter_from_date', $fromDate->Get());
//        $tpl->SetVariable('lbl_filter_from_date', _t('LOGS_FROM_DATE'));
//
//        // To Date Filter
//        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
//        $toDate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
//        $toDate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
//        $toDate->setDateFormat('%Y-%m-%d %H:%M:%S');
//        $toDate->AddEvent(ON_CHANGE, "javascript:searchLogs();");
//        $tpl->SetVariable('filter_to_date', $toDate->Get());
//        $tpl->SetVariable('lbl_filter_to_date', _t('LOGS_TO_DATE'));
//
//        // Gadgets Filter
//        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
//        $gadgetsCombo->AddOption(_t('LOGS_ALL_GADGETS'), "", false);
//        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
//        $gadgetList = $cmpModel->GetGadgetsList();
//        foreach ($gadgetList as $gadget) {
//            $gadgetsCombo->AddOption($gadget['title'], $gadget['name']);
//        }
//        $gadgetsCombo->AddEvent(ON_CHANGE, "searchLogs();");
//        $gadgetsCombo->SetDefault(0);
//        $tpl->SetVariable('filter_gadget', $gadgetsCombo->Get());
//        $tpl->SetVariable('lbl_filter_gadget', Jaws::t('GADGETS'));
//
//        // Result Filter
//        $filterResult =& Piwi::CreateWidget('Entry', 'filter_result', '');
//        $filterResult->SetID('filter_result');
//        $filterResult->AddEvent(ON_CHANGE, "searchLogs();");
//        $tpl->SetVariable('lbl_filter_result', _t('LOGS_RESULT'));
//        $tpl->SetVariable('filter_result', $filterResult->Get());
//
//        // Priority
//        $priorityCombo =& Piwi::CreateWidget('Combo', 'filter_priority');
//        $priorityCombo->AddOption(Jaws::t('ALL'), 0, false);
//        $priorityCombo->AddOption(_t('LOGS_PRIORITY_5'), JAWS_WARNING, false);
//        $priorityCombo->AddOption(_t('LOGS_PRIORITY_6'), JAWS_NOTICE, false);
//        $priorityCombo->AddOption(_t('LOGS_PRIORITY_7'), JAWS_INFO, false);
//        $priorityCombo->AddEvent(ON_CHANGE, "javascript:searchLogs();");
//        $priorityCombo->SetDefault(0);
//        $tpl->SetVariable('filter_priority', $priorityCombo->Get());
//        $tpl->SetVariable('lbl_filter_priority', _t('LOGS_PRIORITY'));
//
//        // Status
//        $allStatus = array (1, 2);
//        $statusCombo =& Piwi::CreateWidget('Combo', 'filter_status');
//        $statusCombo->AddOption(Jaws::t('ALL'), 0, false);
//        foreach($allStatus as $status) {
//            $statusCombo->AddOption(_t('LOGS_LOG_STATUS_' . $status), $status, false);
//        }
//        $statusCombo->AddEvent(ON_CHANGE, "javascript:searchLogs();");
//        $statusCombo->SetDefault(0);
//        $tpl->SetVariable('filter_status', $statusCombo->Get());
//        $tpl->SetVariable('lbl_filter_status', _t('LOGS_LOG_STATUS'));
//
//        //DataGrid
//        $tpl->SetVariable('datagrid', $this->LogsDataGrid());
//
//        //LogUI
//        $tpl->SetVariable('log_ui', $this->LogUI());
//
//        // Actions
//        $actions =& Piwi::CreateWidget('Combo', 'logs_actions');
//        $actions->SetID('logs_actions_combo');
//        $actions->SetTitle(Jaws::t('ACTIONS'));
//        $actions->AddOption('&nbsp;', '');
//        if ($this->gadget->GetPermission('DeleteLogs')) {
//            $actions->AddOption(Jaws::t('DELETE'), 'delete');
//            $actions->AddOption(_t('LOGS_DELETE_ALL'), 'deleteAll');
//            $actions->AddOption(_t('LOGS_DELETE_FILTERED'), 'deleteFiltered');
//        }
//        if ($this->gadget->GetPermission('ExportLogs')) {
//            $actions->AddOption(_t('LOGS_EXPORT_ALL'), 'export');
//            $actions->AddOption(_t('LOGS_EXPORT_FILTERED'), 'exportFiltered');
//        }
//        $tpl->SetVariable('actions_combo', $actions->Get());
//
//        $btnExecute =& Piwi::CreateWidget('Button', 'executeLogsAction', '', STOCK_YES);
//        $btnExecute->AddEvent(ON_CLICK, "javascript:logsDGAction($('#logs_actions_combo'));");
//        $tpl->SetVariable('btn_execute', $btnExecute->Get());
//
//
//        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
//        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
//        $btnCancel->SetStyle('display:none;');
//        $tpl->SetVariable('btn_cancel', $btnCancel->Get());
//        $tpl->SetVariable('legend_title',     _t('LOGS_LOG_DETAILS'));
//
//        $tpl->ParseBlock('Logs');
//        return $tpl->Get();
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
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($post['filters'], $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($logs)) {
            return $this->gadget->session->response($logs->GetMessage(), RESPONSE_ERROR);
        }

        $logsCount = $model->GetLogsCount($post['filters']);
        if (Jaws_Error::IsError($logsCount)) {
            return $this->gadget->session->response($logsCount->GetMessage(), RESPONSE_ERROR);
        }

        if ($logsCount > 0) {
            $objDate = Jaws_Date::getInstance();
            foreach ($logs as &$log) {
                $log['time'] = $objDate->Format($log['time'], 'Y/m/d H:i:s');
            }
        }

//        $date = Jaws_Date::getInstance();
//        $newData = array();
//        foreach ($logs as $log) {
//            $logData = array();
//
//            // Gadget
//            if (!empty($log['gadget'])) {
//                $logData['gadget'] = _t(strtoupper($log['gadget'] . '_TITLE'));
//            } else {
//                $logData['gadget'] = '';
//            }
//
//            // Action
//            $logData['action'] = $log['action'];
//            // auth
//            $logData['auth'] = $log['auth'];
//            // Username
//            $logData['username'] = $log['username'];
//            // Date
//            $link =& Piwi::CreateWidget(
//                'Link',
//                $date->Format($log['time'], 'Y-m-d H:i:s'),
//                "javascript:viewLog(this, '".$log['id']."');"
//            );
//            $logData['time'] = $link->Get();
//            $newData[] = $logData;
//        }


        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $logsCount,
                'records' => $logs
            )
        );
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
            return $this->gadget->session->response($log->GetMessage(), RESPONSE_ERROR);
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

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $log
        );
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
        $post = $this->gadget->request->fetch(array('ids:array', 'filters:array'), 'post');
        $logsID = $post['ids'];
        $filters = $post['filters'];
        $model = $this->gadget->model->loadAdmin('Logs');
        if (!empty($logsID)) {
            $res = $model->DeleteLogs($logsID);
        } else {
            $res = $model->DeleteLogsUseFilters($filters);
        }
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('LOGS_ERROR_CANT_DELETE_LOGS'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('LOGS_LOGS_DELETED'),
            RESPONSE_NOTICE
        );

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
            array('from_date', 'to_date', 'gadget', 'action', 'user', 'priority', 'result', 'status'),
            'get'
        );

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($filters);
        if (Jaws_Error::IsError($logs) || count($logs) < 1) {
            return;
        }

        $tmpDir = sys_get_temp_dir() . '/';
        $tmpCSVFileName = uniqid(rand(), true) . '.csv';
        $fp = fopen($tmpDir . $tmpCSVFileName, 'w');

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
        $tmpArchiveName = $tmpDir . $tmpFileName;
        $writerObj = File_Archive::toFiles();
        $src = File_Archive::read($tmpDir . $tmpCSVFileName);
        $dst = File_Archive::toArchive($tmpArchiveName, $writerObj);
        $res = File_Archive::extract($src, $dst);
        if (!PEAR::isError($res)) {
            return Jaws_FileManagement_File::download($tmpArchiveName, $tmpFileName);
        }

        Jaws_Header::Referrer();
    }
}