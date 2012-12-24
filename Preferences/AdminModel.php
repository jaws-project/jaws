<?php
require_once JAWS_PATH . 'gadgets/Preferences/Model.php';
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
class Preferences_AdminModel extends Preferences_Model
{
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