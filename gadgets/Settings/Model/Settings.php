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
        $path = ROOT_JAWS_PATH . 'include/Jaws/Date';
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
        $editors['TextArea'] = $this::t('EDITOR_CLASSIC');
        $editors['TinyMCE']  = $this::t('EDITOR_TINYMCE');
        $editors['CKEditor'] = $this::t('EDITOR_CKEDITOR');
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
        $dt_formats['MMMM d, h:mm aa']  = $date->Format($time, 'MMMM d, h:mm aa');
        $dt_formats['dd.MM.yy']         = $date->Format($time, 'dd.MM.yy');
        $dt_formats['d MMMM, h:mm aa']  = $date->Format($time, 'd MMMM, h:mm aa');
        $dt_formats['yy.MM.d, h:mm aa'] = $date->Format($time, 'yy.MM.d, h:mm aa');
        $dt_formats['dd MMMM yyyy']     = $date->Format($time, 'dd MMMM yyyy');
        $dt_formats['EEEE d MMMM yyyy'] = $date->Format($time, 'EEEE d MMMM yyyy');
        $dt_formats['EEEE d MMMM yyyy h:mm aa'] = $date->Format($time, 'EEEE d MMMM yyyy h:mm aa');
        $dt_formats['d MMMM yy'] = $date->Format($time, 'd MMMM yy');
        $dt_formats['d MM yyyy - H:mm'] = $date->Format($time, 'd MM yyyy - H:mm');
        $dt_formats['AGO'] = $date->Format($time, 'since');

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