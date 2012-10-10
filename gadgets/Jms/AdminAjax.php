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
class JmsAdminAjax extends Jaws_Ajax
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
        $GLOBALS['app']->Registry->loadFile($plugin, 'plugins');
        $use_in_gadgets = explode(',', $GLOBALS['app']->Registry->Get('/plugins/parse_text/'.$plugin.'/use_in'));

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
        $GLOBALS['app']->Registry->loadFile($plugin, 'plugins');
        return ($GLOBALS['app']->Registry->Get('/plugins/parse_text/'.$plugin.'/use_in') == '*');
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

        $gInfo = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($gInfo)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        $req = $gInfo->GetRequirements();
        if (is_array($req)) {
            $problem = array();
            foreach ($req as $r) {
                if (!Jaws_Gadget::IsGadgetInstalled($r)) {
                    $info = $GLOBALS['app']->loadGadget($r, 'Info');
                    if (Jaws_Error::IsError($info)) {
                        $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $g), RESPONSE_ERROR);
                        return $GLOBALS['app']->Session->PopLastResponse();
                    }

                    $problem[] = $info->getName();
                }
            }

            if (count($problem) > 0) {
                $p = implode($problem, ', ');
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_REQUIRES_X_GADGET', $gInfo->getName(), $p), RESPONSE_ERROR);
                return $GLOBALS['app']->Session->PopLastResponse();
            }
        }

        $return = Jaws_Gadget::EnableGadget($gadget);
        if (Jaws_Error::IsError($return)) {
            $GLOBALS['app']->Session->PushLastResponse($return->GetMessage(), RESPONSE_ERROR);
        } elseif (!$return) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gInfo->getName()), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_OK', $gInfo->getName()), RESPONSE_NOTICE);
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

        $return = Jaws_Gadget::DisableGadget($gadget);
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

        $uninstall = Jaws_Gadget::UninstallGadget($gadget);
        if (Jaws_Error::IsError($uninstall)) {
            $GLOBALS['app']->Session->PushLastResponse($uninstall->GetMessage(), RESPONSE_ERROR);
        } else if (!$uninstall) {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_DISABLE_FAILURE', $gadget), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_DISABLE_OK', $gadget), RESPONSE_NOTICE);
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
        if ($GLOBALS['app']->Registry->Get('/config/main_gadget') == $gadget) {
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
                $affected[] = $info->getName();
            }

            $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($info)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_ENABLED_FAILURE', $gadget), RESPONSE_ERROR);
                return $GLOBALS['app']->Session->PopLastResponse();
            }

            $a = implode($affected, ', ');
            $GLOBALS['app']->Session->PushLastResponse(_t('JMS_GADGETS_REQUIRES_X_DEPENDENCY', $info->getName(), $a, $type), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        return true;
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

        $GLOBALS['app']->Registry->loadFile($plugin, 'plugins');
        if (is_array($selection)) {
            if ($GLOBALS['app']->Registry->Get('/plugins/parse_text/'.$plugin.'/use_in') == '*')
                $GLOBALS['app']->Registry->Set('/plugins/parse_text/'.$plugin.'/use_in', '');

            $use_in = '';
            if (count($selection) > 0) {
                $use_in = implode(',', $selection);
                $GLOBALS['app']->Registry->Set('/plugins/parse_text/'.$plugin.'/use_in', $use_in);
            } else {
                $GLOBALS['app']->Registry->Set('/plugins/parse_text/'.$plugin.'/use_in', '');
            }
        } else {
            $GLOBALS['app']->Registry->Set('/plugins/parse_text/'.$plugin.'/use_in', '*');
        }
        $GLOBALS['app']->Registry->Commit($plugin, 'plugins');

        $GLOBALS['app']->Session->PushLastResponse(_t('JMS_PLUGINS_SAVED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}