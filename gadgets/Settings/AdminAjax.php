<?php
/**
 * Settings AJAX API
 *
 * @category   Ajax
 * @package    Settings
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SettingsAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Updates basic settings
     *
     * @access  public
     * @param   array   $settings  Basic settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @return  array   Response array (notice or error)
     */
    function UpdateBasicSettings($settings)
    {
        $this->CheckSession('Settings', 'BasicSettings');
        $this->_Model->SaveBasicSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates advanced settings
     *
     * @access  public
     * @param   array   $settings  Advanced settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @return  array   Response array (notice or error)
     */
    function UpdateAdvancedSettings($settings)
    {
        $this->CheckSession('Settings', 'AdvancedSettings');
        $this->_Model->SaveAdvancedSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates META settings
     *
     * @access  public
     * @param   array   $settings  META settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @param   array   $customMeta User defined META
     * @return  array   Response array (notice or error)
     */
    function UpdateMetaSettings($settings, $customMeta)
    {
        $this->CheckSession('Settings', 'MetaSettings');
        $settings['custom_meta'] = serialize($customMeta);
        $this->_Model->SaveMetaSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates mail settings
     *
     * @access  public
     * @param   array   $settings  Mail settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @return  array   Response array (notice or error)
     */
    function UpdateMailSettings($settings)
    {
        $this->CheckSession('Settings', 'MailSettings');
        $this->_Model->UpdateMailSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates FTP settings
     *
     * @access  public
     * @param   array   $settings  FTP settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @return  array   Response array (notice or error)
     */
    function UpdateFTPSettings($settings)
    {
        $this->CheckSession('Settings', 'FTPSettings');
        $this->_Model->UpdateFTPSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates proxy settings
     *
     * @access  public
     * @param   array   $settings  Proxy settings array. Should have the same
     *                             format as the SaveBasicSettings model's method
     * @return  array   Response array (notice or error)
     */
    function UpdateProxySettings($settings)
    {
        $this->CheckSession('Settings', 'ProxySettings');
        $this->_Model->UpdateProxySettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}