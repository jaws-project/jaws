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
        $this->gadget->export('incompleteFields', $this::t('INCOMPLETE_FIELDS'));
        $this->gadget->export('lbl_title', Jaws::t('TITLE'));
        $this->gadget->export('lbl_status', Jaws::t('STATUS'));
        $this->gadget->export('lbl_install', $this::t('INSTALL'));
        $this->gadget->export('lbl_uninstall', $this::t('UNINSTALL'));
        $this->gadget->export('lbl_edit', Jaws::t('EDIT'));

        $tpl = $this->gadget->template->loadAdmin('NotificationDrivers.html');
        $tpl->SetBlock('drivers');

        $tpl->SetVariable('menubar', $this->MenuBar('NotificationDrivers'));
        $tpl->SetVariable('title', $this->gadget->title);
        $tpl->SetVariable('lbl_driver_details', $this::t('DRIVER_DETAILS'));
        $tpl->SetVariable('lbl_driver_settings_title', Jaws::t('SETTINGS'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_back', Jaws::t('BACK'));
        $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
        $tpl->SetVariable('lbl_enabled', Jaws::t('ENABLED'));
        $tpl->SetVariable('lbl_disabled', Jaws::t('DISABLED'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));

        $tpl->SetVariable('lbl_of', Jaws::t('OF'));
        $tpl->SetVariable('lbl_to', Jaws::t('TO'));
        $tpl->SetVariable('lbl_items', Jaws::t('ITEMS'));
        $tpl->SetVariable('lbl_per_page', Jaws::t('PERPAGE'));

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
                $finalData['status'] = $this::t('NOT_INSTALLED');
            } else if ($driver['status'] == true) {
                $finalData['status'] = Jaws::t('ENABLED');
            } else {
                $finalData['status'] = Jaws::t('DISABLED');
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
        $post['settings'] = array_map(
            static function($item) { return htmlspecialchars_decode($item, ENT_QUOTES); },
            $post['settings']
        );

        $model = $this->gadget->model->loadAdmin('Drivers');
        $res = $model->UpdateNotificationDriver($post['id'], $post['data'], $post['settings']);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                $res->GetMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $this->gadget->session->response(
                $this::t('DRIVER_UPDATED'),
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
                $this::t('DRIVER_INSTALLED'),
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
                $this::t('DRIVER_UNINSTALLED'),
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

        $driverOptions = $driver->getDriverOptions();
        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $driverOptions
        );
    }

}