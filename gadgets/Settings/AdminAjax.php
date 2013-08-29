<?php
/**
 * Settings AJAX API
 *
 * @category   Ajax
 * @package    Settings
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Settings_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

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
        $this->gadget->CheckPermission('BasicSettings');
        $settings = array_column($settings, 'value', 'name');
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
        $this->gadget->CheckPermission('AdvancedSettings');
        $settings = array_column($settings, 'value', 'name');
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
        $this->gadget->CheckPermission('MetaSettings');
        $settings = array_column($settings, 'value', 'name');
        $settings['site_custom_meta'] = serialize($customMeta);
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
        $this->gadget->CheckPermission('MailSettings');
        $settings = array_column($settings, 'value', 'name');
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
        $this->gadget->CheckPermission('FTPSettings');
        $settings = array_column($settings, 'value', 'name');
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
        $this->gadget->CheckPermission('ProxySettings');
        $settings = array_column($settings, 'value', 'name');
        $this->_Model->UpdateProxySettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}