<?php
/**
 * Class parent of all plugins, features that each plugin can have
 * to print nice text/images
 *
 * @category   Plugins
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Plugin
{
    /**
     * @access  public
     * @var     string
     */
    var $name;

    /**
     * @access  public
     * @var     string
     */
    var $title;

    /**
     * @access  public
     * @var     string
     */
    var $description;

    /**
     * @access  public
     * @var     string
     */
    var $example;

    /**
     * @access  public
     * @var     bool
     */
    var $friendly;

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
     * Constructor
     *
     * @access  protected
     * @param   string $plugin Plugin name(same as the filesystem name)
     * @return  void
     */
    function Jaws_Plugin($plugin)
    {
        $plugin = preg_replace('/[^[:alnum:]_]/', '', $plugin);
        $this->name        = $plugin;
        $this->title       = $plugin;
        $this->example     = _t('PLUGINS_'. strtoupper($plugin). '_EXAMPLE');
        $this->description = _t('PLUGINS_'. strtoupper($plugin). '_DESCRIPTION');
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
     * @param   string  $plugin Plugin name
     * @return  mixed   True if everything is OK or Jaws_Error on failure
     */
    static function InstallPlugin($plugin)
    {
        $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
        if (Jaws_Error::IsError($result)) {
            return $objPlugin;
        }

        // adding plugin to installed plugins list
        $installed_plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        if (false !== strpos($installed_plugins, ",$plugin,")) {
            return true;
        }
        $installed_plugins.= $plugin. ',';
        $GLOBALS['app']->Registry->update('plugins_installed_items', $installed_plugins);

        // load plugin install method
        $result = $objPlugin->Install();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $GLOBALS['app']->Registry->insert(
            'backend_gadgets',
            $objPlugin->_DefaultBackendEnabled? '*' : ',',
            false,
            $plugin
        );
        $GLOBALS['app']->Registry->insert(
            'frontend_gadgets',
            $objPlugin->_DefaultFrontendEnabled? '*' : ',',
            false,
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
     *
     * @access  public
     * @param   string  $plugin Plugin name
     * @return  mixed   True if everything is OK or Jaws_Error on failure
     */
    static function UninstallPlugin($plugin)
    {
        $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
        if (Jaws_Error::IsError($result)) {
            return $objPlugin;
        }
        
        // removeing plugin from installed plugins list
        $installed_plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        $installed_plugins = str_replace(",$plugin,", ',', $installed_plugins);
        $GLOBALS['app']->Registry->update('plugins_installed_items', $installed_plugins);

        // removeing plugin registry keys
        $GLOBALS['app']->Registry->Delete($plugin);

        // load plugin uninstall method
        $result = $objPlugin->Uninstall();
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