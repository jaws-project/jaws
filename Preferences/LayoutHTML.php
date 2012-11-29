<?php
/**
 * Preferences Gadget (layout client side)
 *
 * @category   GadgetLayout
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PreferencesLayoutHTML
{
    /**
     * Display Action
     *
     * @access      public
     * @return      object   The template of the Preferences gadget
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Preferences/templates/');
        $tpl->Load('Preferences.html');
        $tpl->SetBlock('preferences');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('PREFERENCES_ACTION_TITLE'));

        $user_preferences        = $GLOBALS['app']->Session->GetCookie('preferences');

        $user_theme              = $user_preferences['theme'];
        $user_editor             = $user_preferences['editor'];
        $user_language           = $user_preferences['language'];
        $user_calendar_type      = $user_preferences['calendar_type'];
        $user_calendar_language  = $user_preferences['calendar_language'];
        $user_date_format        = $user_preferences['date_format'];
        $user_timezone           = $user_preferences['timezone'];

        $displayTheme            = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_theme') == 'true');
        $displayeEditor          = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_editor') == 'true');
        $displayLanguage         = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_language') == 'true');
        $displayCalendarType     = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_calendar_type') == 'true');
        $displayCalendarLanguage = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_calendar_language') == 'true');
        $displayDateFormat       = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_date_format') == 'true');
        $displayTimeZone         = ($GLOBALS['app']->Registry->Get('/gadgets/Preferences/display_timezone') == 'true');

        if ($displayTheme || $displayeEditor || $displayLanguage || $displayCalendarType ||
            $displayCalendarLanguage || $displayDateFormat || $displayTimeZone) {
            //Add the submit button..
            $submit =& Piwi::CreateWidget('Button', 'save_preferences', _t('GLOBAL_SAVE'));
            $submit->SetSubmit();
            $tpl->SetVariable('submit_button', $submit->Get());

            $reset =& Piwi::CreateWidget('Button', 'reset_preferences', _t('GLOBAL_RESET'));
            $reset->SetReset();
            $tpl->SetVariable('reset_button', $reset->Get());
        }

        $settingsModel = $GLOBALS['app']->LoadGadget('Settings', 'AdminModel');
        //get a list of themes
        if ($displayTheme) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_THEME'));
            $pTheme =& Piwi::CreateWidget('ComboGroup', 'theme');
            $pTheme->setStyle('direction: ltr;');
            $pTheme->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
            $pTheme->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
            $pTheme->AddOption('local', _t('PREFERENCES_NOT_DEFINED'), false);
            $themes = Jaws_Utils::GetThemesList();
            foreach ($themes as $theme => $tInfo) {
                $pTheme->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
            }

            if (!empty($user_theme) && array_key_exists($user_theme, $themes)) {
                $pTheme->SetDefault($user_theme);
            } else {
                $pTheme->SetDefault(false);
            }

            $tpl->SetVariable('value', $pTheme->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of editors
        if ($displayeEditor) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_EDITOR'));
            $editorlist = $settingsModel->GetEditorList();
            $editors =& Piwi::CreateWidget('Combo', 'editor');
            $editors->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($editorlist as $editor => $key_editor) {
                $editors->AddOption($key_editor, $editor);
            }

            if (!empty($user_editor) && array_key_exists($user_editor, $editorlist)) {
                $editors->SetDefault($user_editor);
            } else {
                $editors->SetDefault(false);
            }

            $tpl->SetVariable('value', $editors->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of languages
        if ($displayLanguage) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_LANGUAGE'));
            $languagelist = Jaws_Utils::GetLanguagesList();
            $languages =& Piwi::CreateWidget('Combo', 'language');
            $languages->setStyle('direction: ltr;');
            $languages->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($languagelist as $language => $key_lang) {
                $languages->AddOption($key_lang, $language);
            }

            if (!empty($user_language) && array_key_exists($user_language, $languagelist)) {
                $languages->SetDefault($user_language);
            } else {
                $languages->SetDefault(false);
            }

            $tpl->SetVariable('value', $languages->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of calendar
        if ($displayCalendarType) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_CALENDAR_TYPE'));
            $calendarlist = $settingsModel->GetCalendarList();
            $calendar_types =& Piwi::CreateWidget('Combo', 'calendar_type');
            $calendar_types->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($calendarlist as $calendar) {
                $calendar_types->AddOption($calendar, $calendar);
            }

            if (!empty($user_calendar_type) && in_array($user_calendar_type, $calendarlist)) {
                $calendar_types->SetDefault($user_calendar_type);
            } else {
                $calendar_types->SetDefault(false);
            }

            $tpl->SetVariable('value', $calendar_types->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of languages for select calendar language
        if ($displayCalendarLanguage) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_CALENDAR_LANGUAGE'));
            if (!$displayLanguage) {
                $languagelist = Jaws_Utils::GetLanguagesList();
            } else {
                // not require load languages list because befor loaded
            }
            $calendar_languages =& Piwi::CreateWidget('Combo', 'calendar_language');
            $calendar_languages->setStyle('direction: ltr;');
            $calendar_languages->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($languagelist as $language => $key_lang) {
                $calendar_languages->AddOption($key_lang, $language);
            }

            if (!empty($user_calendar_language) && array_key_exists($user_calendar_language, $languagelist)) {
                $calendar_languages->SetDefault($user_calendar_language);
            } else {
                $calendar_languages->SetDefault(false);
            }

            $tpl->SetVariable('value', $calendar_languages->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of date format
        if ($displayDateFormat) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('PREFERENCES_DATE_FORMAT'));
            $dtfmtlist = $settingsModel->GetDateFormatList();
            $date_formats =& Piwi::CreateWidget('Combo', 'date_format');
            $date_formats->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($dtfmtlist as $dtfmt => $key_dtfmt) {
                $date_formats->AddOption($key_dtfmt, $dtfmt);
            }

            if (!empty($user_date_format) && array_key_exists($user_date_format, $dtfmtlist)) {
                $date_formats->SetDefault($user_date_format);
            } else {
                $date_formats->SetDefault(false);
            }

            $tpl->SetVariable('value', $date_formats->Get());
            $tpl->ParseBlock('preferences/option');
        }

        //get a list of timezone
        if ($displayTimeZone) {
            $tpl->SetBlock('preferences/option');
            $tpl->SetVariable('label', _t('GLOBAL_TIMEZONE'));
            $timezonelist = $settingsModel->GetTimeZonesList();
            $timezone =& Piwi::CreateWidget('Combo', 'timezone');
            $timezone->setStyle('direction: ltr;');
            $timezone->AddOption(_t('PREFERENCES_NOT_DEFINED'), false);
            foreach ($timezonelist as $tz => $key_tz) {
                $timezone->AddOption($key_tz, $tz);
            }

            if (array_key_exists($user_timezone, $timezonelist)) {
                $timezone->SetDefault($user_timezone);
            } else {
                $timezone->SetDefault(false);
            }

            $tpl->SetVariable('value', $timezone->Get());
            $tpl->ParseBlock('preferences/option');
        }

        $tpl->ParseBlock('preferences');
        return $tpl->Get();
    }
}