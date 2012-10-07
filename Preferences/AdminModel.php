<?php
/**
 * Preferences Gadget Model
 *
 * @category   GadgetModel
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Preferences/Model.php';

class PreferencesAdminModel extends PreferencesModel
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  boolean true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_theme',             'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_editor',            'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_language',          'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_calendar_type',     'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_calendar_language', 'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_date_format',       'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_timezone',          'true');

        //enable cookie precedence
        $GLOBALS['app']->Registry->Set('/config/cookie_precedence', 'true');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        // registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_theme');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_editor');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_language');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_calendar_type');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_calendar_language');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_date_format');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Preferences/display_timezone');

        //disable cookie precedence
        $GLOBALS['app']->Registry->Set('/config/cookie_precedence', 'false');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        // ACL keys
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Preferences/UpdateProperties',   'true');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Preferences/ChangeSettings');

        // Registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_editor',            'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_calendar_type',     'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_calendar_language', 'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_date_format',       'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Preferences/display_timezone',          'true');

        //enable cookie precedence
        $GLOBALS['app']->Registry->Set('/config/cookie_precedence', 'true');

        return true;
    }

    /**
     * Update preferences
     *
     * @access  public
     * @param   array   $preferences_config
     * @return  array   Response (notice or error)
     */
    function UpdatePreferences($preferences_config)
    {
        $prefKeys = array('display_theme', 'display_editor', 'display_language',
                          'display_calendar_type', 'display_calendar_language',
                          'display_date_format', 'display_timezone');

        foreach ($preferences_config as $Key => $Value) {
            if (in_array($Key, $prefKeys)) {
                $res = $GLOBALS['app']->Registry->Set("/gadgets/Preferences/$Key", (empty($Value)? 'false' : 'true'));
                if (!$res) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('PREFERENCES_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                    return new Jaws_Error(_t('PREFERENCES_ERROR_PROPERTIES_NOT_UPDATED'), _t('PREFERENCES_NAME'));
                }
            }
        }

        $GLOBALS['app']->Registry->Set('/config/cookie_precedence', (empty($preferences_config['cookie_precedence'])? 'false' : 'true'));
        $GLOBALS['app']->Registry->Commit('core');

        $GLOBALS['app']->Registry->Commit('Preferences');
        $GLOBALS['app']->Session->PushLastResponse(_t('PREFERENCES_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}