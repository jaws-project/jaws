<?php
/**
 * Class to manage translation of strings
 *
 * @category   JawsType
 * @package    Core
 * @author     Jorge A Gallegos <kad@gulags.org>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Translate
{
    /**
     * Default language to use
     *
     * @access private
     * @var    string
     */
    var $_defaultLanguage = 'en';

    /**
     * load user translated files
     *
     * @access private
     * @var    boolean
     */
    var $_load_user_translated = true;

    /**
     * Constructor
     *
     * @access public
     */
    function Jaws_Translate($load_user_translated = true)
    {
        $this->_load_user_translated = $load_user_translated;
    }

    /**
     * Initializes the Translate
     */
    function Init($lang = 'en')
    {
        $this->_defaultLanguage = $lang;
        $this->LoadTranslation('Global');
        $this->LoadTranslation('Date');
    }

    /**
     * Set the default language to use
     *
     * @access  public
     * @param   string  $lang  Language to use
     */
    function SetLanguage($lang)
    {
        $this->_defaultLanguage = $lang;
    }

    /**
     * Translate a string.
     *
     * @access public
     * @static
     * @param string $string The ID of the string to translate.
     * @param array $replacements An array replacements to make in the string.
     * @return string The tranlsated string, with replacements made.
     */
    function Translate($lang, $string, $replacements = array())
    {
        $lang = strtoupper(empty($lang)? $this->_defaultLanguage : $lang);
        $base_translate = "_EN_$string";
        $orig_translate = "_{$lang}_$string";
        $data_translate = "_{$lang}_DATA_$string";
        if (defined($data_translate)) {
            $string = constant($data_translate);
        } elseif (defined($orig_translate)) {
            $string = constant($orig_translate);
        } elseif (defined($base_translate)) {
            $string = constant($base_translate)."*";
        }

        $count = count($replacements);
        if ($count) {
            for ($i = 0; $i < $count; $i++) {
                $string = str_replace('{' . $i . '}', $replacements[$i], $string);
            }
        }

        if (strpos($string, '{') !== false) {
            $originalString = $string;
            $string = preg_replace('/[\s\{[0-9]+\}]*/', '', $string);
            if ($originalString != $string) {
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG,
                                     'A placeholder was not replaced while trying to translate ' . $string);
            }
        }

        return $string;
    }

    /**
     * Loads a translation file.
     *
     * Loaded translations are kept in $GLOBALS['i18n'], so that they aren't
     * reloaded.
     *
     * @access public
     * @static
     * @param string module The translation to load.
     * @param string type   Type of modulr(JAWS_OTHERS, JAWS_GADGET, JAWS_PLUGIN)
     * @return void
     */
    function LoadTranslation($module, $type = JAWS_OTHERS, $lang = null)
    {
        $language = $this->_defaultLanguage;
        if ($module == 'Date' && isset($GLOBALS['app'])) {
            $language = $GLOBALS['app']->GetCalendarLanguage();
        }
        $language = empty($lang) ? $language : $lang;

        // Make sure the arrays are setup
        if (!isset($GLOBALS['i18n'])) {
            $GLOBALS['i18n'] = array();
        }

        if (!isset($GLOBALS['i18n'][$language])) {
            $GLOBALS['i18n'][$language] = array();
        }

        if (!isset($GLOBALS['i18n']['en'])) {
            $GLOBALS['i18n']['en'] = array();
        }

        // Only attempt to load a translation if it isn't already loaded.
        if (in_array(array($module, $type), $GLOBALS['i18n'][$language])) {
            return true;
        }

        switch ($type) {
            case JAWS_GADGET:
                $orig_i18n = JAWS_PATH . "gadgets/$module/languages/$language.php";
                $data_i18n = JAWS_DATA . "languages/$language/gadgets/$module.php";
                $fall_back = JAWS_PATH . "gadgets/$module/languages/en.php";
                break;

            case JAWS_PLUGIN:
                $orig_i18n = JAWS_PATH . "plugins/$module/languages/$language.php";
                $data_i18n = JAWS_DATA . "languages/$language/plugins/$module.php";
                $fall_back = JAWS_PATH . "plugins/$module/languages/en.php";
                break;

            default:
                $orig_i18n = JAWS_PATH . "languages/$language/$module.php";
                $data_i18n = JAWS_DATA . "languages/$language/$module.php";
                $fall_back = JAWS_PATH . "languages/en/$module.php";
        }

        $GLOBALS['i18n'][$language][] = array($module, $type);
        if ($this->_load_user_translated && file_exists($data_i18n)) {
            require_once $data_i18n;
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded data translation for $module, language $language");
        }

        if (file_exists($orig_i18n)) {
            require_once $orig_i18n;
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded translation for $module, language $language");
        } else {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "No translation could be found for $module for language $language");
        }

        if ($language != 'en') {
            if (file_exists($fall_back)) {
                require_once $fall_back;
                $GLOBALS['i18n']['en'][] = array($module, $type);
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded fallback translation for $module, language en");
            } else {
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG,
                                     "No fallback translation could be found for $module for language en");
            }
        }
    }
}

/**
 * Convenience function to translate strings.
 *
 * Passes it's arguments to Jaws_Translate::Translate to do the actual translation.
 *
 * @access public
 * @param string        string The string to translate.
 * @return string
 */
function _t($string)
{
    $args = array();
    if (func_num_args() > 1) {
        $args = func_get_args();

        // Argument 1 is the string to be translated.
        array_shift($args);
    }

    return isset($GLOBALS['app']->Translate)?
           $GLOBALS['app']->Translate->Translate(null, $string, $args) :
           $GLOBALS['i10n']->Translate(null, $string, $args);
}

/**
 * Convenience function to translate strings.
 *
 * Passes it's arguments to Jaws_Translate::Translate to do the actual translation.
 *
 * @access public
 * @param string        lang The language.
 * @param string        string The string to translate.
 * @return string
 */
function _t_lang($lang, $string)
{
    $args = array();
    if (func_num_args() > 2) {
        $args = func_get_args();

        // Argument 1th for lang and argument 2th is the string to be translated.
        array_shift($args);
        array_shift($args);
    }

    return isset($GLOBALS['app']->Translate)?
           $GLOBALS['app']->Translate->Translate($lang, $string, $args) :
           $GLOBALS['i10n']->Translate($lang, $string, $args);
}