<?php
/**
 * Settings - Preferences hook
 *
 * @category    GadgetHook
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
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
        $themes = Jaws_Utils::GetThemesList();
        $themes = array_column(array_values($themes), 'title', 'name');
        $objSettings = $this->gadget->loadAdminModel('Settings');
        $objComponents = Jaws_Gadget::getInstance('Components')->loadModel('Gadgets');
        $gadgets = $objComponents->GetGadgetsList(null, true, true, null, true);
        $gadgets = array_column(array_values($gadgets), 'title', 'name');
        array_unshift($gadgets, _t('GLOBAL_NOGADGET'));

        $result['admin_language']['values'] = $languages;
        $result['site_language']['values'] = $languages;
        $result['calendar']['values'] = $objSettings->GetCalendarList();
        $result['date_format']['values'] = $objSettings->GetDateFormatList();
        $result['theme']['values'] = $themes;
        $result['main_gadget']['values'] = $gadgets;
        $result['editor']['values'] = $objSettings->GetEditorList();
        $result['timezone']['values'] = $objSettings->GetTimeZonesList();
        return $result;
    }

}