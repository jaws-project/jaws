<?php
/**
 * Languages Core Gadget
 *
 * @category   GadgetModel
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Languages_Model_Admin_Languages extends Jaws_Gadget_Model
{
    /**
     * Special empty string
     *
     * @var     string
     * @access  private
     */
    var $_EMPTY_STRING = '-EMPTY-';

    /**
     * Add/Edit language's profile(local/international name, ...)
     *
     * @access  public
     * @param   string  $lang_str   Language code and name
     * @return  bool    True on Success or False on failure
     */
    function SaveLanguage($lang_str)
    {
        if ($lang_str == $lang_str) {
            $lang_code = substr($lang_str, 0, strpos($lang_str, ';'));
            if (preg_match("/^([a-z]{2})$|^([a-z]{2}[-][a-z]{2})$/", $lang_code)) {
                $lang_name = substr($lang_str, strpos($lang_str, ';')+1);
                if (!empty($lang_name) || trim($lang_name) == $lang_name) {
                    $use_data_lang = $this->gadget->registry->fetch('use_data_lang') == 'true';
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
            case JAWS_COMPONENT_GADGET:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "gadgets/$module/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/gadgets/$module.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/gadgets/$module.ini";
                $from_file = JAWS_PATH . "gadgets/$module/Resources/translates.ini";
                break;

            case JAWS_COMPONENT_PLUGIN:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "plugins/$module/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/plugins/$module.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/plugins/$module.ini";
                $from_file = JAWS_PATH . "plugins/$module/Resources/translates.ini";
                $module = 'Plugins_' . $module;
                break;

            case JAWS_COMPONENT_INSTALL:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "install/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Install.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Install.ini";
                $from_file = JAWS_PATH . "install/Resources/translates.ini";
                break;

            case JAWS_COMPONENT_UPGRADE:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "upgrade/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Upgrade.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Upgrade.ini";
                $from_file = JAWS_PATH . "upgrade/Resources/translates.ini";
                break;

            default:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "include/Jaws/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Global.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Global.ini";
                $from_file = JAWS_PATH . "include/Jaws/Resources/translates.ini";
        }

        if (!file_exists($from_file)) {
            return false;
        }

        $strings = array();
        if (file_exists($orig_file)) {
            $content = file_get_contents($orig_file);
            $strings = parse_ini_file($orig_file, false, INI_SCANNER_RAW);
        }

        // load "from language" file
        if ($from_file == $orig_file) {
            $fromstrings = $strings;
        } else {
            $fromstrings = parse_ini_file($from_file, false, INI_SCANNER_RAW);
        }

        if (file_exists($data_file)) {
            $strings = array_merge($strings, parse_ini_file($data_file, false, INI_SCANNER_RAW));
        }

        $data = array();
        $data['lang_direction'] = 'ltr';
        if ($type != 0) {
            if ($langTo == 'en') {
                $global_file = JAWS_PATH . "include/Jaws/Resources/translates.ini";
            } else {
                $global_file = JAWS_PATH . "languages/$langTo/Global.ini";
            }
            $globals = @parse_ini_file($global_file, false, INI_SCANNER_RAW);
            $data['lang_direction'] = 
                isset($globals['GLOBAL_LANG_DIRECTION'])? $globals['GLOBAL_LANG_DIRECTION'] : 'ltr';
        } else {
            $data['lang_direction'] = 
                isset($strings['GLOBAL_LANG_DIRECTION'])? $strings['GLOBAL_LANG_DIRECTION'] : 'ltr';
        }

        // Metadata
        preg_match('/"Last-Translator:(.*)"/', isset($content)? $content : '', $res);
        $data['meta']['Last-Translator'] = !empty($res)? trim($res[1]) : '';

        // Strings
        foreach ($fromstrings as $k => $v) {
            if (strpos($k, strtoupper("{$module}_")) != 0) {
                continue;
            }

            $data['strings'][$k]['en'] = $v;
            $data['strings'][$k][$langTo] =
                isset($strings[$k])? (($strings[$k] === '')? $this->_EMPTY_STRING : $strings[$k]) : '';
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
            case JAWS_COMPONENT_GADGET:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "gadgets/$module/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/gadgets/$module.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/gadgets/$module.ini";
                break;

            case JAWS_COMPONENT_PLUGIN:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "plugins/$module/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/plugins/$module.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/plugins/$module.ini";
                $from_file = JAWS_PATH . "plugins/$module/Resources/translates.ini";
                $module_name = 'Plugins_' . $module;
                break;

            case JAWS_COMPONENT_INSTALL:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "install/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Install.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Install.ini";
                break;

            case JAWS_COMPONENT_UPGRADE:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "upgrade/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Upgrade.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Upgrade.ini";
                break;

            default:
                if ($langTo == 'en') {
                    $orig_file = JAWS_PATH . "include/Jaws/Resources/translates.ini";
                } else {
                    $orig_file = JAWS_PATH . "languages/$langTo/Global.ini";
                }
                $data_file = JAWS_DATA . "languages/$langTo/Global.ini";
        }

        $update_default_lang = $this->gadget->registry->fetch('update_default_lang') == 'true';
        $strings = array();
        if (file_exists($orig_file)) {
            $strings = parse_ini_file($orig_file, false, INI_SCANNER_RAW);
        }

        // user translation
        $tpl = $this->gadget->template->loadAdmin('FileTemplate.html');
        $tpl->SetBlock('template');
        $tpl->SetVariable('project', $module_name);
        $tpl->SetVariable('language', strtoupper($langTo));

        // orig translation
        $tpl2 = $this->gadget->template->loadAdmin('FileTemplate.html');
        $tpl2->SetBlock('template');
        $tpl2->SetVariable('project', $module_name);
        $tpl2->SetVariable('language', strtoupper($langTo));

        // Meta
        foreach ($data['meta'] as $k => $v) {
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
            } elseif ($v === $this->_EMPTY_STRING) {
                $v = '';
            }

            $v = preg_replace("$\r\n|\n$", '\n', $v);
            $changed = !isset($strings[$k]) || $strings[$k] !== $v;

            if ($changed) {
                $change_detected = true;
                $tpl->SetBlock('template/string');
                $tpl->SetVariable('key', $k);
                $tpl->SetVariable('value', $v);
                $tpl->ParseBlock('template/string');
            }

            // orig translation
            $tpl2->SetBlock('template/string');
            $tpl2->SetVariable('key', $k);
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