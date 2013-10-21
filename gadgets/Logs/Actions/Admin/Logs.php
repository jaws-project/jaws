<?php
/**
 * Logs Gadget
 *
 * @category   GadgetAdmin
 * @package    Logs
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class Logs_Actions_Admin_Logs extends Jaws_Gadget_HTML
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

        //DataGrid
        $tpl->SetVariable('grid', $this->LogsDataGrid());

        //LogUI
        $tpl->SetVariable('log_ui', $this->LogUI());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('confirmLogDelete', _t('LOGS_CONFIRM_DELETE'));
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

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('logs_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_GADGETS'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('LOGS_ACTION'), null, false);
        $column2->SetStyle('width:72px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column3->SetStyle('width:64px; white-space:nowrap;');
        $grid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column4->SetStyle('width:64px; white-space:nowrap;');
        $grid->AddColumn($column4);

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
        $tpl = $this->gadget->loadTemplate('Logs.html');
        $tpl->SetBlock('LogUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));
        
        //User
        $tpl->SetVariable('lbl_user', _t('GLOBAL_USERNAME'));

        //Gadget
        $tpl->SetVariable('lbl_gadget', _t('GLOBAL_GADGETS'));

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
        $offset = jaws()->request->fetch('offset');
        $model = $this->gadget->loadAdminModel('Logs');
        $logs = $model->GetLogs(12, $offset);
        if (Jaws_Error::IsError($logs)) {
            return array();
        }

        $date = $GLOBALS['app']->loadDate();
        $newData = array();
        foreach ($logs as $log) {
            $logData = array();

            // Gadget
            $label =& Piwi::CreateWidget('Label', $log['gadget']);
            $label->setTitle($log['gadget']);
            $logData['gadget'] = $label->get();

            // Action
            $label =& Piwi::CreateWidget('Label', $log['action']);
            $label->setTitle($log['action']);
            $logData['action'] = $label->get();

            // Date
            $label =& Piwi::CreateWidget('Label', $date->Format($log['createtime'],'Y-m-d'));
            $label->setTitle($date->Format($log['createtime'],'H:i:s'));
            $logData['time'] = $label->get();

            // Actions
            $actions = '';
            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                        "javascript: viewLog('".$log['id']."');",
                                        STOCK_EDIT);
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

        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser((int) $logInfo['user']);
        $logInfo['username'] = $user['username'];
        return $logInfo;
    }
}