<?php
/**
 * Weather admin model
 *
 * @category   GadgetModel
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Weather/Model.php';

class WeatherAdminModel extends WeatherModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA), _t('WEATHER_NAME'));
        }

        $new_dir = JAWS_DATA . 'weather' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('WEATHER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry('unit', 'metric');
        $this->gadget->AddRegistry('date_format', 'DN d MN');
        $this->gadget->AddRegistry('update_period', '3600');
        $this->gadget->AddRegistry('api_key', '');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('weather');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('WEATHER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $this->gadget->DelRegistry('unit');
        $this->gadget->DelRegistry('date_format');
        $this->gadget->DelRegistry('update_period');
        $this->gadget->DelRegistry('api_key');

        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('schema.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Remove from layout
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->DeleteGadgetElements('Weather');
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Weather/ManageRegions', 'true');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Weather/AddCity');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Weather/EditCity');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Weather/DeleteCity');

            // Registry keys
            $this->gadget->AddRegistry('unit', 'metric');
            $this->gadget->AddRegistry('date_format', 'DN d MN');
            $this->gadget->AddRegistry('update_period', '3600');
            $this->gadget->DelRegistry('refresh');
            $this->gadget->DelRegistry('cities');
            $this->gadget->DelRegistry('units');
            $this->gadget->DelRegistry('forecast');
            $this->gadget->DelRegistry('partner_id');
            $this->gadget->DelRegistry('license_key');
        }

        // Registry keys
        $this->gadget->AddRegistry('api_key', '');

        return true;
    }

    /**
     * Inserts a new region
     *
     * @access  public
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of GEO posiotion
     * @param   float   $longitude  Longitude of GEO posiotion
     * @param   bool    $published  Visibility status of GEO posiotion
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function InsertRegion($title, $fast_url, $latitude, $longitude, $published)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'weather');

        $params = array();
        $params['title']     = $title;
        $params['fast_url']  = $fast_url;
        $params['latitude']  = (float) $latitude;
        $params['longitude'] = (float) $longitude;
        $params['published'] = $published;

        $sql = '
            INSERT INTO [[weather]]
                ([title], [fast_url], [latitude], [longitude], [published])
            VALUES
                ({title}, {fast_url}, {latitude}, {longitude}, {published})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_ADDED'), _t('WEATHER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the region
     *
     * @access  public
     * @param   int     $id         ID of the GEO posiotion
     * @param   string  $title      Title of the GEO posiotion
     * @param   string  $fast_url   Fast URL
     * @param   float   $latitude   Latitude of the GEO posiotion
     * @param   float   $longitude  Longitude of the GEO posiotion
     * @param   bool    $published  Visibility status of the GEO posiotion
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateRegion($id, $title, $fast_url, $latitude, $longitude, $published)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'weather', false);

        $params = array();
        $params['id']        = (int)$id;
        $params['title']     = $title;
        $params['fast_url']  = $fast_url;
        $params['latitude']  = (float) $latitude;
        $params['longitude'] = (float) $longitude;
        $params['published'] = $published;

        $sql = '
            UPDATE [[weather]] SET
                [title]      = {title},
                [fast_url]   = {fast_url},
                [latitude]   = {latitude},
                [longitude]  = {longitude},
                [published]  = {published}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_UPDATED'), _t('WEATHER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the specified region
     *
     * @access  public
     * @param   int     $id  Region ID
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteRegion($id)
    {
        $sql = 'DELETE FROM [[weather]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_REGION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEATHER_ERROR_REGION_NOT_DELETED'), _t('WEATHER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_REGION_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @param   string  $unit           Unit for displaying temperature
     * @param   int     $update_period  Time interval between updates
     * @param   string  $date_format    Date string format
     * @param   string  $api_key        API key
     * @return  mixed   True if update is successful or Jaws_Error on any error
     */
    function UpdateProperties($unit, $update_period, $date_format, $api_key)
    {
        $res = array();
        $res[] = $this->SetRegistry('unit', $unit);
        $res[] = $this->SetRegistry('update_period', $update_period);
        $res[] = $this->SetRegistry('date_format', $date_format);
        $res[] = $this->SetRegistry('api_key', $api_key);

        foreach ($res as $r) {
            if (Jaws_Error::IsError($r) || !$r) {
                $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('WEATHER_ERROR_PROPERTIES_NOT_UPDATED'), _t('WEATHER_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEATHER_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

}