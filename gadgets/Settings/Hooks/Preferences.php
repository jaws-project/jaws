<?php
/**
 * Settings - Preferences hook
 *
 * @category    GadgetHook
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
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
        $gadgets = array('-' => Jaws::t('NOGADGET')) + $gadgets;

        $result['admin_language'] = array(
            'type'  => 'select',
            'title' => $this::t('ADMIN_LANGUAGE'),
            'values' => $languages,
            'ltr' => true,
        );
        $result['site_language'] = array(
            'type'  => 'select',
            'title' => $this::t('DEFAULT_SITE_LANGUAGE'),
            'values' => $languages,
            'ltr' => true,
        );
        $result['calendar'] = array(
            'type'  => 'select',
            'title' => $this::t('CALENDAR'),
            'values' => $objSettings->GetCalendarList(),
        );
        $result['date_format'] = array(
            'type'  => 'select',
            'title' => $this::t('DATE_FORMAT'),
            'values' => $objSettings->GetDateFormatList(),
        );
        $result['main_gadget'] = array(
            'type'  => 'select',
            'title' => $this::t('MAIN_GADGET'),
            'values' => $gadgets,
        );
        $result['editor'] = array(
            'type'  => 'select',
            'title' => $this::t('EDITOR'),
            'values' => $objSettings->GetEditorList(),
        );
        $result['timezone'] = array(
            'type'  => 'select',
            'title' => Jaws::t('TIMEZONE'),
            'values' => $objSettings->GetTimeZonesList(),
            'ltr' => true,
        );

        return $result;
    }

}