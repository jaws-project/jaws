<?php
/**
 * Components Gadget
 *
 * @category   GadgetModel
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_Model_Plugins extends Jaws_Gadget_Model
{
    /**
     * Fetches list of plugins, installed or not installed
     *
     * @access  public
     * @param   bool    $installed  accepts true/false/null
     * @return  array   List of plugins
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

            $installed_plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
            $plugins = scandir($pDir);
            foreach ($plugins as $plugin) {
                if ($plugin{0} == '.' || !is_dir($pDir . $plugin)) {
                    continue;
                }

                $objPlugin = Jaws_Plugin::getInstance($plugin, false);
                if (Jaws_Error::IsError($objPlugin)) {
                    continue;
                }

                $pluginsList[$plugin] = array(
                    'name' => $objPlugin->name,
                    'title' => $objPlugin->title,
                    'description' => $objPlugin->description,
                    'installed' => strpos($installed_plugins, ",$plugin,") !==false,
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

}