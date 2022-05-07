<?php
/**
 * Class to manage translation of strings
 *
 * @category   JawsType
 * @package    Core
 * @author     Jorge A Gallegos <kad@gulags.org>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Translate
{
    /**
     * Translate types
     *
     * @const   int     
     * @access  public
     */
    const TRANSLATE_GLOBAL  = 0;
    const TRANSLATE_GADGET  = 1;
    const TRANSLATE_PLUGIN  = 2;
    const TRANSLATE_INSTALL = 4;
    const TRANSLATE_UPGRADE = 5;

    /**
     * Gadgets list array
     *
     * @access  private
     * @var     array
     */
    private static $real_gadgets_module = array();

    /**
     * plugins list array
     *
     * @access  private
     * @var     array
     */
    private static $real_plugins_module = array();

    /**
     * Default language to use
     *
     * @access  private
     * @var     string
     */
    var $_defaultLanguage = 'en';

    /**
     * load user translated files
     *
     * @access  private
     * @var     bool
     */
    var $_load_user_translated = true;

    /**
     * store components translates data
     *
     * @access  private
     * @var     bool
     */
    var $translates = array();

    /**
     * Constructor
     *
     * @access  private
     * @param   bool    $load_user_translated   Loaded user customized translated statements
     * @return  void
     */
    private function __construct($load_user_translated)
    {
        $gDir = ROOT_JAWS_PATH . 'gadgets/';
        $gadgets = scandir($gDir);
        foreach ($gadgets as $gadget) {
            if ($gadget[0] == '.' || !is_dir($gDir . $gadget)) {
                continue;
            }
            self::$real_gadgets_module[strtoupper($gadget)] = $gadget;
        }

        $pDir = ROOT_JAWS_PATH . 'plugins/';
        $plugins = scandir($pDir);
        foreach ($plugins as $plugin) {
            if ($plugin[0] == '.' || !is_dir($pDir . $plugin)) {
                continue;
            }
            self::$real_plugins_module[strtoupper($plugin)] = $plugin;
        }

        $this->_load_user_translated = $load_user_translated;
    }

    /**
     * Creates the Jaws_Translate instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   bool    $load_user_translated   Loaded user customized translated statements
     * @return  object  returns the instance
     */
    static function getInstance($load_user_translated = true)
    {
        static $objTranslate;
        if (!isset($objTranslate)) {
            $objTranslate = new Jaws_Translate($load_user_translated);
        }

        return $objTranslate;
    }

    /**
     * Initializes the Translate
     *
     * @access  public
     * @param   bool    $load_user_translated   Loaded user customized translated statements
     * @return  void
     */
    function init($lang = 'en')
    {
        $this->_defaultLanguage = $lang;
    }

    /**
     * Set the default language to use
     *
     * @access  public
     * @param   string  $lang  Language to use
     * @return  void
     */
    function SetLanguage($lang)
    {
        $this->_defaultLanguage = $lang;
    }

    /**
     * Translate a string.
     *
     * @access  public
     * @param   int     $type       Component type
     * @param   string  $component  Called component name
     * @param   string  $string     Statement
     * @param   array   $params     Statement parameters
     * @return  string The translated string, with replacements made.
     */
    function XTranslate($lang, $type, $component, $string, $params)
    {
        $lang = empty($lang)? $this->_defaultLanguage : $lang;

        switch ($type) {
            case self::TRANSLATE_GLOBAL:
                $string = strtoupper('GLOBAL_' . $string);
                break;

            case self::TRANSLATE_GADGET:
                if (array_key_exists($component, self::$real_gadgets_module)) {
                    $component = self::$real_gadgets_module[$component];
                }
                $string = strtoupper($component . '_'. $string);
                break;

            case self::TRANSLATE_PLUGIN:
                if (array_key_exists($component, self::$real_plugins_module)) {
                    $component = self::$real_plugins_module[$component];
                }
                $string = strtoupper('PLUGINS_' . $component . '_' . $string);
                break;

            case self::TRANSLATE_INSTALL:
                $string = strtoupper('INSTALL_' . $string);
                break;

            case self::TRANSLATE_UPGRADE:
                $string = strtoupper('UPGRADE_' . $string);
                break;

            default:
                return $string;
        }

        // autoload not loaded component language
        if (!isset($this->translates[$lang][$type][$component])) {
            $this->LoadTranslation($component, $type, $lang);
        }

        if (isset($this->translates[$lang][$type][$component][$string])) {
            $string = str_replace('\n', "\n", $this->translates[$lang][$type][$component][$string]);
        }

        foreach ($params as $key => $value) {
            $string = str_replace('{' . $key . '}', $value, $string);
        }

        if (strpos($string, '{') !== false) {
            $string = preg_replace('/\s*{[0-9]+\}/u', '', $string);
        }

        return $string;
    }

    /**
     * Translate a string.
     *
     * @access  public
     * @param   string  $lang       Language code
     * @param   string  $string     The ID of the string to translate.
     * @param   array   $parameters An array replacements to make in the string.
     * @return  string The translated string, with replacements made.
     */
    function Translate($lang, $string, $parameters = array())
    {
        $lang = empty($lang)? $this->_defaultLanguage : $lang;
        @list($type, $module) = explode('_', $string);
        switch ($type) {
            case 'GLOBAL':
                $type = 0;
                $module = 'Global';
                break;

            case 'PLUGINS':
                $type = 2;
                $module = self::$real_plugins_module[$module];
                break;

            case 'INSTALL':
                $type = 4;
                $module = 'Install';
                break;

            case 'UPGRADE':
                $type = 5;
                $module = 'Upgrade';
                break;

            default:
                if (!array_key_exists($type, self::$real_gadgets_module)) {
                    return $string;
                }

                $module = self::$real_gadgets_module[$type];
                $type = 1;
        }

        // autoload not loaded module language
        if (!isset($this->translates[$lang][$type][$module])) {
            $this->LoadTranslation($module, $type, $lang);
        }

        if (isset($this->translates[$lang][$type][$module][$string])) {
            $string = str_replace('\n', "\n", $this->translates[$lang][$type][$module][$string]);
        }

        foreach ($parameters as $key => $value) {
            $string = str_replace('{' . $key . '}', $value, $string);
        }

        if (strpos($string, '{') !== false) {
            $string = preg_replace('/\s*{[0-9]+\}/u', '', $string);
        }

        return $string;
    }

    /**
     * Loads a translation file.
     *
     * Loaded translations are kept in $GLOBALS['i18n'], so that they aren't
     * reloaded.
     *
     * @access  public
     * @param   string  $module The translation to load
     * @param   string  $type   Type of module(TRANSLATE_GLOBAL, TRANSLATE_GADGET, TRANSLATE_PLUGIN)
     * @param   string  $lang   Optional language code
     * @return  void
     */
    function LoadTranslation($module, $type = self::TRANSLATE_GLOBAL, $lang = null)
    {
        $language = empty($lang) ? $this->_defaultLanguage : $lang;

        // Only attempt to load a translation if it isn't already loaded.
        if (isset($this->translates[$language][$type][strtoupper($module)])) {
            return;
        }

        switch ($type) {
            case self::TRANSLATE_GADGET:
                if ($language == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "gadgets/$module/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$language/gadgets/$module.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$language/gadgets/$module.ini";
                break;

            case self::TRANSLATE_PLUGIN:
                if ($language == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "plugins/$module/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$language/plugins/$module.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$language/plugins/$module.ini";
                break;

            case self::TRANSLATE_INSTALL:
                if ($language == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "install/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$language/Install.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$language/Install.ini";
                break;

            case self::TRANSLATE_UPGRADE:
                if ($language == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "upgrade/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$language/Upgrade.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$language/Upgrade.ini";
                break;

            default:
                if ($language == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "include/Jaws/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$language/Global.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$language/Global.ini";
        }

        $tmp_orig = array();
        if (file_exists($orig_i18n)) {
            $tmp_orig = parse_ini_file($orig_i18n, false, INI_SCANNER_RAW);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded translation for $module, language $language");
        } else {
            $GLOBALS['log']->Log(JAWS_DEBUG, "No translation could be found for $module for language $language");
        }

        $tmp_data = array();
        if ($this->_load_user_translated && Jaws_FileManagement_File::file_exists($data_i18n)) {
            $tmp_data = Jaws_FileManagement_File::parse_ini_file($data_i18n, false, INI_SCANNER_RAW);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded data translation for $module, language $language");
        }

        $this->translates[$language][$type][$module] = array_merge($tmp_orig, $tmp_data);
    }

    /**
     * Add a new translation statement
     *
     * @access  public
     * @param   string  $module     Module name
     * @param   string  $key_name   Key name
     * @param   string  $key_value  Key value
     * @param   string  $type       Type of module(TRANSLATE_GLOBAL, TRANSLATE_GADGET, TRANSLATE_PLUGIN)
     * @param   string  $lang       Optional language code
     * @return  void
     */
    function AddTranslation($module, $key_name, $key_value, $type = self::TRANSLATE_GLOBAL, $lang = null)
    {
        $language = empty($lang)? $this->_defaultLanguage : $lang;
        $this->translates[$language][$type][$module][strtoupper($key_name)] = $key_value;
        return true;
    }

}