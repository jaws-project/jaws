<?php
/**
 * Logs Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Logs
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2021 Jaws Development Group
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

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Settings') : $menubar;
        $assigns['log_parameters'] = (int)$this->gadget->registry->fetch('log_parameters');
        $assigns['priority_level'] = (int)$this->gadget->registry->fetch('log_priority_level');
        $assigns['priorityItems'] = array(
            JAWS_WARNING => _t('LOGS_PRIORITY_5'),
            JAWS_NOTICE => _t('LOGS_PRIORITY_6'),
            JAWS_INFO => _t('LOGS_PRIORITY_7'),
        );

        return $this->gadget->template->xLoadAdmin('Settings.html')->render($assigns);
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
        $settings = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Settings');
        $res = $model->SaveSettings($settings);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push(_t('LOGS_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
    }

}