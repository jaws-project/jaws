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
     * @access  private
     * @var     string
     * @see     function  GetDescription
     */
    var $_Description;

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
     * @access  private
     * @var     string
     * @see     function  GetVersion
     */
    var $_Version;

    /**
     * @access  private
     * @var     string
     * @see     function  GetAccessKey
     */
    var $_AccessKey;

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
     * Get the description of the plugin
     *
     * @access  public
     * @return  string Value of $_Description
     */
    function GetDescription()
    {
        return $this->_Description;
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
     * Get the version of the plugin
     *
     * @access  public
     * @return  string value of $_Version
     */
    function GetVersion()
    {
        return $this->_Version;
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
     * Deprecated method
     */
    function LoadTranslation()
    {
        return true;
    }

    /**
     * Enable the plugin (creates the registry keys)
     *
     * @access  public
     * @return  bool     True if everything is OK or Jaws_Error on failure
     */
    function EnablePlugin($plugin = null)
    {
        if (is_null($plugin)) {
            $plugin = $this->_Name;
        }

        if (strtolower($plugin) === 'core') {
            return new Jaws_Error(_t('_JMS_PLUGINS_PLUGIN_CANT_HAVE_NAME_CORE', $plugin),
                                     __FUNCTION__);
        }

        $file = JAWS_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php';
        if (!file_exists($file)) {
            return new Jaws_Error(_t('_JMS_PLUGINS_PLUGIN_DOESNT_EXISTS', $plugin),
                                     __FUNCTION__);
        }

        if (
            !$GLOBALS['app']->Registry->NewKey('enabled', 'true', $plugin, JAWS_COMPONENT_PLUGIN) ||
            !$GLOBALS['app']->Registry->NewKey('use_in', '*', $plugin, JAWS_COMPONENT_PLUGIN)
        ) {
            return new Jaws_Error(_t('JMS_PLUGINS_ENABLED_FAILURE', $plugin),
                                     __FUNCTION__);
        }

        // Put it in the enabled plugin record
        $items = $GLOBALS['app']->Registry->Get('plugins_admin_enabled_items');
        if (!in_array($plugin, explode(',', $items))) {
            $GLOBALS['app']->Registry->Set('plugins_admin_enabled_items', $items.','.$plugin);
        }

        require_once $file;
        $pluginObj = new $plugin;
        $result = $pluginObj->InstallPlugin();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('JMS_PLUGINS_ENABLED_FAILURE', $plugin),
                                     __FUNCTION__);
        }

        // Everything is done
        $res = $GLOBALS['app']->Event->Shout('EnablePlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Install the plugin
     *
     * @access  public
     * @return  string
     */
    function InstallPlugin()
    {
        return true;
    }

    /**
     * Uninstalls the plugin
     *
     * @access  public
     * @return  string
     */
    function UninstallPlugin()
    {
        return true;
    }

    /**
     * This function disables a plugin
     * @param   string $plugin The name of the plugin to disable
     * @access  public
     */
    function DisablePlugin($plugin = null)
    {
        if (is_null($plugin)) {
            $plugin = $this->_Name;
        }

        $file = JAWS_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php';
        if (!file_exists($file)) {
            return new Jaws_Error(_t('_GLOBAL_PLUGINS_PLUGIN_DOES_NOT_EXISTS', $plugin),
                                     __FUNCTION__);
        }


        $pull = $GLOBALS['app']->Registry->Get('plugins_admin_enabled_items');
        $new  = str_replace(',' . $plugin, '', $pull);

        $GLOBALS['app']->Registry->Set('plugins_admin_enabled_items', $new);
        $GLOBALS['app']->Registry->Delete($plugin, 'enabled', JAWS_COMPONENT_PLUGIN);
        $GLOBALS['app']->Registry->Delete($plugin, 'use_in', JAWS_COMPONENT_PLUGIN);

        require_once $file;
        $pluginObj = new $plugin;
        $result = $pluginObj->UninstallPlugin();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('JMS_PLUGINS_DISABLE_FAILURE', $plugin),
                                     __FUNCTION__);
        }

        // Everything is done
        $res = $GLOBALS['app']->Event->Shout('DisablePlugin', $plugin);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }
}
