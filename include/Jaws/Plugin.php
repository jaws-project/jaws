<?php
/**
 * Class parent of all plugins, features that each gadget can have
 * to print nice text/images
 *
 * @category   Plugins
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Plugin
{
    /**
     * @access  private
     * @var     string
     * @see     function  GetName
     */
    var $_Name;

    /**
     * @access  public
     * @var     string
     */
    var $description;

    /**
     * @access  private
     * @var     string
     * @see     function  GetExample
     */
    var $_Example;

    /**
     * @access  private
     * @var     bool
     * @see     function  IsFriendly
     */
    var $_IsFriendly;

    /**
     * @access  public
     * @var     string
     */
    var $version;

    /**
     * @access  private
     * @var     string
     * @see     function  GetAccessKey
     */
    var $_AccessKey;

    /**
     * Frontend available by default
     *
     * @var     bool
     * @access  protected
     */
    var $_DefaultFrontendEnabled = false;

    /**
     * Backend available by default
     *
     * @var     bool
     * @access  protected
     */
    var $_DefaultBackendEnabled = true;

    /**
     * Get the name of the plugin
     *
     * @access  public
     * @return  string Value of $_Name
     */
    function GetName()
    {
        return $this->_Name;
    }

    /**
     * Get the example of the plugin
     *
     * @access  public
     * @return  string value of $_Example
     */
    function GetExample()
    {
        return $this->_Example;
    }

    /**
     * Get the friendly state of the plugin, friendly or non-friendly
     *
     * @access  public
     * @return  bool    value of $_IsFriendly
     */
    function IsFriendly()
    {
        return $this->_IsFriendly;
    }

    /**
     * Get the accesskey of the plugin
     *
     * @access  public
     * @return  string Value of $_Accesskey
     */
    function GetAccesskey()
    {
        return $this->_AccessKey;
    }

    /**
     * Parse the text.
     *
     * @access  public
     * @param   string  $html Html to Parse
     * @return  string  The parsed Html
     */
    function ParseText($html)
    {
        //This method does nothing
        return $html;
    }

    /**
     *
     * Get the webcontrol of the plugin, usefull for the JawsEditor
     * @access  public
     * @return  string
     */
    function GetWebControl($textarea)
    {
        //Returns an empty text by default
        return '';
    }

    /**
     * Enable the plugin (creates the registry keys)
     *
     * @access  public
     * @return  bool     True if everything is OK or Jaws_Error on failure
     */
    function InstallPlugin($plugin = null)
    {
        if (is_null($plugin)) {
            $plugin = $this->_Name;
        }

        $file = JAWS_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php';
        if (!file_exists($file)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                __FUNCTION__
            );
        }

        // adding plugin to installed plugins list
        $installed_plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        if (false !== strpos($installed_plugins, ",$plugin,")) {
            return true;
        }
        $installed_plugins.= $plugin. ',';
        $GLOBALS['app']->Registry->update('plugins_installed_items', $installed_plugins);

        require_once $file;
        $pluginObj = new $plugin;
        $result = $pluginObj->Install();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $GLOBALS['app']->Registry->insert(
            'backend_gadgets',
            $pluginObj->_DefaultBackendEnabled? '*' : ',',
            $plugin
        );
        $GLOBALS['app']->Registry->insert(
            'frontend_gadgets',
            $pluginObj->_DefaultFrontendEnabled? '*' : ',',
            $plugin
        );

        // Everything is done
        $res = $GLOBALS['app']->Listener->Shout('InstallPlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * This function disables a plugin
     * @param   string $plugin The name of the plugin to disable
     * @access  public
     */
    function UninstallPlugin($plugin = null)
    {
        if (is_null($plugin)) {
            $plugin = $this->_Name;
        }

        $file = JAWS_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php';
        if (!file_exists($file)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                __FUNCTION__
            );
        }

        // removeing plugin from installed plugins list
        $installed_plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        $installed_plugins = str_replace(",$plugin,", ',', $installed_plugins);
        $GLOBALS['app']->Registry->update('plugins_installed_items', $installed_plugins);

        // removeing plugin registry keys
        $GLOBALS['app']->Registry->Delete($plugin);

        require_once $file;
        $pluginObj = new $plugin;
        $result = $pluginObj->Uninstall();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Everything is done
        $res = $GLOBALS['app']->Listener->Shout('UninstallPlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Install a plugin
     * Plugins should override this method only if they need to perform actions to install
     *
     * @access  public
     * @return  bool    True on successfull install and Jaws_Error on failure
     */
    function Install()
    {
        return true;
    }

    /**
     * Uninstall a plugin
     * Plugins should override this method only if they need to perform actions to uninstall
     *
     * @access  public
     * @return  mixed    True on a successful uninstall and Jaws_Error otherwise
     */
    function Uninstall()
    {
        return true;
    }

}