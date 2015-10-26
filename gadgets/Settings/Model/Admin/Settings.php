<?php
/**
 * Settings Core Gadget
 *
 * @category   GadgetModel
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Model_Admin_Settings extends Jaws_Gadget_Model
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
     *                   'admin_language',   => //Admin area language
     *                   'main_gadget',      => //Main gadget
     *                   'site_comment',     => //Site commnet
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function SaveBasicSettings($settings)
    {
        $basicKeys = array('site_status', 'site_name', 'site_slogan', 'site_language',
            'admin_language', 'main_gadget', 'site_email', 'site_comment');

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

    /**
     * Updates advanced settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'date_format',         //Date format
     *                   'calendar',            //Date Calendar
     *                   'use_gravatar',        //Use gravatar service?
     *                   'gravatar_rating',     //Gravatar rating
     *                   'show_viewsite',       //show the view site on CP?
     *                   'site_title_separator',//Separator used when user uses page_title
     *                   'editor',              //Editor to use
     *                   'timezone',            //Timezone
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function SaveAdvancedSettings($settings)
    {
        $advancedKeys = array(
            'date_format', 'calendar',
            'use_gravatar', 'gravatar_rating', 'show_viewsite',
            'site_title_separator', 'editor', 'timezone'
        );

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $advancedKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }
            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }

    /**
     * Updates META tags settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'site_description',
     *                   'site_keywords',
     *                   'site_author',    //Use gravatar service?
     *                   'site_license', //Gravatar rating
     *                   'site_copyright',
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function SaveMetaSettings($settings)
    {
        $advancedKeys = array('site_description', 'site_keywords', 'site_author',
            'site_license', 'site_copyright', 'site_custom_meta');

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $advancedKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }
            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }

    /**
     * Updates mail settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'mailer',
     *                   'gate_email',
     *                   'gate_title',
     *                   'smtp_vrfy',
     *                   'sendmail_path',
     *                   'smtp_host',
     *                   'smtp_port',
     *                   'smtp_auth',
     *                   'smtp_user',
     *                   'smtp_pass',
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function UpdateMailSettings($settings)
    {
        $mailKeys = array('mailer', 'gate_email', 'gate_title', 'smtp_vrfy', 'sendmail_path',
            'smtp_host', 'smtp_port', 'smtp_auth', 'smtp_user', 'smtp_pass');

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $mailKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }
            if ($settingKey == 'smtp_pass' && empty($settingValue)) {
                continue;
            }

            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }

    /**
     * Updates ftp settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'ftp_enabled',
     *                   'ftp_host',
     *                   'ftp_port',
     *                   'ftp_mode',
     *                   'ftp_user',
     *                   'ftp_pass',
     *                   'ftp_root',
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function UpdateFTPSettings($settings)
    {
        $ftpKeys = array('ftp_enabled', 'ftp_host', 'ftp_port',
            'ftp_mode', 'ftp_user', 'ftp_pass', 'ftp_root');

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $ftpKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }
            if ($settingKey == 'ftp_pass' && empty($settingValue)) {
                continue;
            }

            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }

    /**
     * Updates proxy settings
     *
     * @access  public
     * @param   array   $settings  Settings array. This should have the following entries:
     *
     * $settings = array(
     *                   'proxy_enabled',
     *                   'proxy_host',
     *                   'proxy_port',
     *                   'proxy_auth',
     *                   'proxy_user',
     *                   'proxy_pass',
     *                  );
     *
     * @return  mixed   True or Jaws_Error
     */
    function UpdateProxySettings($settings)
    {
        $proxyKeys = array('proxy_enabled', 'proxy_host', 'proxy_port',
            'proxy_auth', 'proxy_user', 'proxy_pass');

        foreach ($settings as $settingKey => $settingValue) {
            if (!in_array($settingKey, $proxyKeys)) {
                continue;
            }

            if (is_string($settingValue) && !empty($settingValue)) {
                $settingValue = $settingValue;
            }
            if ($settingKey == 'proxy_pass' && empty($settingValue)) {
                continue;
            }

            $this->gadget->registry->update($settingKey, $settingValue);
        }

        return true;
    }

}