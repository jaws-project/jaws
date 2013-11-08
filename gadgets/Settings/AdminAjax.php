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
class Settings_AdminAjax extends Jaws_Gadget_Action
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
        parent::Jaws_Gadget_Action($gadget);
        $this->_Model = $this->gadget->model->loadAdmin('Settings');
    }

    /**
     * Updates basic settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateBasicSettings()
    {
        $this->gadget->CheckPermission('BasicSettings');
        $settings = jaws()->request->fetchAll('post');
        $this->_Model->SaveBasicSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates advanced settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateAdvancedSettings()
    {
        $this->gadget->CheckPermission('AdvancedSettings');
        $settings = jaws()->request->fetchAll('post');
        $this->_Model->SaveAdvancedSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates META settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMetaSettings()
    {
        $this->gadget->CheckPermission('MetaSettings');
        $settings = jaws()->request->fetchAll('post');
        $settings['site_custom_meta'] = serialize(jaws()->request->fetch('site_custom_meta:array', 'post'));
        $this->_Model->SaveMetaSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates mail settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMailSettings()
    {
        $this->gadget->CheckPermission('MailSettings');
        $settings = jaws()->request->fetchAll('post');
        $this->_Model->UpdateMailSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates FTP settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateFTPSettings()
    {
        $this->gadget->CheckPermission('FTPSettings');
        $settings = jaws()->request->fetchAll('post');
        $this->_Model->UpdateFTPSettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates proxy settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateProxySettings()
    {
        $this->gadget->CheckPermission('ProxySettings');
        $settings = jaws()->request->fetchAll('post');
        $this->_Model->UpdateProxySettings($settings);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}