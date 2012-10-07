<?php
/**
 * Preferences Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PreferencesAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Admin of Gadget
     *
     * @access  public
     * @return  string HTML content of administration
     */
    function Admin()
    {
        $this->CheckPermission('UpdateProperties');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Preferences/templates/');
        $tpl->Load('AdminPreferences.html');
        $tpl->SetBlock('preferences');

        $preferences =& Piwi::CreateWidget('VBox');
        $preferences->SetId('preferences');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('PREFERENCES_NAME'));
        $fieldset->SetDirection('vertical');

        $checks =& Piwi::CreateWidget('CheckButtons', 'display','vertical');
        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_theme') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_THEME'), 'theme', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_editor') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_EDITOR'), 'editor', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_language') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_LANGUAGE'), 'language', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_calendar_type') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_CALENDAR_TYPE'), 'calendar_type', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_calendar_language') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_CALENDAR_LANGUAGE'), 'calendar_language', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_date_format') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_DATE_FORMAT'), 'date_format', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_timezone') == 'true');
        $checks->AddOption(_t('PREFERENCES_DISPLAY_TIMEZONE'), 'timezone', null, $checked);

        $checked = ($GLOBALS['app']->Registry->Get('/config/cookie_precedence') == 'true');
        $checks->AddOption(_t('PREFERENCES_COOKIE_PRECEDENCE'), 'cookie', null, $checked);

        $submit =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_UPDATE', _t('GLOBAL_SETTINGS')), STOCK_SAVE);
        $submit->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $submit->AddEvent(ON_CLICK, 'updatePreferences();');

        $fieldset->Add($checks);
        $preferences->Add($fieldset);
        $preferences->Add($submit);
        $tpl->SetVariable('preferences_config', $preferences->Get());

        $tpl->ParseBlock('preferences');

        return $tpl->Get();
    }
}
