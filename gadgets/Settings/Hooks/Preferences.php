<?php
/**
 * Settings - Preferences hook
 *
 * @category    GadgetHook
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Hooks_Preferences extends Jaws_Gadget_Hook
{
    /**
     * Get user's preferences of this gadget
     *
     * @access  public
     * @return  array   Formatted array for using in Users Preferences action
     */
    function Execute()
    {
        $result = array();
        $languages = Jaws_Utils::GetLanguagesList();
        $objSettings = $this->gadget->model->loadAdmin('Settings');
        $objComponents = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $objComponents->GetGadgetsList(null, true, true, null, true);
        $gadgets = array_column(array_values($gadgets), 'title', 'name');
        array_unshift($gadgets, _t('GLOBAL_NOGADGET'));

        $result['admin_language'] = array(
            'title' => _t('SETTINGS_ADMIN_LANGUAGE'),
            'values' => $languages,
            'ltr' => true,
        );
        $result['site_language'] = array(
            'title' => _t('SETTINGS_DEFAULT_SITE_LANGUAGE'),
            'values' => $languages,
            'ltr' => true,
        );
        $result['calendar'] = array(
            'title' => _t('SETTINGS_CALENDAR'),
            'values' => $objSettings->GetCalendarList(),
        );
        $result['date_format'] = array(
            'title' => _t('SETTINGS_DATE_FORMAT'),
            'values' => $objSettings->GetDateFormatList(),
        );
        $result['main_gadget'] = array(
            'title' => _t('SETTINGS_MAIN_GADGET'),
            'values' => $gadgets,
        );
        $result['editor'] = array(
            'title' => _t('SETTINGS_EDITOR'),
            'values' => $objSettings->GetEditorList(),
        );
        $result['timezone'] = array(
            'title' => _t('GLOBAL_TIMEZONE'),
            'values' => $objSettings->GetTimeZonesList(),
            'ltr' => true,
        );

        return $result;
    }

}