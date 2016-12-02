<?php
/**
 * Notification Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Notification
 */
class Notification_Actions_Admin_NotificationDrivers extends Notification_Actions_Admin_Default
{
    /**
     * Manage the notifications driver
     *
     * @access  public
     * @return  string  XHTML content
     */
    function NotificationDrivers()
    {
        $this->gadget->CheckPermission('NotificationDrivers');

        $tpl = $this->gadget->template->loadAdmin('NotificationDrivers.html');
        $tpl->SetBlock('drivers');

        $this->AjaxMe('script.js');
        $tpl->SetVariable('menubar', $this->MenuBar('NotificationDrivers'));
        $tpl->SetVariable('title', $this->gadget->title);
        $tpl->SetVariable('datagrid', $this->NotificationDriversDataGrid());
        $tpl->SetVariable('legend_title', _t('NOTIFICATION_DRIVER_DETAILS'));
        $tpl->SetVariable('driver_settings_title', _t('GLOBAL_SETTINGS'));

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        $enabled =& Piwi::CreateWidget('Combo', 'enabled');
        $enabled->AddOption(_t('GLOBAL_ENABLED'), '1');
        $enabled->AddOption(_t('GLOBAL_DISABLED'), '0');
        $enabled->setStyle('width:80px');
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('enabled', $enabled->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'updateNotificationDriver();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('incompleteFields', _t('NOTIFICATION_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('drivers');
        return $tpl->Get();
    }

    /**
     * Builds datagrid structure
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function NotificationDriversDataGrid()
    {
        $model = $this->gadget->model->load('Drivers');
        $total = count($model->GetNotificationDrivers());

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->SetID('notification_drivers_datagrid');

        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_STATUS'), null, false);
        $datagrid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column3->SetStyle('width:40px; white-space:nowrap;');
        $datagrid->AddColumn($column3);

        return $datagrid->Get();
    }

    /**
     * Prepares data for notification drivers datagrid
     *
     * @access  public
     * @param   int     $offset  Data offset
     * @return  array   Grid data
     */
    function GetNotificationDrivers($offset = null)
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $model = $this->gadget->model->load('Drivers');
        $availableDrivers  = $model->GetNotificationDriversList();
        $installedDrivers = $model->GetNotificationDrivers(null, 10, $offset);
        if (Jaws_Error::IsError($installedDrivers)) {
            return array();
        }

        $newData = array();
        foreach ($availableDrivers as $driver) {
            $posData = array();

            if($driver['name']=='Default') {
                continue;
            }

            $posData['title'] = $driver['title'];

            $installed = false;
            if (count($installedDrivers) > 0) {
                foreach ($installedDrivers as $iDriver) {
                    if ($iDriver['name'] == $driver['name']) {
                        $installed = true;
                        $driver['id'] = $iDriver['id'];
                        $driver['status'] = $iDriver['enabled'];
                    }
                }
            }

            if ($installed == false) {
                $posData['status'] = _t('NOTIFICATION_NOT_INSTALLED');
            } else if ($driver['status'] == true) {
                $posData['status'] = _t('GLOBAL_ENABLED');
            } else {
                $posData['status'] = _t('GLOBAL_DISABLED');
            }

            $actions = '';
            if ($this->gadget->GetPermission('NotificationDrivers')) {

                if ($installed === true) {
                    $link =& Piwi::CreateWidget('Link', _t('NOTIFICATION_UNINSTALL'),
                        "javascript: uninstallDriver(this, '" . $driver['id'] . "');",
                        STOCK_CANCEL);
                    $actions .= $link->Get() . '&nbsp;';
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                        "javascript: editNotificationDriver(this, '" . $driver['id'] . "');",
                        STOCK_EDIT);
                    $actions .= $link->Get() . '&nbsp;';
                } else {
                    $link =& Piwi::CreateWidget('Link', _t('NOTIFICATION_INSTALL'),
                        "javascript: installDriver(this, '" . $driver['name'] . "');",
                        STOCK_OK);
                    $actions .= $link->Get() . '&nbsp;';
                }
            }
            $posData['actions'] = $actions;

            $newData[] = $posData;
        }
        return $newData;
    }

    /**
     * Gets associated data of the notification driver
     *
     * @access   public
     * @internal param   int    $id
     * @return   mixed   Array of notification driver data ot false
     */
    function GetNotificationDriver()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->load('Drivers');
        $driver = $model->GetNotificationDriver($id);
        if (Jaws_Error::IsError($driver)) {
            return false;
        }

        return $driver;
    }

    /**
     * Updates the specified notification driver
     *
     * @access   public
     * @internal param   int    $id
     * @internal param   array  $pData notification driver data
     * @return   array   Response (success or failure)
     */
    function UpdateNotificationDriver()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $post = $this->gadget->request->fetch(array('id', 'data:array', 'settings:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Drivers');
        $res = $model->UpdateNotificationDriver($post['id'], $post['data'], $post['settings']);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('NOTIFICATION_DRIVER_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Install a notification driver
     *
     * @access   public
     * @internal param   string     $dName  driver name
     * @return   array   Response (success or failure)
     */
    function InstallNotificationDriver()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $dName = $this->gadget->request->fetch('driver', 'post');
        $model = $this->gadget->model->loadAdmin('Drivers');
        $res = $model->InstallNotificationDriver($dName);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('NOTIFICATION_DRIVER_INSTALLED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Uninstall a notification driver
     *
     * @access   public
     * @internal param   int    $id     driver name
     * @return   array   Response (success or failure)
     */
    function UninstallNotificationDriver()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $driver = $this->gadget->request->fetch('driver', 'post');
        $model = $this->gadget->model->loadAdmin('Drivers');
        $res = $model->UninstallNotificationDriver($driver);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('NOTIFICATION_DRIVER_UNINSTALLED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Show a form to show/edit a given drivers settings
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetNotificationDriverSettingsUI()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->loadAdmin('Drivers');
        $installDriver = $model->GetNotificationDriver($id);


        $driver = $model->LoadNotificationDriver($installDriver['name']);
        if(Jaws_Error::IsError($driver)) {
            return $driver;
        }

        $tpl = $this->gadget->template->loadAdmin('NotificationDrivers.html');
        $tpl->SetBlock('DriverSettingsUI');

        $driverOptions = $installDriver['options'];
        $availableOptions = $driver->getDriverOptions();
        foreach ($availableOptions as $option) {
            $tpl->SetBlock('DriverSettingsUI/field');

            $entry =& Piwi::CreateWidget('Entry', $option, $driverOptions[$option]);
            $entry->SetStyle('direction:ltr');
            $entry->SetID($option);
            $tpl->SetVariable('lbl_var', $option);
            $tpl->SetVariable('var', $entry->Get());

            $tpl->ParseBlock('DriverSettingsUI/field');
        }

        $tpl->ParseBlock('DriverSettingsUI');
        return $tpl->Get();
    }

}