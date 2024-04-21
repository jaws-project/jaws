<?php
/**
 * Components AJAX API
 *
 * @category   Ajax
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Gets list of gadgets
     *
     * @access  public
     * @return  array   Gadgets list
     */
    function GetGadgets()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        $model = $this->gadget->model->load('Gadgets');
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
            $g['name']  = $gadget['name'];
            $g['title'] = $gadget['title'];
            $g['disabled'] = $gadget['disabled'];
            $g['core_gadget'] = $gadget['core_gadget'];
            $g['description'] = $gadget['description'];
            $g['manage_reg'] = $this->gadget->GetPermission('default_registry', '', false, $gadget['name']);
            $g['manage_acl'] = $this->gadget->GetPermission('ManageACLs');
            $result[$key] = $g;
        }

        return $result;
    }

    /**
     * Gets basic information of the gadget
     *
     * @access  public
     * @return  array   Gadget information
     */
    function GetGadgetInfo()
    {
        $this->gadget->CheckPermission('ManageGadgets');
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('Gadgets');
        return $html->GadgetInfo($gadget);
    }

    /**
     * Installs requested gadget
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InstallGadget2()
    {
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('GadgetInstaller');
        $html->InstallGadget($gadget);
        return $this->gadget->session->pop();
    }

    /**
     * Upgrades requested gadget
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpgradeGadget2()
    {
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('GadgetInstaller');
        $html->UpgradeGadget($gadget);
        return $this->gadget->session->pop();
    }

    /**
     * Uninstalls requested gadget
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UninstallGadget2()
    {
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('GadgetInstaller');
        $html->UninstallGadget($gadget);
        return $this->gadget->session->pop();
    }

    /**
     * Enables requested gadget
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function EnableGadget()
    {
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('GadgetInstaller');
        $html->EnableGadget($gadget);
        return $this->gadget->session->pop();
    }

    /**
     * Disables requested gadget
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DisableGadget()
    {
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('GadgetInstaller');
        $html->DisableGadget($gadget);
        return $this->gadget->session->pop();
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
        $model = $this->gadget->model->load('Plugins');
        $plugins = $model->GetPluginsList();
        foreach ($plugins as $key => $plugin) {
            $plugins[$key]['state'] = $plugin['installed']? 'installed' : 'notinstalled';
            $plugins[$key]['manage_reg'] = $this->gadget->GetPermission(
                'default_registry',
                '',
                false,
                $plugin['name']
            );
            $plugins[$key]['manage_acl'] = $this->gadget->GetPermission('ManageACLs');
            unset($plugins[$key]['installed']);
        }
        return $plugins;
    }

    /**
     * Gets basic information of the plugin
     *
     * @access  public
     * @return  array   Plugin information
     */
    function GetPluginInfo()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        @list($plugin) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('Plugins');
        return $html->PluginInfo($plugin);
    }

    /**
     * Enables the plugin
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InstallPlugin()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        @list($plugin) = $this->gadget->request->fetchAll('post');
        $return = Jaws_Plugin::InstallPlugin($plugin);
        if (Jaws_Error::IsError($return)) {
            $this->gadget->session->push($this::t('PLUGINS_INSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('PLUGINS_INSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $this->gadget->session->pop();
    }

    /**
     * Disables the plugin
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UninstallPlugin()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        @list($plugin) = $this->gadget->request->fetchAll('post');
        $return = Jaws_Plugin::UninstallPlugin($plugin);
        if (Jaws_Error::isError($return)) {
            $this->gadget->session->push($this::t('PLUGINS_UNINSTALL_FAILURE'), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('PLUGINS_UNINSTALL_OK', $plugin), RESPONSE_NOTICE);
        }
        return $this->gadget->session->pop();
    }

    /**
     * Returns gadgets which are used in a certain plugin
     *
     * @access  public
     * @return  array   Array of backend, frontend and all gadgets
     */
    function GetPluginUsage()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        @list($plugin) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('Plugins');
        $ui = $html->PluginUsage();

        $usage = array();
        $usage['gadgets'] = array();
        $usage['backend'] = $this->app->registry->fetch('backend_gadgets', $plugin);
        $usage['frontend'] = $this->app->registry->fetch('frontend_gadgets', $plugin);
        $model = $this->gadget->model->load('Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true, true);
        foreach ($gadgets as $gadget) {
            $usage['gadgets'][] = array('name' => $gadget['name'], 'title' => $gadget['title']);
        }

        return array('ui' => $ui, 'usage' => $usage);
    }

    /**
     * Updates plugin usage
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePluginUsage()
    {
        $this->gadget->CheckPermission('ManagePlugins');
        @list($plugin, $backend, $frontend) = $this->gadget->request->fetchAll('post');
        $this->gadget->registry->update('backend_gadgets', $backend, false, $plugin);
        $this->gadget->registry->update('frontend_gadgets', $frontend, false, $plugin);
        $this->gadget->session->push($this::t('PLUGINS_UPDATED'), RESPONSE_NOTICE);
        return $this->gadget->session->pop();
    }

    /**
     * Fetches registry data of the gadget/plugin
     *
     * @access  public
     * @return  array   Registry keys/values
     */
    function GetRegistry()
    {
        $this->gadget->CheckPermission('ManageRegistry');
        @list($comp, $is_plugin) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('Registry');
        $ui = $html->RegistryUI();
        $data = $this->app->registry->fetchAll($comp);
        return array('ui' => $ui, 'data' => $data);
    }

    /**
     * Updates registry with new values
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateRegistry()
    {
        $this->gadget->CheckPermission('ManageRegistry');
        @list($comp, $data) = $this->gadget->request->fetchAll('post');
        $data = $this->gadget->request->fetch('1:array', 'post');
        foreach ($data as $key => $value) {
            $res = $this->app->registry->update($key, $value, null, $comp);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($this::t('REGISTRY_NOT_UPDATED'), RESPONSE_ERROR);
            }
        }
        $this->gadget->session->push($this::t('REGISTRY_UPDATED'), RESPONSE_NOTICE);
        return $this->gadget->session->pop();
    }

    /**
     * Fetches default ACL data of the gadget/plugin
     *
     * @access  public
     * @return  array   ACL keys/values
     */
    function GetACL()
    {
        $this->gadget->CheckPermission('ManageACLs');
        @list($comp, $is_plugin) = $this->gadget->request->fetchAll('post');
        $html = $this->gadget->action->loadAdmin('ACL');
        $ui = $html->ACLUI();
        $acls = array();
        $result = $this->app->acl->fetchAll($comp);
        if (!$is_plugin && !empty($result)) {
            $info = Jaws_Gadget::getInstance($comp);
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $acls[] = array(
                        'key_name' => $key_name,
                        'key_subkey' => $subkey,
                        'key_value' => $value,
                        'key_desc' => $info->acl->description($key_name, $subkey)
                    );
                }
            }
        }

        return array('ui' => $ui, 'acls' => $acls);
    }

    /**
     * Updates ACLs with new values
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateACL()
    {
        $this->gadget->CheckPermission('ManageACLs');
        @list($comp, $data) = $this->gadget->request->fetchAll('post');
        $data = $this->gadget->request->fetch('1:array', 'post');
        foreach ($data as $key => $value) {
            list($key, $subkey) = explode(':', $key);
            $res = $this->app->acl->update($key, $subkey, $value, $comp);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($this::t('ACL_NOT_UPDATED'), RESPONSE_ERROR);
            }
        }

        $this->gadget->session->push($this::t('ACL_UPDATED'), RESPONSE_NOTICE);
        return $this->gadget->session->pop();
    }

}