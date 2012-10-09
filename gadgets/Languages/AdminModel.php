<?php
/**
 * Languages Core Gadget
 *
 * @category   GadgetModel
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
define('EMPTY_STRING', "-EMPTY-");

class LanguagesAdminModel extends Jaws_Model
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'languages' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('LANGUAGES_NAME'));
        }

        $GLOBALS['app']->Registry->NewKey('/gadgets/Languages/base_lang', 'en');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Languages/update_default_lang', 'false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Languages/pluggable', 'false');
        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateGadget($old, $new)
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'languages' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('LANGUAGES_NAME'));
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Languages/use_data_lang');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Languages/update_default_lang', 'false');

        // ACL keys
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Languages/ModifyLanguageProperties', 'false');

        return true;
    }

    /**
     * Add/Edit language's profile(local/international name, ...)
     *
     * @access  public
     * @param   string  $lang_str   Language code and name
     * @return  bool    True on Success or False on failure
     */
    function SaveLanguage($lang_str)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        if ($lang_str == $xss->parse($lang_str)) {
            $lang_code = substr($lang_str, 0, strpos($lang_str, ';'));
            if (preg_match("/^([a-z]{2})$|^([a-z]{2}[-][a-z]{2})$/", $lang_code)) {
                $lang_name = substr($lang_str, strpos($lang_str, ';')+1);
                if (!empty($lang_name) || trim($lang_name) == $lang_name) {
                    $use_data_lang = $GLOBALS['app']->Registry->Get('/gadgets/Languages/use_data_lang') == 'true';
                    $jaws_lang_dir = ($use_data_lang? JAWS_DATA : JAWS_PATH) . "languages";

                    $lang_dir = $jaws_lang_dir. DIRECTORY_SEPARATOR. $lang_code;
                    if (!Jaws_Utils::mkdir($lang_dir, 2)) {
                        $GLOBALS['app']->Session->PushLastResponse(
                                            _t('GLOBAL_ERROR_FAILED_CREATING_DIR'),
                                            RESPONSE_ERROR);
                        return false;
                    }

                    if (!Jaws_Utils::is_writable($jaws_lang_dir)) {
                        $GLOBALS['app']->Session->PushLastResponse(
                                            _t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE'),
                                            RESPONSE_ERROR);
                        return false;
                    }

                    $lang_exist = @is_dir($lang_dir);
                    $lang_fname_file = $lang_dir. DIRECTORY_SEPARATOR. 'FullName';
                    if (Jaws_Utils::file_put_contents($lang_fname_file, $lang_name)) {
                        if ($lang_exist) {
                            $GLOBALS['app']->Session->PushLastResponse(
                                            _t('LANGUAGES_LANGUAGE_UPDATED', $lang_code),
                                            RESPONSE_NOTICE);
                        } else {
                            $GLOBALS['app']->Session->PushLastResponse(
                                            _t('LANGUAGES_LANGUAGE_ADDED', $lang_code),
                                            RESPONSE_NOTICE);
                        }
                        return true;
                    } else {
                        if ($lang_exist) {
                            $GLOBALS['app']->Session->PushLastResponse(
                                            _t('LANGUAGES_LANGUAGE_UPDATE_ERROR', $lang_code),
                                            RESPONSE_ERROR);
                        } else {
                            $GLOBALS['app']->Session->PushLastResponse(
                                            _t('LANGUAGES_LANGUAGE_ADD_ERROR', $lang_code),
                                            RESPONSE_ERROR);
                        }
                        return false;
                    }
                }
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LANGUAGES_NAME_ERROR'), RESPONSE_ERROR);
        return false;
    }

    /**
     * Get grouped Jaws component list
     *
     * @access  public
     * @return  array   List of components
     */
    function GetComponents()
    {
        /**
         *
         */
        function GetModulesList($type = 'gadgets')
        {
            $modules = array();
            $mDir = JAWS_PATH . $type . DIRECTORY_SEPARATOR;
            if (!is_dir($mDir)) {
                return $modules;
            }
            $dir = scandir($mDir);
            foreach($dir as $file) {
                if ($file != '.' && $file != '..' && !strpos($file, '.php') && $file != '.svn') {
                    $modules[] = $file;
                }
            }
            asort($modules);
            return $modules;        
        }

        $components = array();
        $components[JAWS_OTHERS] = array('Global', 'Date', 'Install', 'Upgrade');
        $components[JAWS_GADGET] = GetModulesList('gadgets');
        $components[JAWS_PLUGIN] = GetModulesList('plugins');
        return $components;
    }

    /**
     * Returns an array of module language data
     *
     * @access  public
     * @param   string  $module
     * @param   string  $type
     * @param   string  $langTo
     * @param   string  $langFrom
     * @return  mixed   A list of module language string or false on error
     */
    function GetLangData($module, $type, $langTo, $langFrom)
    {
        switch ($type) {
            case JAWS_GADGET:
                $data_file = JAWS_DATA . "languages/$langTo/gadgets/$module.php";
                $orig_file = JAWS_PATH . "gadgets/$module/languages/$langTo.php";
                $from_file = JAWS_PATH . "gadgets/$module/languages/$langFrom.php";
                break;

            case JAWS_PLUGIN:
                $data_file = JAWS_DATA . "languages/$langTo/plugins/$module.php";
                $orig_file = JAWS_PATH . "plugins/$module/languages/$langTo.php";
                $from_file = JAWS_PATH . "plugins/$module/languages/$langFrom.php";
                $module = 'Plugins_' . $module;
                break;

            default:
                $data_file = JAWS_DATA . "languages/$langTo/$module.php";
                $orig_file = JAWS_PATH . "languages/$langTo/$module.php";
                $from_file = JAWS_PATH . "languages/$langFrom/$module.php";
        }

        if (!file_exists($from_file)) {
            return false;
        }

        $data = array();
        if (file_exists($orig_file)) {
            require_once $orig_file;
            $contents = file_get_contents($orig_file);
        }

        if (file_exists($data_file)) {
            require_once $data_file;
            $contents = file_get_contents($data_file);
        }

        @require_once $from_file;
        $fromstrings = get_defined_constants();

        $global = JAWS_PATH . "languages/$langTo/Global.php";
        if (file_exists($global)) {
            @require_once $global;
        }

        if (defined('_' . strtoupper($langTo) . '_GLOBAL_LANG_DIRECTION')) {
            $data['lang_direction'] = constant('_' . strtoupper($langTo) . '_GLOBAL_LANG_DIRECTION');
        } else {
            $data['lang_direction'] = 'ltr';
        }

        // Metadata
        preg_match('/"Last-Translator:(.*)"/', isset($contents)?$contents:'', $res);
        $data['meta']['Last-Translator'] = !empty($res) ? trim($res[1]) : '';

        // Strings
        foreach ($fromstrings as $k => $v) {
            if (strpos($k, strtoupper("_{$langFrom}_{$module}")) === false) {
                continue;
            }
            $cons = str_replace('_' . strtoupper($langFrom) . '_', '', $k);
            $data['strings'][$cons][$langFrom] = $v;
            $toValue = '';
            if (defined('_' . strtoupper($langTo) . '_DATA_' . $cons)) {
                $toValue = constant('_' . strtoupper($langTo) . '_DATA_' . $cons);
                if ($toValue == '') {
                    $toValue = EMPTY_STRING;
                }
            } elseif (defined('_' . strtoupper($langTo) . '_' . $cons)) {
                $toValue = constant('_' . strtoupper($langTo) . '_' . $cons);
                if ($toValue == '') {
                    $toValue = EMPTY_STRING;
                }
            }
            $data['strings'][$cons][$langTo] = $toValue;
        }
        return $data;
    }

    /**
     * Save language data into file
     *
     * @access  public
     * @param   string  $module
     * @param   string  $type
     * @param   string  $langTo
     * @param   array   $data
     * @return  bool    True on Success or False on failure
     */
    function SetLangData($module, $type, $langTo, $data = null)
    {
        $module_name = $module;
        switch ($type) {
            case JAWS_GADGET:
                $data_file = JAWS_DATA . "languages/$langTo/gadgets/$module.php";
                $orig_file = JAWS_PATH . "gadgets/$module/languages/$langTo.php";
                break;

            case JAWS_PLUGIN:
                $data_file = JAWS_DATA . "languages/$langTo/plugins/$module.php";
                $orig_file = JAWS_PATH . "plugins/$module/languages/$langTo.php";
                $module_name = 'Plugins_' . $module;
                break;

            default:
                $data_file = JAWS_DATA . "languages/$langTo/$module.php";
                $orig_file = JAWS_PATH . "languages/$langTo/$module.php";
        }

        $update_default_lang = $GLOBALS['app']->Registry->Get('/gadgets/Languages/update_default_lang') == 'true';
        if (file_exists($orig_file)) {
            require_once $orig_file;
        }

        // user translation
        $tpl  = new Jaws_Template('gadgets/Languages/templates/');
        $tpl->Load('FileTemplate.html');
        $tpl->SetBlock('template');
        $tpl->SetVariable('project', $module_name);
        $tpl->SetVariable('language', strtoupper($langTo));

        // orig translation
        $tpl2 = new Jaws_Template('gadgets/Languages/templates/');
        $tpl2->Load('FileTemplate.html');
        $tpl2->SetBlock('template');
        $tpl2->SetVariable('project', $module_name);
        $tpl2->SetVariable('language', strtoupper($langTo));

        // Meta
        foreach ($data['meta'] as $k => $v) {
            $v = str_replace('"', '\"', $v);
            // user translation
            $tpl->SetBlock('template/meta');
            $tpl->SetVariable('key', $k);
            $tpl->SetVariable('value', $v);
            $tpl->ParseBlock('template/meta');
            // orig translation
            $tpl2->SetBlock('template/meta');
            $tpl2->SetVariable('key', $k);
            $tpl2->SetVariable('value', $v);
            $tpl2->ParseBlock('template/meta');
        }

        // Strings
        $change_detected = false;
        foreach ($data['strings'] as $k => $v) {
            if ($v == '') {
                continue;
            } elseif ($v === EMPTY_STRING) {
                $v = '';
            }

            $orig_cons = '_' . strtoupper($langTo) . '_' . $k;
            $data_cons = '_' . strtoupper($langTo) . '_DATA_' . $k;
            $v = preg_replace("$\r\n|\n$", "\n", $v);
            $changed = !defined($orig_cons) || constant($orig_cons) !== $v;
            $v = str_replace(array('"', "\n"), array('\"', '\n'), $v);

            if ($changed) {
                $change_detected = true;
                $tpl->SetBlock('template/string');
                $tpl->SetVariable('key', $data_cons);
                $tpl->SetVariable('value', $v);
                $tpl->ParseBlock('template/string');
            }

            // orig translation
            $tpl2->SetBlock('template/string');
            $tpl2->SetVariable('key', $orig_cons);
            $tpl2->SetVariable('value', $v);
            $tpl2->ParseBlock('template/string');
        }

        $tpl->ParseBlock('template');
        $tpl2->ParseBlock('template');

        // update original translation
        if ($update_default_lang) {
            // update default language translation,
            // so we can delete customized language's file
            if (Jaws_Utils::file_put_contents($orig_file, $tpl2->Get())) {
                $change_detected = false;
            }
        }

        // Writable
        if(file_exists($data_file)) {
            $writeable = Jaws_Utils::is_writable($data_file);
        } else {
            Jaws_Utils::mkdir(dirname($data_file), 3);
            $writeable = Jaws_Utils::is_writable(dirname($data_file));
        }

        if (!$writeable) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LANGUAGES_NOT_PERMISSION'), RESPONSE_ERROR);
            return false;
        }

        if ($change_detected) {
            if (Jaws_Utils::file_put_contents($data_file, $tpl->Get())) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LANGUAGES_UPDATED', $module), RESPONSE_NOTICE);
                return true;
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('LANGUAGES_NOT_UPDATED', $module), RESPONSE_ERROR);
                return false;
            }
        } else {
            Jaws_Utils::Delete($data_file);
            $GLOBALS['app']->Session->PushLastResponse(_t('LANGUAGES_UPDATED', $module), RESPONSE_NOTICE);
            return true;
        }
    }

}