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
        $this->AjaxMe('script.js');
        $this->gadget->define('incompleteFields', _t('NOTIFICATION_INCOMPLETE_FIELDS'));
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_status', _t('GLOBAL_STATUS'));
        $this->gadget->define('lbl_install', _t('NOTIFICATION_INSTALL'));
        $this->gadget->define('lbl_uninstall', _t('NOTIFICATION_UNINSTALL'));
        $this->gadget->define('lbl_edit', _t('GLOBAL_EDIT'));

        $tpl = $this->gadget->template->loadAdmin('NotificationDrivers.html');
        $tpl->SetBlock('drivers');

        $tpl->SetVariable('menubar', $this->MenuBar('NotificationDrivers'));
        $tpl->SetVariable('title', $this->gadget->title);
        $tpl->SetVariable('lbl_driver_details', _t('NOTIFICATION_DRIVER_DETAILS'));
        $tpl->SetVariable('lbl_driver_settings_title', _t('GLOBAL_SETTINGS'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_back', _t('GLOBAL_BACK'));
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('lbl_disabled', _t('GLOBAL_DISABLED'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));

        $tpl->ParseBlock('drivers');
        return $tpl->Get();
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
            return $this->gadget->session->response(
                $installedDrivers->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $newData = array();
        foreach ($availableDrivers as $driver) {
            if ($driver['name'] == 'Default') {
                continue;
            }

            $finalData = array(
                'id' => 0,
                'title' => $driver['title'],
                'name' => $driver['name'],
                'enabled' => false,
            );

            $installed = false;
            if (count($installedDrivers) > 0) {
                foreach ($installedDrivers as $iDriver) {
                    if ($iDriver['name'] == $driver['name']) {
                        $installed = true;
                        $driver['status'] = $iDriver['enabled'];
                        $finalData['id'] = $iDriver['id'];
                        $finalData['enabled'] = $iDriver['enabled'];
                    }
                }
            }
            $finalData['installed'] = $installed;

            if ($installed == false) {
                $finalData['status'] = _t('NOTIFICATION_NOT_INSTALLED');
            } else if ($driver['status'] == true) {
                $finalData['status'] = _t('GLOBAL_ENABLED');
            } else {
                $finalData['status'] = _t('GLOBAL_DISABLED');
            }

            $newData[] = $finalData;
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => count($newData),
                'records' => $newData
            )
        );
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
            return $this->gadget->session->response(
                $res->GetMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                _t('NOTIFICATION_DRIVER_UPDATED'),
                RESPONSE_NOTICE
            );
        }
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
            return $this->gadget->session->response(
                $res->GetMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                _t('NOTIFICATION_DRIVER_INSTALLED'),
                RESPONSE_NOTICE
            );
        }
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
            return $this->gadget->session->response(
                $res->GetMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                _t('NOTIFICATION_DRIVER_UNINSTALLED'),
                RESPONSE_NOTICE
            );
        }
    }

    /**
     * Get a driver's settings
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetNotificationDriverSettings()
    {
        $this->gadget->CheckPermission('NotificationDrivers');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->loadAdmin('Drivers');
        $installDriver = $model->GetNotificationDriver($id);
        if (Jaws_Error::IsError($installDriver)) {
            return $this->gadget->session->response(
                $installDriver->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $driver = $model->LoadNotificationDriver($installDriver['name']);
        if (Jaws_Error::IsError($driver)) {
            return $this->gadget->session->response(
                $driver->GetMessage(),
                RESPONSE_ERROR
            );
        }

        $driverOptions = empty($installDriver['options']) ? array() : $installDriver['options'];
        $availableOptions = $driver->getDriverOptions();
        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array_merge($availableOptions, $driverOptions)
        );
    }

}