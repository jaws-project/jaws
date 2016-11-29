<?php
require_once JAWS_PATH . 'gadgets/Notification/Model/Drivers.php';
/**
 * Notification Model Admin
 *
 * @category   GadgetModel
 * @package    Notification
 */
class Notification_Model_Admin_Drivers extends Notification_Model_Drivers
{
    /**
     * Updates the specified notification driver
     *
     * @access   public
     * @param    int     $id
     * @param    array   $pData     notification driver data
     * @param    array   $settings  notification driver settings
     * @return   bool    True on Success, False on Failure
     */
    function UpdateNotificationDriver($id, $pData, $settings)
    {
        if (!empty($settings)) {
            $pData['options'] = serialize($settings);
        }
        $pData['enabled'] = (bool)$pData['enabled'];
        $driverTable = Jaws_ORM::getInstance()->table('notification_driver');
        return $driverTable->update($pData)->where('id', $id)->exec();
    }

    /**
     * Install a notification driver
     *
     * @access   public
     * @param    string  $dName  driver name
     * @return   bool    True on Success, False on Failure
     */
    function InstallNotificationDriver($dName)
    {
        $driver = $this->LoadNotificationDriver($dName);
        $pData = array();
        $pData['name'] = $dName;
        $pData['title'] = $driver->GetTitle();
        $pData['enabled'] = true;
        $driverTable = Jaws_ORM::getInstance()->table('notification_driver');
        return $driverTable->insert($pData)->exec();
    }

    /**
     * Uninstall a notification driver
     *
     * @access   public
     * @param    int     $id  driver name
     * @return   bool    True on Success, False on Failure
     */
    function UninstallNotificationDriver($id)
    {
        $driverTable = Jaws_ORM::getInstance()->table('notification_driver');
        return $driverTable->delete()->where('id', $id)->exec();
    }
}