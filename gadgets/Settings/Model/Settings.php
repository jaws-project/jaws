<?php
/**
 * Settings Core Gadget
 *
 * @category   GadgetModel
 * @package    Settings
 */
class Settings_Model_Settings extends Jaws_Gadget_Model
{
    /**
     * Gets the available calendars
     *
     * @access   public
     * @return   mixed  Array of available calendars or flase on failure
     */
    function GetCalendarList()
    {
        $calendars = array();
        $path = JAWS_PATH . 'include/Jaws/Date';
        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir as $calendar) {
                if (stristr($calendar, '.php')) {
                    $calendar = str_replace('.php', '', $calendar);
                    $calendars[$calendar] = $calendar;
                }
            }

            return $calendars;
        }

        return false;
    }

    /**
     * Gets available editors
     *
     * @access   public
     * @return   array  List of available editors
     */
    function GetEditorList()
    {
        $editors = array();
        $editors['TextArea'] = _t('SETTINGS_EDITOR_CLASSIC');
        $editors['TinyMCE']  = _t('SETTINGS_EDITOR_TINYMCE');
        $editors['CKEditor'] = _t('SETTINGS_EDITOR_CKEDITOR');
        return $editors;
    }

    /**
     * Gets available date formats
     *
     * @access   public
     * @return   array  List of available date formats
     */
    function GetDateFormatList()
    {
        $dt_formats = array();
        $time = time();
        $date = Jaws_Date::getInstance();
        $dt_formats['MN j, g:i a']     = $date->Format($time, 'MN j, g:i a');
        $dt_formats['j.m.y']           = $date->Format($time, 'j.m.y');
        $dt_formats['j MN, g:i a']     = $date->Format($time, 'j MN, g:i a');
        $dt_formats['y.m.d, g:i a']    = $date->Format($time, 'y.m.d, g:i a');
        $dt_formats['d MN Y']          = $date->Format($time, 'd MN Y');
        $dt_formats['DN d MN Y']       = $date->Format($time, 'DN d MN Y');
        $dt_formats['DN d MN Y g:i a'] = $date->Format($time, 'DN d MN Y g:i a');
        $dt_formats['j MN y']          = $date->Format($time, 'j MN y');
        $dt_formats['j m Y - H:i']     = $date->Format($time, 'j m Y - H:i');
        $dt_formats['AGO']             = $date->Format($time, 'since');

        return $dt_formats;
    }

    /**
     * Gets list of timezones
     *
     * @access   public
     * @return   array  List of timezones
     */
    function GetTimeZonesList()
    {
        $timezones = timezone_identifiers_list();
        $timezones = array_combine($timezones, $timezones);
        return $timezones;
    }

    /**
     * Updates basic settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'site_status',      => //Site status
     *                   'site_name',        => //Site name
     *                   'site_slogan',      => //Site slogan
     *                   'site_language',    => //Default site language
     *                   'main_gadget',      => //Main gadget
     *                   'site_comment',     => //Site commnet
     *                   'date_format',         //Date format
     *                   'calendar',            //Date Calendar
     *                   'show_viewsite',       //show the view site on CP?
     *                   'editor',              //Editor to use
     *                   'timezone',            //Timezone
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function SaveSettings($settings)
    {
        $basicKeys = array('site_name', 'site_slogan', 'site_language',
            'main_gadget', 'site_email', 'site_comment','date_format', 'calendar',
            'editor', 'timezone'
        );
        if ($this->gadget->GetPermission('ManageSiteStatus')) {
            $basicKeys[] = 'site_status';
        }

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $basicKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }

            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }
}