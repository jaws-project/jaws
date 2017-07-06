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
        $tpl->SetVariable('lbl_gadgets_notification_configuration', _t('NOTIFICATION_GADGETS_NOTIFICATION_CONFIGURATION'));

        // get gadget driver settings
        $configuration = unserialize($this->gadget->registry->fetch('configuration'));

        // get drivers list
        $driversInfo = array();
        $drivers = glob(JAWS_PATH . 'include/Jaws/Notification/*.php');
        foreach ($drivers as $driver) {
            $driver = basename($driver, '.php');
            $driversInfo[] = $driver;
        }

        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $info) {
            $tpl->SetBlock('settings/gadget');
            $tpl->SetVariable('gadget_title', $info['title']);

            $driverOpt =& Piwi::CreateWidget('Combo', $gadget);
            $driverOpt->AddOption(_t('NOTIFICATION_ALL_DRIVERS'), 1);
            $driverOpt->AddOption(_t('GLOBAL_DISABLED'), 0);
            foreach($driversInfo as $driver) {
                $driverOpt->AddOption($driver, $driver);
            }
            $driverOpt->setStyle('width:140px');

            if (isset($configuration[$gadget])) {
                $driverOpt->SetDefault($configuration[$gadget]);
            }
            $tpl->SetVariable('driver_option', $driverOpt->Get());

            $tpl->ParseBlock('settings/gadget');
        }

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:saveSettings();');
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
        $configuration = $this->gadget->request->fetch('gadgets_drivers:array', 'post');
        $res = $this->gadget->registry->update('configuration', serialize($configuration));
        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('NOTIFICATION_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        }
    }

}