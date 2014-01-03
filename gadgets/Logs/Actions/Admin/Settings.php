<?php
/**
 * Logs Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Logs
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Actions_Admin_Settings extends Logs_Actions_Admin_Default
{
    /**
     * Builds admin settings UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('ManageSettings');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('Settings');

        // Log Priority Level
        $priority_level = (int)$this->gadget->registry->fetch('log_priority_level');
        $priorityCombo =& Piwi::CreateWidget('Combo', 'priority');
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_5'), JAWS_WARNING, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_6'), JAWS_NOTICE, false);
        $priorityCombo->AddOption(_t('LOGS_PRIORITY_7'), JAWS_INFO, false);
        $priorityCombo->SetDefault($priority_level);
        $tpl->SetVariable('lbl_priority', _t('LOGS_SETTINGS_DEFAULT_LOG_PRIORITY'));
        $tpl->SetVariable('priority', $priorityCombo->Get());

        // Log Parameters?
        $log_parameters = (int)$this->gadget->registry->fetch('log_parameters');
        $logParametersCombo =& Piwi::CreateWidget('Combo', 'log_parameters');
        $logParametersCombo->AddOption(_t('GLOBAL_YES'), 1, false);
        $logParametersCombo->AddOption(_t('GLOBAL_NO'), 0, false);
        $logParametersCombo->SetDefault($log_parameters);
        $tpl->SetVariable('lbl_log_parameters', _t('LOGS_SETTINGS_LOG_PARAMETERS'));
        $tpl->SetVariable('log_parameters', $logParametersCombo->Get());

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:saveSettings();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->SetVariable('menubar', $this->MenuBar('Settings'));

        $tpl->ParseBlock('Settings');

        return $tpl->Get();
    }

    /**
     * Update Settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveSettings()
    {
        $this->gadget->CheckPermission('ManageSettings');
        $settings = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $res = $model->SaveSettings($settings);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

}