<?php
/**
 * Jms AJAX API
 *
 * @category   Ajax
 * @package    Jms
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jms_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jms_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Gets list of gadgets
     *
     * @access  public
     * @return  array   Gadget list
     */
    function GetGadgets()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadgets = $this->_Model->GetGadgetsList();
        //_log_var_dump($gadgets);
        $result = array();
        foreach ($gadgets as $key => $gadget) {
            $g = array();
            if (!$gadget['updated']) {
                $g['state'] = 'outdated';
            } else if (!$gadget['installed']) {
                $g['state'] = 'notinstalled';
            } else if (!$gadget['core_gadget']) {
                $g['state'] = 'installed';
            } else {
                $g['state'] = 'core';
            }
            $g['name'] = $gadget['name'];
            $g['realname'] = $gadget['realname'];
            $g['disabled'] = $gadget['disabled'];
            $g['core_gadget'] = $gadget['core_gadget'];
            $result[$key] = $g;
        }
        // exclude ControlPanel to be listed as a gadget
        unset($result['ControlPanel']);

        return $result;
    }

    /**
     * Gets basic information of the gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Gadget information
     */
    function GetGadgetInfo($gadget)
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $html = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        return $html->GetGadgetInfo($gadget);
    }

    /**
     * Install requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  array   Response array (notice or error)
     */
    function InstallGadget($gadget)
    {
        $htmlJms = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        $htmlJms->InstallGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Upgrade requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  array   Response array (notice or error)
     */
    function UpgradeGadget($gadget)
    {
        $htmlJms = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        $htmlJms->UpgradeGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Uninstall requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  array   Response array (notice or error)
     */
    function UninstallGadget($gadget)
    {
        $htmlJms = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        $htmlJms->UninstallGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Enable requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  array   Response array (notice or error)
     */
    function EnableGadget($gadget)
    {
        $htmlJms = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        $htmlJms->EnableGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disable requested gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  array   Response array (notice or error)
     */
    function DisableGadget($gadget)
    {
        $htmlJms = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        $htmlJms->DisableGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets list of plugins and categorize them
     *
     * @access  public
     * @return  array    Plugin's list
     */
    function GetPlugins()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $plugins = $this->_Model->GetPluginsList();
        $result = array();
        foreach ($plugins as $key => $plugin) {
            $p = array();
            $p['name'] = $plugin['name'];
            $p['realname'] = $plugin['realname'];
            $p['state'] = $plugin['installed']? 'installed' : 'notinstalled';
            $result[$key] = $p;
        }
        return $result;
    }

    /**
     * Gets basic information of the plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin name
     * @return  array   Plugin information
     */
    function GetPluginInfo($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $html = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        return $html->GetPluginInfo($plugin);
    }

    /**
     * Returns a list of gadgets are used in a certain plugin
     *
     * @access  public
     * @param   string  $plugin     Plugin name
     * @return  mixed   Array of gadgets or '*'
     */
    function GetPluginUsage($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');

        $gadgets = $this->_Model->GetGadgetsList(null, true, true, true);
        $use_in = $GLOBALS['app']->Registry->Get('use_in', $plugin, JAWS_COMPONENT_PLUGIN);
        $default_value = ($use_in === '*');
        $result = array();
        $result['always'] = array(
            'text' => _t('JMS_PLUGINS_USE_ALWAYS'),
            'value' => $default_value
        );
        if (count($gadgets) > 0) {
            $use_in = explode(',', $use_in);
            foreach ($gadgets as $gadget) {
                $value = ($use_in === '*')? true : false;
                $result[$gadget['realname']] = array(
                    'text' => $gadget['name'],
                    'value' => !$default_value && in_array($gadget['realname'], $use_in)
                );
            }
        }
        return $result;
    }

    /**
     * Enables the plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin name
     * @return  array   Response array (notice or error)
     */
    function InstallPlugin($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');

        require_once JAWS_PATH . 'include/Jaws/Plugin.php';
        $return = Jaws_Plugin::EnablePlugin($plugin);
        if (Jaws_Error::IsError($return)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_INSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_INSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables the plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin's name
     * @return  array   Response array (notice or error)
     */
    function UninstallPlugin($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');

        require_once JAWS_PATH . 'include/Jaws/Plugin.php';
        $return = Jaws_Plugin::DisablePlugin($plugin);
        if (Jaws_Error::isError($return)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_UNINSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_UNINSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates plugin usage
     *
     * @access  public
     * @param   string  $plugin    Plugin name
     * @param   mixed   $selection Comma seperated string of gadgets or '*'
     * @return  array   Response array (notice or error)
     */
    function UpdatePluginUsage($plugin, $selection)
    {
        $this->gadget->CheckPermission('ManagePlugins');

        $GLOBALS['app']->Registry->Set('use_in', $selection, $plugin, JAWS_COMPONENT_PLUGIN);
        $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}