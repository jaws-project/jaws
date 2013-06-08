<?php
/**
 * Components AJAX API
 *
 * @category   Ajax
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Components_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
    }

    /**
     * Gets list of gadgets
     *
     * @access  public
     * @return  array   Gadgets list
     */
    function GetGadgets()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $model = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadgets = $model->GetGadgetsList();
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
            $g['description'] = $gadget['description'];
            $g['manage_reg'] = $this->gadget->GetPermission('default_registry', '', $gadget['realname']);
            $g['manage_acl'] = $this->gadget->GetPermission('ManageACLs');
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
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Gadgets');
        return $html->GadgetInfo($gadget);
    }

    /**
     * Installs requested gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Response array (notice or error)
     */
    function InstallGadget($gadget)
    {
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'GadgetInstaller');
        $html->InstallGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Upgrades requested gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Response array (notice or error)
     */
    function UpgradeGadget($gadget)
    {
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'GadgetInstaller');
        $html->UpgradeGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Uninstalls requested gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Response array (notice or error)
     */
    function UninstallGadget($gadget)
    {
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'GadgetInstaller');
        $html->UninstallGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Enables requested gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Response array (notice or error)
     */
    function EnableGadget($gadget)
    {
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'GadgetInstaller');
        $html->EnableGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables requested gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Response array (notice or error)
     */
    function DisableGadget($gadget)
    {
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'GadgetInstaller');
        $html->DisableGadget($gadget);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets list of plugins and categorize them
     *
     * @access  public
     * @return  array   List of plugins
     */
    function GetPlugins()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $model = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Plugins');
        $plugins = $model->GetPluginsList();
        foreach ($plugins as $key => $plugin) {
            $plugins[$key]['state'] = $plugin['installed']? 'installed' : 'notinstalled';
            $plugins[$key]['manage_reg'] = $this->gadget->GetPermission('default_registry', '', $plugin['realname']);
            $plugins[$key]['manage_acl'] = $this->gadget->GetPermission('ManageACLs');
            unset($plugins[$key]['installed']);
        }
        return $plugins;
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
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Plugins');
        return $html->PluginInfo($plugin);
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
        $return = Jaws_Plugin::InstallPlugin($plugin);
        if (Jaws_Error::IsError($return)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_PLUGINS_INSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_PLUGINS_INSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables the plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin name
     * @return  array   Response array (notice or error)
     */
    function UninstallPlugin($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');

        require_once JAWS_PATH . 'include/Jaws/Plugin.php';
        $return = Jaws_Plugin::UninstallPlugin($plugin);
        if (Jaws_Error::isError($return)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_PLUGINS_UNINSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_PLUGINS_UNINSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns gadgets which are used in a certain plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin name
     * @return  array   Array of backend, frontend and all gadgets
     */
    function GetPluginUsage($plugin)
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Plugins');
        $ui = $html->PluginUsage();

        $usage = array();
        $usage['gadgets'] = array();
        $usage['backend'] = $GLOBALS['app']->Registry->fetch('backend_gadgets', $plugin);
        $usage['frontend'] = $GLOBALS['app']->Registry->fetch('frontend_gadgets', $plugin);
        $model = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true, true);
        foreach ($gadgets as $gadget) {
            $usage['gadgets'][] = array('name' => $gadget['name'], 'realname' => $gadget['realname']);
        }

        return array('ui' => $ui, 'usage' => $usage);
    }

    /**
     * Updates plugin usage
     *
     * @access  public
     * @param   string  $plugin     Plugin name
     * @param   string  $backend    Comma separated list of gadgets
     * @param   string  $frontend   Comma separated list of gadgets
     * @return  array   Response array (notice or error)
     */
    function UpdatePluginUsage($plugin, $backend, $frontend)
    {
        $this->gadget->CheckPermission('ManagePlugins');
        $this->gadget->registry->update('backend_gadgets', $backend, $plugin);
        $this->gadget->registry->update('frontend_gadgets', $frontend, $plugin);
        $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_PLUGINS_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Fetches registry data of the gadget/plugin
     *
     * @access  public
     * @param   string  $comp   Gadget/Plugin name
     * @return  array   Registry keys/values
     */
    function GetRegistry($comp)
    {
        $this->gadget->CheckPermission('ManageRegistry');
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'Registry');
        $ui = $html->RegistryUI();
        $data = $GLOBALS['app']->Registry->fetchAll($comp);
        return array('ui' => $ui, 'data' => $data);
    }

    /**
     * Updates registry with new values
     *
     * @access  public
     * @param   string  $comp   Gadget/Plugin name
     * @param   array   $data   changed keys/values
     * @return  array   Response array (notice or error)
     */
    function UpdateRegistry($comp, $data)
    {
        $this->gadget->CheckPermission('ManageRegistry');
        foreach ($data as $key => $value) {
            $res = $GLOBALS['app']->Registry->update($key, $value, $comp);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_REGISTRY_NOT_UPDATED'), RESPONSE_ERROR);
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_REGISTRY_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Fetches default ACL data of the gadget/plugin
     *
     * @access  public
     * @param   string  $comp  Gadget/Plugin name
     * @return  array   ACL keys/values
     */
    function GetACL($comp)
    {
        $this->gadget->CheckPermission('ManageACLs');
        $html = $GLOBALS['app']->LoadGadget('Components', 'AdminHTML', 'ACL');
        $ui = $html->ACLUI();
        $info = $GLOBALS['app']->LoadGadget($comp, 'Info');
        $acls = $GLOBALS['app']->ACL->fetchAll($comp);
        foreach ($acls as $k => $acl) {
            $acls[$k]['key_desc'] = $info->GetACLDescription($acl['key_name']);
        }
        return array('ui' => $ui, 'acls' => $acls);
    }

    /**
     * Updates ACLs with new values
     *
     * @access  public
     * @param   string  $comp  Gadget/Plugin name
     * @param   array   $data  changed keys/values
     * @return  array   Response array (notice or error)
     */
    function UpdateACL($comp, $data)
    {
        $this->gadget->CheckPermission('ManageACLs');
        foreach ($data as $key => $value) {
            list($key, $subkey) = explode(':', $key);
            $res = $GLOBALS['app']->ACL->update($key, $subkey, $value, $comp);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_ACL_NOT_UPDATED'), RESPONSE_ERROR);
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('COMPONENTS_ACL_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}