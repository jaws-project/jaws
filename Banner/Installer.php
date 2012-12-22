<?php
/**
 * Banner Installer
 *
 * @category    GadgetModel
 * @package     Banner
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Banner_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . $this->gadget->DataDirectory;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('BANNER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  mixed    True on a successful install and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array('banners',
                        'banners_groups');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('BANNER_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        //registry keys

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   TRUE on success, or Jaws_Error
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', '0.7.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('update.xml', '', '0.8.0.xml', true);
            if (Jaws_Error::IsError($result)) {
                // maybe user have banner group with this name
                //return $result;
            }
        }

        if (version_compare($old, '0.8.1', '<')) {
            $base_path = $GLOBALS['app']->getDataURL() . $this->gadget->DataDirectory;
            $sql = '
                SELECT [id], [banner]
                FROM [[banners]]';
            $banners = $GLOBALS['db']->queryAll($sql);
            if (!Jaws_Error::IsError($banners)) {
                foreach ($banners as $banner) {
                    if (!empty($banner['banner'])) {
                        if (strpos($banner['banner'], $base_path) !== 0) {
                            continue;
                        }
                        $banner['banner'] = substr($banner['banner'], strlen($base_path));
                        $sql = '
                            UPDATE [[banners]] SET
                                [banner] = {banner}
                            WHERE [id] = {id}';
                        $res = $GLOBALS['db']->query($sql, $banner);
                    }
                }
            }
        }

        if (version_compare($old, '0.8.2', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        return true;
    }

}