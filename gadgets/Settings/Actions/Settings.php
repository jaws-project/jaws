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
        $this->SetTitle(_t('SETTINGS_TITLE'));

        $assigns = array(
            'response' => $this->gadget->session->pop('Settings'),
            'enabled' => $this->gadget->registry->fetch('site_status'),
            'site_name' => Jaws_XSS::defilter($this->gadget->registry->fetch('site_name')),
            'site_slogan' => Jaws_XSS::defilter($this->gadget->registry->fetch('site_slogan')),
            'site_email' => $this->gadget->registry->fetch('site_email'),
            'site_comment' => Jaws_XSS::defilter($this->gadget->registry->fetch('site_comment')),
        );
        // available languages
        $assigns['languages'] = Jaws_Utils::GetLanguagesList();
        $assigns['selected_language'] = $this->gadget->registry->fetch('site_language');
        // installed gadgets
        $assigns['gadgets'] = Jaws_Gadget::getInstance('Components')
            ->model->load('Gadgets')
            ->GetGadgetsList(null, true, true, true);
        $assigns['gadgets'] = array('-' => array('title' => _t('GLOBAL_NOGADGET'))) + $assigns['gadgets'];
        $assigns['selected_gadget'] = $this->gadget->registry->fetch('main_gadget');
        // date formats
        $assigns['dateFormats'] = $this->gadget->model->load('Settings')->GetDateFormatList();
        $assigns['selected_dateFormat'] = $this->gadget->registry->fetch('date_format');
        // calendar
        $assigns['calendars'] = $this->gadget->model->load('Settings')->GetCalendarList();
        $assigns['selected_calendar'] = $this->gadget->registry->fetch('calendar');
        // editors
        $assigns['editors'] = $this->gadget->model->load('Settings')->GetEditorList();
        $assigns['selected_editor'] = $this->gadget->registry->fetch('editor');
        // timezones
        $assigns['timezones'] = $this->gadget->model->load('Settings')->GetTimeZonesList();
        $assigns['selected_timezone'] = $this->gadget->registry->fetch('timezone');

        return $this->gadget->template->xLoad('Settings.html')->render($assigns);
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
        $post = $this->gadget->request->fetch(
            array(
                'site_status', 'site_name', 'site_slogan', 'site_language', 'main_gadget',
                'site_email', 'site_comment', 'date_format', 'calendar', 'editor', 'timezone'
            ),
            'post'
        );

        $uModel = $this->gadget->model->load('Settings');
        $result = $uModel->SaveSettings($post);
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $result->GetMessage(),
                RESPONSE_ERROR,
                'Settings'
            );
        } else {
            $this->gadget->session->push(
                _t('SETTINGS_SAVED'),
                RESPONSE_NOTICE,
                'Settings'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Settings'), 'Settings');
    }

    /**
     * Returns health status text
     *
     * @access  public
     * @return  string
     */
    function HealthStatus()
    {
        if (defined('JAWS_HEALTH_STATUS')) {
            $status = JAWS_HEALTH_STATUS;
        }  else {
            $status = $this->gadget->registry->fetch('health_status');
        }

        http_response_code(200);
        header('Content-Length: ' . strlen($status));
        return $status;
    }

}