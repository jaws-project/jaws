<?php
/**
 * Notification Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Notification
 */
class Notification_Actions_Admin_Settings extends Notification_Actions_Admin_Default
{
    /**
     * Builds admin settings UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('Settings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('menubar', $this->MenuBar('Settings'));

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:SaveSettings();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->ParseBlock('settings');

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
        $this->gadget->CheckPermission('Settings');
        return true;
    }

}