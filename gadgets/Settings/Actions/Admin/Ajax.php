<?php
/**
 * Settings AJAX API
 *
 * @category   	Ajax
 * @package    	Settings
 * @author     	Pablo Fischer <pablo@pablo.com.mx>
 * @author     	Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    	http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * @var     object
     * @access  private
     */
    private $_Model = null;

    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function __construct($gadget)
    {
        parent::__construct($gadget);
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
        $settings = $this->gadget->request->fetchAll('post');
        $this->_Model->SaveBasicSettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
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
        $settings = $this->gadget->request->fetchAll('post');
        $this->_Model->SaveAdvancedSettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
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
        $settings = $this->gadget->request->fetchAll('post');
        $settings['site_custom_meta'] = serialize($this->gadget->request->fetch('site_custom_meta:array', 'post'));
        $this->_Model->SaveMetaSettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
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
        $settings = $this->gadget->request->fetchAll('post');
        $this->_Model->UpdateMailSettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
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
        $settings = $this->gadget->request->fetchAll('post');
        $this->_Model->UpdateFTPSettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
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
        $settings = $this->gadget->request->fetchAll('post');
        $this->_Model->UpdateProxySettings($settings);
        return $this->gadget->session->response($this::t('SAVED'));
    }

}