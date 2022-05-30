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
 * @copyright  2005-2022 Jaws Development Group
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
     * store modules translates data
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
     * @param   int     $type       Type of module
     * @param   string  $module     Module name
     * @param   string  $string     Statement
     * @param   array   $params     Statement parameters
     * @return  string The translated string, with replacements made.
     */
    function XTranslate($lang, $type, $module, $string, $params)
    {
        $lang = empty($lang)? $this->_defaultLanguage : $lang;
        $string = strtoupper($string);
        $module = strtoupper($module);

        switch ($type) {
            case self::TRANSLATE_GLOBAL:
            case self::TRANSLATE_INSTALL:
            case self::TRANSLATE_UPGRADE:
            case self::TRANSLATE_GADGET:
            case self::TRANSLATE_PLUGIN:
                break;

            default:
                return $string;
        }

        // autoload not loaded module language
        if (!isset($this->translates[$lang][$type][$module])) {
            if (!$this->LoadTranslation($module, $type, $lang)) {
                return $string;
            }
        }

        if (isset($this->translates[$lang][$type][$module][$string])) {
            $string = Jaws_UTF8::str_replace(
                array('\n', '\"'),
                array("\n", '"'),
                $this->translates[$lang][$type][$module][$string]
            );
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
     * Loads a translation file.
     *
     * @access  public
     * @param   string  $module The translation to load
     * @param   string  $type   Type of module(TRANSLATE_GLOBAL, TRANSLATE_GADGET, TRANSLATE_PLUGIN)
     * @param   string  $lang   Optional language code
     * @return  mixed   
     */
    function LoadTranslation($module, $type = self::TRANSLATE_GLOBAL, $lang = null)
    {
        $lang = empty($lang) ? $this->_defaultLanguage : $lang;
        $module = strtoupper($module);

        // Only attempt to load a translation if it isn't already loaded.
        if (isset($this->translates[$lang][$type][$module])) {
            return $this->translates[$lang][$type][$module];
        }

        switch ($type) {
            case self::TRANSLATE_GADGET:
                if (!array_key_exists($module, self::$real_gadgets_module)) {
                    return false;
                }
                $module = self::$real_gadgets_module[$module];

                if ($lang == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "gadgets/$module/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$lang/gadgets/$module.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$lang/gadgets/$module.ini";
                break;

            case self::TRANSLATE_PLUGIN:
                if (!array_key_exists($module, self::$real_plugins_module)) {
                    return false;
                }
                $module = self::$real_plugins_module[$module];

                if ($lang == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "plugins/$module/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$lang/plugins/$module.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$lang/plugins/$module.ini";
                break;

            case self::TRANSLATE_INSTALL:
                if ($lang == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "install/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$lang/Install.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$lang/Install.ini";
                break;

            case self::TRANSLATE_UPGRADE:
                if ($lang == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "upgrade/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$lang/Upgrade.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$lang/Upgrade.ini";
                break;

            default:
                if ($lang == 'en') {
                    $orig_i18n = ROOT_JAWS_PATH . "include/Jaws/Resources/translates.ini";
                } else {
                    $orig_i18n = ROOT_JAWS_PATH . "languages/$lang/Global.ini";
                }
                $data_i18n = ROOT_DATA_PATH . "languages/$lang/Global.ini";
        }

        $tmp_orig = array();
        if (file_exists($orig_i18n)) {
            $tmp_orig = parse_ini_file($orig_i18n, false, INI_SCANNER_RAW);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded translation for $module, language $lang");
        } else {
            $GLOBALS['log']->Log(JAWS_DEBUG, "No translation could be found for $module for language $lang");
        }

        $tmp_data = array();
        if ($this->_load_user_translated && Jaws_FileManagement_File::file_exists($data_i18n)) {
            $tmp_data = Jaws_FileManagement_File::parse_ini_file($data_i18n, false, INI_SCANNER_RAW);
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded data translation for $module, language $lang");
        }

        return $this->translates[$lang][$type][strtoupper($module)] = $tmp_data + $tmp_orig;;
    }

    /**
     * Gets module translations
     *
     * @access  public
     * @param   string  $module The translation to load
     * @param   string  $type   Type of module(TRANSLATE_GLOBAL, TRANSLATE_GADGET, TRANSLATE_PLUGIN)
     * @param   string  $lang   Optional language code
     * @return  mixed
     */
    function getTranslation($module, $type = self::TRANSLATE_GLOBAL, $lang = null)
    {
        $lang = empty($lang) ? $this->_defaultLanguage : $lang;
        return $this->LoadTranslation($module, $type, $lang);
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
        $lang = empty($lang)? $this->_defaultLanguage : $lang;
        $this->translates[$lang][$type][strtoupper($module)][strtoupper($key_name)] = $key_value;
        return true;
    }

}