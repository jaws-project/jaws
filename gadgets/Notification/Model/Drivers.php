<?php
/**
 * Notification Model
 *
 * @category   GadgetModel
 * @package    Notification
 */
class Notification_Model_Drivers extends Jaws_Gadget_Model
{
    /**
     * Gets notification drivers info
     *
     * @param   bool   $enabled    enabled?
     * @param   int    $limit      Data limit
     * @param   int    $offset     Data offset
     * @access  public
     * @return  mixed   Array of associated data of notification drivers or Jaws_Error on failure
     */
    function GetNotificationDrivers($enabled = null, $limit = 0, $offset = null)
    {
        $driverTable = Jaws_ORM::getInstance()->table('notification_driver');
        $driverTable->select('id:integer', 'name', 'title', 'enabled:boolean');
        if(!empty($enabled)) {
            $driverTable->where('enabled', $enabled);
        }
        return $driverTable->limit($limit, $offset)->fetchAll();
    }

    /**
     * Get a notification driver info
     *
     * @param   int|string    $id   Driver Id or Drive name
     * @access  public
     * @return  mixed   Array of associated data of notification driver or Jaws_Error on failure
     */
    function GetNotificationDriver($id)
    {
        $driverTable = Jaws_ORM::getInstance()->table('notification_driver');
        $driverTable->select('id:integer', 'name', 'title', 'enabled:boolean', 'options');
        if (is_numeric($id)) {
            $driverTable->where('id', (int)$id);
        } else {
            $driverTable->where('name', $id);
        }
        $driverInfo = $driverTable->fetchRow();
        if (Jaws_Error::IsError($driverInfo)) {
            return $driverInfo;
        }

        if (!empty($driverInfo['options'])) {
            $driverInfo['options'] = unserialize($driverInfo['options']);
        }
        return $driverInfo;
    }

    /**
     * Fetches list of notification drivers
     *
     * @access  public
     * @return  mixed   Array of associated data of a basket or Jaws_Error on failure
     */
    function GetNotificationDriversList()
    {
        $driversList = array();
        $pDir = JAWS_PATH . 'include/Jaws/Notification/';
        if (!is_dir($pDir)) {
            Jaws_Error::Fatal('The notifications driver directory does not exists!', __FILE__, __LINE__);
        }

        $drivers = glob($pDir. '*.php');
        foreach ($drivers as $driver) {
            $path_parts = pathinfo($driver);
            $dName = $path_parts['filename'];
            $obj = $this->LoadNotificationDriver($dName);
            $dTitle = $obj->getTitle();
            $index = urlencode($dName);

            $driversList[$index] = array(
                'name'        => $dName,
                'title'       => $dTitle,
                'version'     => 1,
            );
        }

        ksort($driversList);
        return $driversList;
    }

    /**
     * Load a notification driver
     *
     * @access  public
     * @param   string  $dName       driver name
     * @param   int     $invoiceId   invoice id
     * @return  mixed   Array of associated data of a basket or Jaws_Error on failure
     */
    public function LoadNotificationDriver($dName, $invoiceId = null)
    {
        $driverInfo = $this->GetNotificationDriver($dName);
        if (Jaws_Error::IsError($driverInfo)) {
            return $driverInfo;
        }
        $options = null;
        if (!empty($driverInfo)) {
            $options = $driverInfo['options'];
            $dName =  $driverInfo['name'];
        }

        return Jaws_Notification::getInstance($dName, $options);
    }
}