<?php
/**
 * Class parent of all plugins, features that each plugin can have
 * to print nice text/images
 *
 * @category   Plugins
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Plugin
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * @var     bool
     * @access  public
     */
     public $pluginType;

    /**
     * @var     bool
     * @access  public
     */
     public $onlyNormalMode;

    /**
     * Name of the plugin
     *
     * @var     string
     * @access  private
     */
    var $name = '';

    /**
     *  definition plugin types
     */
    const PLUGIN_TYPE_ALLTYPES = 0;
    const PLUGIN_TYPE_MODIFIER = 1;
    const PLUGIN_TYPE_ATTACHER = 2;

    /**
     * plugin type
     *
     * @var     int
     * @access  protected
     */
    var $_PluginType = Jaws_Plugin::PLUGIN_TYPE_MODIFIER;

    /**
     * Constructor
     *
     * @access  protected
     * @return  void
     */
    protected function __construct($plugin)
    {
        $this->name = $plugin;
        $this->app  = Jaws::getInstance();
    }

    /**
     * Get installed plugins
     *
     * @access  public
     * @return  array   installed plugins in separated groups
     */
    static function getinstalledPlugins()
    {
        static $installedPlugins;
        if (!isset($installedPlugins)) {
            $installedPlugins = array();
            $plugins = Jaws::getInstance()->registry->fetch('plugins_installed_items');
            if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
                $plugins = array_filter(explode(',', $plugins));
                foreach ($plugins as $plugin) {
                    $objPlugin = Jaws_Plugin::getInstance($plugin, false);
                    if (!Jaws_Error::IsError($objPlugin)) {
                        $fgadgets = Jaws::getInstance()->registry->fetch('frontend_gadgets', $plugin);
                        $bgadgets = Jaws::getInstance()->registry->fetch('backend_gadgets',  $plugin);
                        $installedPlugins[$objPlugin->pluginType][$plugin] = array (
                            'pluginType'       => $objPlugin->pluginType,
                            'onlyNormalMode'   => $objPlugin->onlyNormalMode,
                            'frontend_gadgets' => $fgadgets == '*'? '*' : array_filter(explode(',', $fgadgets)),
                            'backend_gadgets'  => $bgadgets == '*'? '*' : array_filter(explode(',', $bgadgets)),
                        );
                    }
                }
            }

            ksort($installedPlugins);
        }

        return $installedPlugins;
    }

    /**
     * Creates the Jaws_Plugin instance
     *
     * @access  public
     * @param   string  $plugin         Plugin name
     * @param   bool    $onlyInstalled  get instance if plugin installed
     * @return  object returns the instance
     */
    static function getInstance($plugin, $onlyInstalled = true)
    {
        static $instances = array();
        $plugin = preg_replace('/[^[:alnum:]_]/', '', $plugin);
        if (!isset($instances[$plugin])) {
            if (!is_dir(ROOT_JAWS_PATH . "plugins/$plugin")) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                    __FUNCTION__
                );
            }

            $file = ROOT_JAWS_PATH . "plugins/$plugin/Plugin.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                    __FUNCTION__
                );
            }

            // is plugin available?
            if (defined('JAWS_AVAILABLE_PLUGINS')) {
                static $available_plugins;
                if (!isset($available_plugins)) {
                    $available_plugins = array_filter(array_map('trim', explode(',', JAWS_AVAILABLE_PLUGINS)));
                }

                if (!in_array($plugin, $available_plugins)) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_PLUGIN_NOT_AVAILABLE', $plugin),
                        'Plugin availability check'
                    );
                }
            }

            require_once $file;
            $classname = $plugin. '_Plugin';
            $instances[$plugin] = new $classname($plugin);
            // only normal mode actions
            if (!isset($instances[$plugin]->onlyNormalMode)) {
                $instances[$plugin]->onlyNormalMode = false;
            }
            // plugin type
            if (!isset($instances[$plugin]->pluginType)) {
                $instances[$plugin]->pluginType = self::PLUGIN_TYPE_MODIFIER;
            }
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded plugin: $plugin");
        }

        if ($onlyInstalled && !self::IsPluginInstalled($plugin)) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_PLUGIN_NOT_INSTALLED', $plugin),
                __FUNCTION__
            );
        }

        return $instances[$plugin];
    }

    /**
     * Returns is plugin installed
     *
     * @access  public
     * @param   string  $plugin Plugin name
     * @return  bool    True or false, depends of the plugin status
     */
    public static function IsPluginInstalled($plugin)
    {
        $installed_plugins = Jaws::getInstance()->registry->fetch('plugins_installed_items');
        return (false !== strpos($installed_plugins, ",$plugin,"));
    }

    /**
     * Enable the plugin (creates the registry keys)
     *
     * @access  public
     * @param   string  $plugin Plugin name
     * @return  mixed   True if everything is OK or Jaws_Error on failure
     */
    static function InstallPlugin($plugin)
    {
        $objPlugin = Jaws_Plugin::getInstance($plugin, false);
        if (Jaws_Error::IsError($result)) {
            return $objPlugin;
        }

        // adding plugin to installed plugins list
        $installed_plugins = Jaws::getInstance()->registry->fetch('plugins_installed_items');
        if (false !== strpos($installed_plugins, ",$plugin,")) {
            return true;
        }
        $installed_plugins.= $plugin. ',';
        Jaws::getInstance()->registry->update('plugins_installed_items', $installed_plugins);

        // backend_gadgets
        Jaws::getInstance()->registry->insert(
            'backend_gadgets',
            (isset($objPlugin->backendEnabled) && $objPlugin->backendEnabled)? '*' : ',',
            false,
            $plugin
        );
        // frontend_gadgets
        Jaws::getInstance()->registry->insert(
            'frontend_gadgets',
            (isset($objPlugin->frontendEnabled) && $objPlugin->frontendEnabled)? '*' : ',',
            false,
            $plugin
        );

        // load plugin install method if exists
        if (method_exists($objPlugin, 'Install')) {
            $result = $objPlugin->Install();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Everything is done
        $res = Jaws::getInstance()->listener->Shout('Plugin', 'InstallPlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            //do nothing
        }

        return true;
    }

    /**
     * This function disables a plugin
     *
     * @access  public
     * @param   string  $plugin Plugin name
     * @return  mixed   True if everything is OK or Jaws_Error on failure
     */
    static function UninstallPlugin($plugin)
    {
        $objPlugin = Jaws_Plugin::getInstance($plugin);
        if (Jaws_Error::IsError($result)) {
            return $objPlugin;
        }
        
        // removing plugin from installed plugins list
        $installed_plugins = Jaws::getInstance()->registry->fetch('plugins_installed_items');
        $installed_plugins = str_replace(",$plugin,", ',', $installed_plugins);
        Jaws::getInstance()->registry->update('plugins_installed_items', $installed_plugins);

        // removing plugin registry keys
        Jaws::getInstance()->registry->Delete($plugin);

        // load plugin uninstall method
        if (method_exists($objPlugin, 'Uninstall')) {
            $result = $objPlugin->Uninstall();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Everything is done
        $res = Jaws::getInstance()->listener->Shout('Plugin', 'UninstallPlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string  $params Method parameters
     *
     * @return string
     */
    public static function t($string, ...$params)
    {
        if ($plugin = strstr($string, '.', true)) {
            $string = substr($string, strlen($plugin) + 1);
        } else {
            $plugin = strstr(get_called_class(), '_', true);
        }

        return Jaws_Translate::getInstance()->XTranslate(
            '',
            Jaws_Translate::TRANSLATE_PLUGIN,
            $plugin,
            $string,
            $params
        );
    }

    /**
     * Overloading __get magic method
     *
     * @access  private
     * @param   string  $property   Property name
     * @return  mixed   Requested property otherwise Jaws_Error
     */
    function __get($property)
    {
        switch ($property) {
            case 'plugin':
                return $this;
                break;

            default:
                break;
        }

        return Jaws_Error::raiseError(
            "Property '$property' not exists!",
            __FUNCTION__,
            JAWS_ERROR_ERROR,
            -1
        );
    }

}