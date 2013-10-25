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
class Logs_Actions_Admin_Logs extends Jaws_Gadget_Action
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Logs()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadTemplate('Logs.html');
        $tpl->SetBlock('Logs');

        //Menu bar
        //$tpl->SetVariable('menubar', $this->MenuBar('Logs'));

        // From Date Filter
        $fromDate =& Piwi::CreateWidget('DatePicker', 'from_date', '');
        $fromDate->showTimePicker(true);
        $fromDate->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $fromDate->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $fromDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $fromDate->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $tpl->SetVariable('filter_from_date', $fromDate->Get());
        $tpl->SetVariable('lbl_filter_from_date', _t('LOGS_FROM_DATE'));


        // To Date Filter
        $toDate =& Piwi::CreateWidget('DatePicker', 'to_date', '');
        $toDate->showTimePicker(true);
        $toDate->setLanguageCode($this->gadget->registry->fetch('calendar_language', 'Settings'));
        $toDate->setCalType($this->gadget->registry->fetch('calendar_type', 'Settings'));
        $toDate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $toDate->AddEvent(ON_CHANGE, "javascript: searchLogs();");
        $tpl->SetVariable('filter_to_date', $toDate->Get());
        $tpl->SetVariable('lbl_filter_to_date', _t('LOGS_TO_DATE'));

        // Gadgets Filter
        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'filter_gadget');
        $gadgetsCombo->AddOption(_t('LOGS_ALL_GADGETS'), "", false);
        $cmpModel = Jaws_Gadget::getInstance('Components')->loadModel('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();
        $gadgets = array_keys($gadgetList);
        foreach ($gadgets as $gadget) {
            $gadgetsCombo->AddOption($gadget, $gadget);
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
        $model = $this->gadget->loadAdminModel('Logs');
        $total = $model->TotalOfData('logs');

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('logs_box');
        $gridBox->SetStyle('width: 100%;');

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('logs_datagrid');
        $grid->TotalRows($total);
        $grid->useMultipleSelection();
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_GADGETS'), null, false);
        $column1->SetStyle('width:100px; white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('LOGS_ACTION'), null, false);
        $column2->SetStyle('width:72px; white-space:nowrap;');
        $grid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('LOGS_USER'), null, false);
        $column3->SetStyle('width:160px; white-space:nowrap;');
        $grid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_IP'), null, false);
        $column4->SetStyle('width:100px; white-space:nowrap;');
        $grid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column5->SetStyle('width:85px; white-space:nowrap;');
        $grid->AddColumn($column5);

        $column6 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column6->SetStyle('width:64px; white-space:nowrap;');
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
        $tpl = $this->gadget->loadTemplate('Logs.html');
        $tpl->SetBlock('LogUI');

        // Gadget
        $tpl->SetVariable('lbl_gadget', _t('GLOBAL_GADGETS'));

        // Action
        $tpl->SetVariable('lbl_action', _t('LOGS_ACTION'));

        // User
        $tpl->SetVariable('lbl_user', _t('LOGS_USER'));

        // IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        
        // Agent
        $tpl->SetVariable('lbl_agent', _t('LOGS_AGENT'));

        // Date
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

        $model = $this->gadget->loadAdminModel('Logs');
        $logs = $model->GetLogs($filters, 12, $post['offset']);
        if (Jaws_Error::IsError($logs)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $newData = array();
        foreach ($logs as $log) {
            $logData = array();

            $logData['__KEY__'] = $log['id'];

            // Gadget
            $label =& Piwi::CreateWidget('Label', $log['gadget']);
            $label->setTitle($log['gadget']);
            $logData['gadget'] = $label->get();

            // Action
            $label =& Piwi::CreateWidget('Label', $log['action']);
            $label->setTitle($log['action']);
            $logData['action'] = $label->get();

            // User
            $label =& Piwi::CreateWidget('Label', $log['nickname']);
            $label->setTitle($log['nickname']);
            $logData['user'] = $label->get();

            // IP
            $label =& Piwi::CreateWidget('Label', long2ip($log['ip']));
            $label->setTitle(long2ip($log['ip']));
            $logData['ip'] = $label->get();

            // Date
            $label =& Piwi::CreateWidget('Label', $date->Format($log['insert_time'],'Y-m-d'));
            $label->setTitle($date->Format($log['insert_time'],'H:i:s'));
            $logData['time'] = $label->get();

            // Actions
            $actions = '';
            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                "javascript: deleteLog('".$log['id']."');",
                STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_VIEW'),
                                        "javascript: viewLog('".$log['id']."');",
                                        STOCK_SEARCH);
            $actions.= $link->Get().'&nbsp;';
            $logData['actions'] = $actions;

            $newData[] = $logData;
        }
        return $newData;
    }

    /**
     * Return info of selected log
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLogInfo()
    {
        $logID = jaws()->request->fetch('logID');
        $model = $this->gadget->loadAdminModel('Logs');
        $logInfo = $model->GetLogInfo($logID);
        if (Jaws_Error::IsError($logInfo)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $logInfo['insert_time'] = $date->Format($logInfo['insert_time']);
        $logInfo['ip'] = long2ip($logInfo['ip']);

        // user's profile
        $logInfo['user_url'] = $GLOBALS['app']->Map->GetURLFor(
                'Users',
                'Profile',
                array('user' => $logInfo['username'])
        );

        return $logInfo;
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
        $model = $this->gadget->loadAdminModel('Logs');
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