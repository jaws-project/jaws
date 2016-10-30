<?php
/**
 * Settings Core Gadget
 *
 * @category    Gadget
 * @package     Settings
 */
class Settings_Actions_Settings extends Jaws_Gadget_Action
{
    /**
     * Prepares a simple form to update site settings
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('BasicSettings');
        $this->AjaxMe('index.js');
        $response = $GLOBALS['app']->Session->PopResponse('Settings.Settings');

        // Load the template
        $tpl = $this->gadget->template->load('Settings.html');
        $tpl->SetBlock('settings');

        $this->SetTitle(_t('SETTINGS_TITLE'));
        $tpl->SetVariable('title', _t('SETTINGS_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('GLOBAL_UPDATE'));

        // site status
        $siteStatus = $this->gadget->registry->fetch('site_status');
        $tpl->SetVariable('lbl_site_status', _t('SETTINGS_SITE_STATUS'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('lbl_disabled', _t('GLOBAL_DISABLED'));
        if ($siteStatus == 'enabled') {
            $tpl->SetVariable('enabled_selected', 'selected');
        } else {
            $tpl->SetVariable('disabled_selected', 'selected');
        }

        // Site name
        $tpl->SetVariable('lbl_site_name', _t('SETTINGS_SITE_NAME'));
        $tpl->SetVariable('site_name', Jaws_XSS::defilter($this->gadget->registry->fetch('site_name')));

        // Site slogan
        $tpl->SetVariable('lbl_site_slogan', _t('SETTINGS_SITE_SLOGAN'));
        $tpl->SetVariable('site_slogan', Jaws_XSS::defilter($this->gadget->registry->fetch('site_slogan')));

        // site language
        $tpl->SetVariable('lbl_site_language', _t('SETTINGS_DEFAULT_SITE_LANGUAGE'));
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $tpl->SetBlock('settings/site_language');
            $tpl->SetVariable('value', $k);
            $tpl->SetVariable('title', $v);

            if ($k == $this->gadget->registry->fetch('site_language')) {
                $tpl->SetBlock('settings/site_language/selected');
                $tpl->ParseBlock('settings/site_language/selected');
            }
            $tpl->ParseBlock('settings/site_language');
        }

        // Main gadget
        $tpl->SetVariable('lbl_main_gadget', _t('SETTINGS_MAIN_GADGET'));
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $installedgadgets = $cmpModel->GetGadgetsList(null, true, true, true);
        array_unshift($installedgadgets, array('title' => _t('GLOBAL_NOGADGET')));
        foreach ($installedgadgets as $k => $v) {
            $tpl->SetBlock('settings/main_gadget');
            $tpl->SetVariable('value', $k);
            $tpl->SetVariable('title', $v['title']);

            if ($k == $this->gadget->registry->fetch('main_gadget')) {
                $tpl->SetBlock('settings/main_gadget/selected');
                $tpl->ParseBlock('settings/main_gadget/selected');
            }
            $tpl->ParseBlock('settings/main_gadget');
        }

        // Site email
        $tpl->SetVariable('lbl_site_email', _t('SETTINGS_SITE_EMAIL'));
        $tpl->SetVariable('site_email', $this->gadget->registry->fetch('site_email'));

        // Site comment
        $tpl->SetVariable('lbl_site_comment', _t('SETTINGS_SITE_COMMENT'));
        $tpl->SetVariable('site_comment', Jaws_XSS::defilter($this->gadget->registry->fetch('site_comment')));

        // Date Format
        $model = $this->gadget->model->load('Settings');
        $tpl->SetVariable('lbl_date_format', _t('SETTINGS_DATE_FORMAT'));
        $dtfmts = $model->GetDateFormatList();
        foreach ($dtfmts as $k => $v) {
            $tpl->SetBlock('settings/date_format');
            $tpl->SetVariable('value', $k);
            $tpl->SetVariable('title', $v);

            if ($k == $this->gadget->registry->fetch('date_format')) {
                $tpl->SetBlock('settings/date_format/selected');
                $tpl->ParseBlock('settings/date_format/selected');
            }
            $tpl->ParseBlock('settings/date_format');
        }

        // Calendar
        $tpl->SetVariable('lbl_calendar', _t('SETTINGS_CALENDAR'));
        $calendars = $model->GetCalendarList();
        foreach ($calendars as $calendar) {
            $tpl->SetBlock('settings/calendar');
            $tpl->SetVariable('value', $calendar);
            $tpl->SetVariable('title', $calendar);

            if ($calendar == $this->gadget->registry->fetch('calendar')) {
                $tpl->SetBlock('settings/calendar/selected');
                $tpl->ParseBlock('settings/calendar/selected');
            }
            $tpl->ParseBlock('settings/calendar');
        }

        // editor
        $tpl->SetVariable('lbl_editor', _t('SETTINGS_EDITOR'));
        $editors = $model->GetEditorList();
        foreach ($editors as $k => $v) {
            $tpl->SetBlock('settings/editor');
            $tpl->SetVariable('value', $k);
            $tpl->SetVariable('title', $v);

            if ($k == $this->gadget->registry->fetch('editor')) {
                $tpl->SetBlock('settings/editor/selected');
                $tpl->ParseBlock('settings/editor/selected');
            }
            $tpl->ParseBlock('settings/editor');
        }

        // Time Zones
        $tpl->SetVariable('lbl_timezone', _t('GLOBAL_TIMEZONE'));
        $timezones = $model->GetTimeZonesList();
        foreach ($timezones as $k => $v) {
            $tpl->SetBlock('settings/timezone');
            $tpl->SetVariable('value', $k);
            $tpl->SetVariable('title', $v);

            if ($k == $this->gadget->registry->fetch('timezone')) {
                $tpl->SetBlock('settings/timezone/selected');
                $tpl->ParseBlock('settings/timezone/selected');
            }
            $tpl->ParseBlock('settings/timezone');
        }

        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

    /**
     * Updates site settings
     *
     * @access  public
     * @return  void
     */
    function UpdateSettings()
    {
        $this->gadget->CheckPermission('BasicSettings');
        $post = jaws()->request->fetch(
            array(
                'site_status', 'site_name', 'site_slogan', 'site_language', 'main_gadget',
                'site_email', 'site_comment', 'date_format', 'calendar', 'editor', 'timezone'
            ),
            'post'
        );

        $uModel = $this->gadget->model->load('Settings');
        $result = $uModel->SaveSettings($post);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Settings.Settings',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('SETTINGS_SAVED'),
                'Settings.Settings'
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('Settings'), 'Settings.Settings');
    }
}