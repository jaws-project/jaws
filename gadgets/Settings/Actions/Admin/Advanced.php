<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_Advanced extends Settings_Actions_Admin_Default
{
    /**
     * Displays advanced settings
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AdvancedSettings()
    {
        $this->gadget->CheckPermission('AdvancedSettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Advanced'));
        $tpl->SetVariable('legend', _t('SETTINGS_ADVANCED_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript: submitAdvancedForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        $model = $this->gadget->model->loadAdmin('Settings');
        // Date Format
        $date_format =& Piwi::CreateWidget('Combo', 'date_format');
        $date_format->setID('date_format');
        $dtfmts = $model->GetDateFormatList();
        foreach ($dtfmts as $k => $v) {
            $date_format->AddOption($v, $k);
        }
        $date_format->SetDefault($this->gadget->registry->fetch('date_format'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'date_format');
        $tpl->SetVariable('label', _t('SETTINGS_DATE_FORMAT'));
        $tpl->SetVariable('field', $date_format->Get());
        $tpl->ParseBlock('settings/item');

        // Calendar
        $date_calendar =& Piwi::CreateWidget('Combo', 'calendar');
        $date_calendar->setID('calendar');
        $calendars = $model->GetCalendarList();
        foreach ($calendars as $calendar) {
            $date_calendar->AddOption($calendar, $calendar);
        }
        $current_cal = $this->gadget->registry->fetch('calendar');
        if (Jaws_Error::isError($current_cal)) {
            $date_calendar->SetDefault('Gregorian');
        } else {
            $date_calendar->SetDefault($current_cal);
        }
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'calendar');
        $tpl->SetVariable('label', _t('SETTINGS_CALENDAR'));
        $tpl->SetVariable('field', $date_calendar->Get());
        $tpl->ParseBlock('settings/item');

        // Use gravatar? or local images?
        $use_gravatar = $this->gadget->registry->fetch('use_gravatar');
        $gravatar =& Piwi::CreateWidget('Combo', 'use_gravatar');
        $gravatar->setID('use_gravatar');
        $gravatar->AddOption(_t('GLOBAL_YES'), 'yes');
        $gravatar->AddOption(_t('GLOBAL_NO'), 'no');
        $gravatar->SetDefault($use_gravatar);
        $gravatar->AddEvent(ON_CHANGE, 'javascript: toggleGR();');
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'use_gravatar');
        $tpl->SetVariable('label', _t('SETTINGS_USE_GRAVATAR'));
        $tpl->SetVariable('field', $gravatar->Get());
        $tpl->ParseBlock('settings/item');

        // Gravatar rating
        $gravatar =& Piwi::CreateWidget('Combo', 'gravatar_rating');
        $gravatar->setID('gravatar_rating');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_G'), 'G');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_PG'), 'PG');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_R'), 'R');
        $gravatar->AddOption(_t('SETTINGS_GRAVATAR_X'), 'X');
        $gravatar->SetDefault($this->gadget->registry->fetch('gravatar_rating'));
        $gravatar->SetEnabled($use_gravatar == 'yes');
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'gravatar_rating');
        $tpl->SetVariable('label', '<a href="http://www.gravatar.com/rating.php" rel="external" target="_blank">' . _t('SETTINGS_GRAVATAR') . '</a>');
        $tpl->SetVariable('field', $gravatar->Get());
        $tpl->ParseBlock('settings/item');

        // show view site icon on CP
        $viewSite =& Piwi::CreateWidget('Combo', 'show_viewsite');
        $viewSite->setID('show_viewsite');
        $viewSite->AddOption(_t('GLOBAL_YES'), 'true');
        $viewSite->AddOption(_t('GLOBAL_NO'), 'false');
        $viewSite->SetDefault($this->gadget->registry->fetch('show_viewsite'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'show_viewsite');
        $tpl->SetVariable('label', _t('SETTINGS_SHOW_VIEWSITE'));
        $tpl->SetVariable('field', $viewSite->Get());
        $tpl->ParseBlock('settings/item');

        // default title
        $defaultTitle =& Piwi::CreateWidget('Combo', 'site_title_separator');
        $defaultTitle->setID('site_title_separator');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_SLASH'), '/');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_PIPE'), '|');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_DASH'), '-');
        $defaultTitle->AddOption(_t('SETTINGS_TITLE_SEPARATOR_DOUBLECOLON'), '::');
        $defaultTitle->SetDefault($this->gadget->registry->fetch('site_title_separator'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'site_title_separator');
        $tpl->SetVariable('label', _t('SETTINGS_TITLE_SEPARATOR'));
        $tpl->SetVariable('field', $defaultTitle->Get());
        $tpl->ParseBlock('settings/item');

        // editor
        $editorCombo =& Piwi::CreateWidget('Combo', 'editor');
        $editorCombo->setID('editor');
        $editors = $model->GetEditorList();
        foreach ($editors as $k => $v) {
            $editorCombo->AddOption($v, $k);
        }
        $editorCombo->SetDefault($this->gadget->registry->fetch('editor'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'editor');
        $tpl->SetVariable('label', _t('SETTINGS_EDITOR'));
        $tpl->SetVariable('field', $editorCombo->Get());
        $tpl->ParseBlock('settings/item');

        //Time Zones
        $timezone =& Piwi::CreateWidget('Combo', 'timezone');
        $timezone->setID('timezone');
        $timezones = $model->GetTimeZonesList();
        foreach($timezones as $k => $v) {
            $timezone->AddOption($v, $k);
        }
        $timezone->SetStyle('direction:ltr;');
        $timezone->SetDefault($this->gadget->registry->fetch('timezone'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'timezone');
        $tpl->SetVariable('label', _t('GLOBAL_TIMEZONE'));
        $tpl->SetVariable('field', $timezone->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');

        return $tpl->Get();
    }
}