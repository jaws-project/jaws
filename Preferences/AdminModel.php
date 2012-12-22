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
     * @return  bool    true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $this->gadget->AddRegistry('display_theme',             'true');
        $this->gadget->AddRegistry('display_editor',            'true');
        $this->gadget->AddRegistry('display_language',          'true');
        $this->gadget->AddRegistry('display_calendar_type',     'true');
        $this->gadget->AddRegistry('display_calendar_language', 'true');
        $this->gadget->AddRegistry('display_date_format',       'true');
        $this->gadget->AddRegistry('display_timezone',          'true');

        //enable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'true', 'Settings');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        // registry keys
        $this->gadget->DelRegistry('display_theme');
        $this->gadget->DelRegistry('display_editor');
        $this->gadget->DelRegistry('display_language');
        $this->gadget->DelRegistry('display_calendar_type');
        $this->gadget->DelRegistry('display_calendar_language');
        $this->gadget->DelRegistry('display_date_format');
        $this->gadget->DelRegistry('display_timezone');

        //disable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'false', 'Settings');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        // ACL keys
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Preferences/UpdateProperties',   'true');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Preferences/ChangeSettings');

        // Registry keys.
        $this->gadget->AddRegistry('display_editor',            'true');
        $this->gadget->AddRegistry('display_calendar_type',     'true');
        $this->gadget->AddRegistry('display_calendar_language', 'true');
        $this->gadget->AddRegistry('display_date_format',       'true');
        $this->gadget->AddRegistry('display_timezone',          'true');

        //enable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'true', 'Settings');

        return true;
    }

    /**
     * Update preferences
     *
     * @access  public
     * @param   array   $preferences_config
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences($preferences_config)
    {
        $prefKeys = array('display_theme', 'display_editor', 'display_language',
                          'display_calendar_type', 'display_calendar_language',
                          'display_date_format', 'display_timezone');

        foreach ($preferences_config as $Key => $Value) {
            if (in_array($Key, $prefKeys)) {
                $res = $this->gadget->SetRegistry($Key, (empty($Value)? 'false' : 'true'));
                if (!$res) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('PREFERENCES_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                    return new Jaws_Error(_t('PREFERENCES_ERROR_PROPERTIES_NOT_UPDATED'), _t('PREFERENCES_NAME'));
                }
            }
        }

        $this->gadget->SetRegistry(
            'cookie_precedence',
            (empty($preferences_config['cookie_precedence'])? 'false' : 'true'),
            'Settings'
        );
        $GLOBALS['app']->Session->PushLastResponse(_t('PREFERENCES_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}