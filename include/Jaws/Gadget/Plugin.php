<?php
/**
 * Jaws Gadget Plugin
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Plugin
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
        $this->app = Jaws::getInstance();
    }


    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text           Text to parse
     * @param   int     $pluginsType    Plugins type(PLUGIN_TYPE_ALLTYPES, PLUGIN_TYPE_MODIFIER, PLUGIN_TYPE_ATTACHER)
     * @param   int     $reference      Action reference entity
     * @param   string  $action         Gadget action name
     * @param   string  $gadget         Gadget name
     * @return  string  Returns the parsed text
     */
    function parse($text, $pluginsType = Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
        $reference = 0, $action = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        $installedPlugins = Jaws_Plugin::getinstalledPlugins();
        foreach ($installedPlugins as $pluginType => $plugins) {
            // only parse via requested plugins type
            if ($pluginsType && $pluginsType != $pluginType) {
                continue;
            }

            foreach ($plugins as $plugin => $properties) {
                if ($properties['onlyNormalMode'] && $this->app->requestedActionMode != 'normal') {
                    continue;
                }
                // check is plugin enabled on this gadget
                if ($properties['frontend_gadgets'] == '*' || in_array($gadget, $properties['frontend_gadgets'])) {
                    $text = Jaws_Plugin::getInstance($plugin, false)->ParseText($text, $reference, $action, $gadget);
                }
            }
        }

        return $text;
    }


    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text           Text to parse
     * @param   int     $pluginsType    Plugins type(PLUGIN_TYPE_ALLTYPES, PLUGIN_TYPE_MODIFIER, PLUGIN_TYPE_ATTACHER)
     * @param   int     $reference      Action reference entity
     * @param   string  $action         Gadget action name
     * @param   string  $gadget         Gadget name
     * @return  string  Returns the parsed text
     */
    function parseAdmin($text, $pluginsType = Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
        $reference = 0, $action = '', $gadget = '')
    {
        $gadget = empty($gadget)? $this->gadget->name : $gadget;
        $installedPlugins = Jaws_Plugin::getinstalledPlugins();
        foreach ($installedPlugins as $pluginType => $plugins) {
            // only parse via requested plugins type
            if ($pluginsType && $pluginsType != $pluginType) {
                continue;
            }

            foreach ($plugins as $plugin => $properties) {
                if ($properties['onlyNormalMode'] && $this->app->requestedActionMode != 'normal') {
                    continue;
                }
                // check is plugin enabled on this gadget
                if ($properties['backend_gadgets'] == '*' || in_array($gadget, $properties['backend_gadgets'])) {
                    $text = Jaws_Plugin::getInstance($plugin, false)->ParseText($text, $reference, $action, $gadget);
                }
            }
        }

        return $text;
    }

}