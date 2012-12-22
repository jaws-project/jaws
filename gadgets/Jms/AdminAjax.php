<?php
/**
 * Jms AJAX API
 *
 * @category   Ajax
 * @package    Jms
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class JmsAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Get a list of installed / not installed gadgets
     *
     * @access  public
     * @param   string   $itemsToShow  Items that should be returned (installed, not installed, outdated)
     * @return  array    Gadget's list
     */
    function GetGadgets($itemsToShow)
    {
        $this->CheckSession('Jms', 'ManageGadgets');

        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        switch($itemsToShow) {
        case 'installed':
            return $model->GetGadgetsList(false, true, true);
            break;
        case 'notinstalled':
            return $model->GetGadgetsList(null, false);
            break;
        case 'outdated':
            return $model->GetGadgetsList(null, true, false);
            break;
        default:
            return $model->GetGadgetsList(false, true, true);
            break;
        }
    }

    /**
     * Get basic information of a gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  array   Gadget's info
     */
    function GetGadgetInfo($gadget)
    {
        $this->CheckSession('Jms', 'ManageGadgets');
        $html = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        return $html->GetGadgetInfo($gadget);
    }

    /**
     * Get basic information of a gadget
     *
     * @access  public
     * @param   string  $plugin  Plugin's name
     * @return  array   Plugin information
     */
    function GetPluginInfo($plugin)
    {
        $this->CheckSession('Jms', 'ManagePlugins');
        $html = $GLOBALS['app']->LoadGadget('Jms', 'AdminHTML');
        return $html->GetPluginInfo($plugin);
    }

    /**
     * Get a list of installed / not installed plugins
     *
     * @access  public
     * @param   string   $itemsToShow  Items that should be returned (installed, not installed)
     * @return  array    Plugin's list
     */
    function GetPlugins($itemsToShow)
    {
        $this->CheckSession('Jms', 'ManagePlugins');

        switch($itemsToShow) {
        case 'installed':
            return $this->_Model->GetPluginsList(true);
            break;
        case 'notinstalled':
            return $this->_Model->GetPluginsList(false);
            break;
        default:
            return $this->_Model->GetPluginsList(true);
            break;
        }
    }

    /**
     * Returns a list of gadgets activated in a certain plugin
     *
     * @access  public
     * @param   string  $plugin     Plugin's name
     * @return  array   List of gadgets
     */
    function GetGadgetsOfPlugin($plugin)
    {
        $this->CheckSession('Jms', 'ManagePlugins');

        $gadgets = $this->_Model->GetGadgetsList(null, true, true, true);
        $use_in_gadgets = explode(',', $GLOBALS['app']->Registry->Get('use_in', $plugin, JAWS_COMPONENT_PLUGIN));

        $useIn = array();
        if (count($gadgets) > 0) {
            foreach ($gadgets as $gadget) {
                if (in_array($gadget['realname'], $use_in_gadgets)) {
                    $useIn[] = array('gadget_t' => $gadget['name'],
                                     'gadget'   => $gadget['realname'],
                                     'value'    => true);
                } else {
                    $useIn[] = array('gadget_t' => $gadget['name'],
                                     'gadget'   => $gadget['realname'],
                                     'value'    => false);
                }
            }
        }
        return $useIn;
    }

    /**
     * Returns true or false if a plugin can be used always
     *
     * @access  public
     * @param   string  $plugin   Plugin's name
     * @return  bool    Can be used or no
     */
    function UseAlways($plugin)
    {
        $this->CheckSession('Jms', 'ManagePlugins');
        return ($GLOBALS['app']->Registry->Get('use_in', $plugin, JAWS_COMPONENT_PLUGIN) == '*');
    }

    /**
     * Installs gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  array   Response array (notice or error)
     */
    function InstallGadget($gadget)
    {
        $this->CheckSession('Jms', 'ManageGadgets');

        $objGadget = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        $return = $objGadget->EnableGadget();
        if (Jaws_Error::IsError($return)) {
            $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
        } elseif (!$return) {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('JMS_GADGETS_ENABLED_FAILURE', $objGadget->GetTitle()),
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('JMS_GADGETS_ENABLED_OK',
                $objGadget->GetTitle()),
                RESPONSE_NOTICE
            );
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Installs a plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin's name
     * @return  array   Response array (notice or error)
     */
    function InstallPlugin($plugin)
    {
        $this->CheckSession('Jms', 'ManagePlugins');

        require_once JAWS_PATH . 'include/Jaws/Plugin.php';

        $return = Jaws_Plugin::EnablePlugin($plugin);
        if (Jaws_Error::IsError($return)) {
            $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_ENABLED_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables a gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  array   Response array (notice or error)
     */
    function UninstallGadget($gadget)
    {
        $this->CheckSession('Jms', 'ManageGadgets');

        $result = $this->_commonDisableGadget($gadget, _t('JMS_UNINSTALLED'));
        if ($result !== true) {
            return $result;
        }

        $objGadget = $GLOBALS['app']->loadGadget($gadget, 'Info');
        $return =$objGadget->DisableGadget();
        if (Jaws_Error::isError($return)) {
            $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
        } else if (!$return) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_DISABLE_FAILURE', $gadget), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_DISABLE_OK', $gadget), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables a plugin
     *
     * @access  public
     * @param   string  $plugin  Plugin's name
     * @return  array   Response array (notice or error)
     */
    function UninstallPlugin($plugin)
    {
        $this->CheckSession('Jms', 'ManagePlugins');

        require_once JAWS_PATH . 'include/Jaws/Plugin.php';

        $return = Jaws_Plugin::DisablePlugin($plugin);
        if (Jaws_Error::isError($return)) {
            $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_DISABLE_OK', $plugin), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Purges a gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  array   Response array (notice or error)
     */
    function PurgeGadget($gadget)
    {
        $this->CheckSession('Jms', 'ManageGadgets');

        $result = $this->_commonDisableGadget($gadget, _t('JMS_PURGED'));
        if ($result !== true) {
            return $result;
        }

        $objGadget = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse($objGadget->GetMessage(), RESPONSE_ERROR);
        } else {
            $installer = $objGadget->load('Installer');
            $return = $installer->UninstallGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(
                    _t('JMS_GADGETS_DISABLE_OK',
                    $objGadget->GetTitle()),
                    RESPONSE_NOTICE
                );
            }
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Disables gadget
     *
     * @access  private
     * @param   string  $gadget     gadget name
     * @param   string  $type
     * @return  mixed   True if susccessful, else Jaws_Error on error
     */
    function _commonDisableGadget($gadget, $type)
    {
        if ($this->GetRegistry('main_gadget', 'Settings') == $gadget) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_SIDEBAR_DISABLE_MAIN_FAILURE'), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        $sql = '
            SELECT [key_name] FROM [[registry]]
            WHERE [key_name] LIKE {name} AND [key_value] LIKE {search}';
        $params = array(
            'name' => '/gadgets/%/requires',
            'search' => '%' . $gadget . '%'
        );

        $result = $GLOBALS['db']->queryCol($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (count($result) > 0) {
            $affected = array();
            foreach ($result as $r) {
                // get the gadget name out of the string
                $g = str_replace(array('/gadgets/', '/requires'), '', $r);
                // Get the real name
                $info = $GLOBALS['app']->loadGadget($g, 'Info');
                if (Jaws_Error::IsError($info)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $g), RESPONSE_ERROR);
                    return $GLOBALS['app']->Session->PopLastResponse();
                }
                $affected[] = $info->GetTitle();
            }

            $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($info)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
                return $GLOBALS['app']->Session->PopLastResponse();
            }

            $a = implode($affected, ', ');
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_REQUIRES_X_DEPENDENCY', $info->GetTitle(), $a, $type), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        return true;
    }

    /**
     * Updates gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  array   Response array (notice or error)
     */
    function UpdateGadget($gadget)
    {
        $this->CheckSession('Jms', 'ManageGadgets');

        $objGadget = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($objGadget)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_FAILURE', $gadget), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
            $return = $objGadget->UpdateGadget();
            if (Jaws_Error::IsError($return)) {
                $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
            } elseif (!$return) {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_FAILURE', $objGadget->GetTitle()), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_OK', $objGadget->GetTitle()), RESPONSE_NOTICE);
            }
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_UPDATED_NO_NEED', $objGadget->GetTitle()), RESPONSE_ERROR);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the gadget list of a plugin.
     *
     * @access  public
     * @param   string  $plugin    Plugin's name
     * @param   mixed   $selection Can be an array of gadget or a: '*' meaning all gadgets should be used
     * @return  array   Response array (notice or error)
     */
    function UpdatePluginUsage($plugin, $selection)
    {
        $this->CheckSession('Jms', 'ManagePlugins');

        if (is_array($selection)) {
            if ($GLOBALS['app']->Registry->Get('use_in', $plugin, JAWS_COMPONENT_PLUGIN) == '*')
                $GLOBALS['app']->Registry->Set('use_in', '', $plugin, JAWS_COMPONENT_PLUGIN);

            $use_in = '';
            if (count($selection) > 0) {
                $use_in = implode(',', $selection);
                $GLOBALS['app']->Registry->Set('use_in', $use_in, $plugin, JAWS_COMPONENT_PLUGIN);
            } else {
                $GLOBALS['app']->Registry->Set('use_in', '', $plugin, JAWS_COMPONENT_PLUGIN);
            }
        } else {
            $GLOBALS['app']->Registry->Set('use_in', '*', $plugin, JAWS_COMPONENT_PLUGIN);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_SAVED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}