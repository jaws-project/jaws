<?php
/**
 * JMS (Jaws Management System) Gadget
 *
 * @category   GadgetModel
 * @package    JMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jms_AdminModel extends Jaws_Gadget_Model
{
    /**
     * Get a list of gadgets, installed or non installed, core or not core, has layout or not,...
     *
     * @access  public
     * @param   bool    $core_gadget accept true/false/null value
     * @param   bool    $installed   accept true/false/null value
     * @param   bool    $updated     accept true/false/null value
     * @param   bool    $has_layout  accept true/false/null value
     * @param   bool    $has_html    accept true/false/null value
     * @return  array   A list of gadgets
     */
    function GetGadgetsList($core_gadget = null, $installed = null, $updated = null,
                            $has_layout = null, $has_html = null)
    {
        //TODO: implementing cache for this method
        static $gadgetsList;
        if (!isset($gadgetsList)) {
            $gadgetsList = array();
            $gDir = JAWS_PATH . 'gadgets' . DIRECTORY_SEPARATOR;
            if (!is_dir($gDir)) {
                Jaws_Error::Fatal('The gadgets directory does not exists!', __FILE__, __LINE__);
            }

            $coreitems = $GLOBALS['app']->Registry->Get('gadgets_core_items');
            $coreitems = array_filter(explode(',', $coreitems));

            $gadgets = scandir($gDir);
            foreach ($gadgets as $gadget) {
                if ($gadget{0} == '.' || !is_dir($gDir . $gadget)) {
                    continue;
                }

                if (!$this->gadget->GetPermission(JAWS_SCRIPT == 'index'? 'default' : 'default_admin', $gadget)) {
                    continue;
                }

                $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }

                $gInstalled = Jaws_Gadget::IsGadgetInstalled($gadget);
                if ($gInstalled) {
                    $gUpdated = Jaws_Gadget::IsGadgetUpdated($gadget);
                } else {
                    $gUpdated = true;
                }

                $tName = $objGadget->GetTitle();
                $index = urlencode($tName);
                $section = strtolower($objGadget->GetSection());
                switch ($section) {
                    case 'general':
                        $order = str_pad(array_search($gadget, $coreitems), 2, '0', STR_PAD_LEFT);
                        $index = '0'. $section. $order. $index;
                        break;
                    case 'gadgets':
                        $index = '2'. $section. $index;
                        break;
                    default:
                        $index = '1'. $section. $index;
                    break;
                }

                $gadgetsList[$index] = array(
                        'section'     => $section,
                        'realname'    => $gadget,
                        'name'        => $tName,
                        'core_gadget' => $objGadget->_IsCore,
                        'description' => $objGadget->GetDescription(),
                        'version'     => $objGadget->GetVersion(),
                        'installed'   => (bool)$gInstalled,
                        'updated'     => (bool)$gUpdated,
                        'has_layout'  => $objGadget->_has_layout,
                        'has_html'    => file_exists($gDir . $gadget . DIRECTORY_SEPARATOR . 'HTML.php'),
                );
            }

            ksort($gadgetsList);
        }

        $resList = array();
        foreach ($gadgetsList as $gadget) {
            if ((is_null($core_gadget) || $gadget['core_gadget'] == $core_gadget) &&
                (is_null($installed) || $gadget['installed'] == $installed) &&
                (is_null($updated) || $gadget['updated'] == $updated) &&
                (is_null($has_layout) || $gadget['has_layout'] == $has_layout) &&
                (is_null($has_html) || $gadget['has_html'] == $has_html))
            {
                $resList[$gadget['realname']] = $gadget;
            }
        }

        return $resList;
    }

    /**
     * Get a list of plugins, installed or non installed
     *
     * @access  public
     * @param   bool    $installed   accept true/false/null value
     * @return  array   A list of plugins
     */
    function GetPluginsList($installed = null)
    {
        //TODO: implementing cache for this method
        static $pluginsList;
        if (!isset($pluginsList)) {
            $pluginsList = array();
            $pDir = JAWS_PATH . 'plugins' . DIRECTORY_SEPARATOR;
            if (!is_dir($pDir)) {
                Jaws_Error::Fatal('The plugins directory does not exists!', __FILE__, __LINE__);
            }

            $plugins = scandir($pDir);
            foreach ($plugins as $plugin) {
                if ($plugin{0} == '.' || !is_dir($pDir . $plugin)) {
                    continue;
                }

                $pInfo = $GLOBALS['app']->LoadPlugin($plugin);
                if (Jaws_Error::IsError($pInfo)) {
                    continue;
                }

                $ei = explode(',', $GLOBALS['app']->Registry->Get('plugins_admin_enabled_items'));
                $ei = str_replace(' ', '', $ei);
                $pInstalled = in_array($plugin, $ei);

                $pluginsList[$plugin] = array(
                    'realname' => $plugin,
                    'name' => $plugin,
                    'installed' => (bool)$pInstalled
                );
            }
        }

        $resList = array();
        foreach ($pluginsList as $name => $plugin) {
            if (is_null($installed) || $plugin['installed'] == $installed) {
                $resList[$name] = $plugin;
            }
        }

        return $resList;
    }

    /**
     * Gets information of the plugin
     *
     * @access  public
     * @param   string  $plugin Plugin
     * @return  mixed   Plugin information or Jaws_Error on error
     */
    function GetPluginInfo($plugin)
    {
        $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
        if (Jaws_Error::IsError($objPlugin)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetPluginInfo'), _t('JMS_NAME'));
        }

        $plugin = array(
            'name'        => $plugin,
            'realname'    => $plugin,
            'version'     => $objPlugin->GetVersion(),
            'friendly'    => $objPlugin->IsFriendly(),
            'accesskey'   => $objPlugin->GetAccessKey(),
            'example'     => $objPlugin->GetExample(),
            'description' => _t('PLUGINS_' . strtoupper($plugin) . '_DESCRIPTION'),
        );

        return $plugin;
    }

}