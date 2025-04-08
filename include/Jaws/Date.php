<?php
/**
 * Class to manage dates
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Amir Mohammad Saied <amir@php.net>
 * @autho      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date
{
    /**
     * Has all months in an array
     *
     * @var     array
     * @access  private
     */
    var $_Months = array();

    /**
     * Has all days in an array
     *
     * @var     array
     * @access  private
     */
    var $_Days = array();

    /**
     * Has the current timezone in ISO8601 form
     *
     * @var     string
     * @access  private
     */
    var $_ISO8601Timezone;

    /**
     * @access  private
     */
    var $_GregorianDaysInMonthes = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    /**
     * default/user calendar/timezone
     *
     * @var     array
     * @access  static
     */
    static protected $options = array();

    /**
     * Creates an available Jaws_Date driver instance calendar
     *
     * @return  object returns the instance
     * @access  public
     */
    static function getInstance($calendar = '')
    {
        if (empty(self::$options)) {
            $thisApp = Jaws::getInstance();
            self::$options = array(
                'calendar' => $thisApp->registry->fetchByUser($thisApp->session->user->id, 'calendar', 'Settings'),
                'site_calendar' => $thisApp->registry->fetch('calendar', 'Settings'),
                'timezone' => $thisApp->registry->fetchByUser($thisApp->session->user->id, 'timezone', 'Settings'),
                'site_timezone' => $thisApp->registry->fetch('timezone', 'Settings'),
            );
        }

        $calendar = preg_replace('/[^[:alnum:]_]/', '', empty($calendar)? self::$options['calendar'] : $calendar);
        if (!file_exists(ROOT_JAWS_PATH . 'include/Jaws/Date/'. $calendar .'.php')) {
            $GLOBALS['log']->Log(JAWS_DEBUG,
                                 'Loading calendar '.$calendar.' failed, Attempting to load default calendar');
            $calendar = 'Gregorian';
        }

        static $instances = array();
        if (!isset($instances[$calendar])) {
            $classname = 'Jaws_Date_' . $calendar;
            $instances[$calendar] = new $classname();
        }

        return $instances[$calendar];
    }

    /**
     * Get local time
     *
     * @access  private
     * @param   mixed   $time   timestamp
     * @param   bool    $default_timezone   use default timezone instead of user timezone
     * @return  int     Unix timestamp
     */
    function utc2local($time = '', $default_timezone = false)
    {
        $time = empty($time)? time() : $time;
        if (is_array($time)) {
            $time = mktime(isset($time[5])? $time[5] : 0,
                           isset($time[4])? $time[4] : 0,
                           isset($time[3])? $time[3] : 0,
                           isset($time[1])? $time[1] : 0,
                           isset($time[2])? $time[2] : 0,
                           $time[0]);
        }
        $time = is_numeric($time)? (int)$time : strtotime($time);

        // timezone offset
        $timezone = $default_timezone? self::$options['timezone'] : self::$options['site_timezone'];
        if (is_numeric($timezone)) {
            $gmt_offset = $timezone * 3600;
        } else {
            @date_default_timezone_set($timezone);
            $gmt_offset = date('Z', $time);
            date_default_timezone_set('UTC');
        }

        return $time + $gmt_offset;
    }

    /**
     * Get UTC time
     *
     * @access  private
     * @param   mixed   $time   timestamp
     * @param   bool    $default_timezone   use default timezone instead of user timezone
     * @return  int     Unix timestamp
     */
    function local2utc($time, $default_timezone = false)
    {
        if (is_array($time)) {
            $time = mktime(isset($time[5])? $time[5] : 0,
                           isset($time[4])? $time[4] : 0,
                           isset($time[3])? $time[3] : 0,
                           isset($time[1])? $time[1] : 0,
                           isset($time[2])? $time[2] : 0,
                           $time[0]);
        }
        $time = is_numeric($time)? $time : strtotime($time);

        // timezone offset
        $timezone = $default_timezone? self::$options['timezone'] : self::$options['site_timezone'];
        if (is_numeric($timezone)) {
            $gmt_offset = $timezone * 3600;
        } else {
            @date_default_timezone_set($timezone);
            $gmt_offset = date('Z', $time);
            date_default_timezone_set('UTC');
        }

        return $time - $gmt_offset;
    }

    /**
     * Returns the timezone in ISO8601 representation
     *
     * @return  string Timezone
     * @access  public
     */
    function GetISO8601Timezone()
    {
        if (is_null($this->_ISO8601Timezone)) {
            $tz = date('O');
            $tz = substr($tz, 0, 3) . ':' . substr($tz, 3, 2);
            $this->_ISO8601Timezone = $tz;
        }

        return $this->_ISO8601Timezone;
    }

    /**
     * Convert the input date(timestamp) to ISO standard
     *
     * @param   mixed   $datetime  Input date, in Timestamp format
     * @return  string  Date in ISO8061 Format
     * @access  public
     */
    function TimeStampToISO8601($datetime)
    {
        if (is_string($datetime)) {
            $string = substr($datetime, 0, 4)  . '-' .
                      substr($datetime, 4, 2)  . '-' .
                      substr($datetime, 6, 2)  . ' ' .
                      substr($datetime, 8, 2)  . ':' .
                      substr($datetime, 10, 2) . ':' .
                      substr($datetime, 12, 2);
            $datetime = strtotime($string);
        }
        ///FIXME check if this is returning proper ISO8601 date string
        return date('Y-m-d\TH:i:s', $datetime) . $this->GetISO8601Timezone();
    }

    /**
     * Convert the input date(datetime) to ISO standard
     *
     * @param   mixed   $datetime  Input date, in datetime format
     * @return  string  Date in ISO8061 Format
     * @access  public
     */
    function DateTimeToISO8601($datetime)
    {
        if (strpos($datetime, '-')) {
            $datetime = strtotime($datetime);
            return date('Y-m-d\TH:i:s', $datetime) . $this->GetISO8601Timezone();
        }

        return $this->TimeStampToISO8601($datetime);
    }

    /**
     * Detect the time of date and convert it to ISO
     *
     * @param   mixed   $datetime  Input date, can be in timestamp or datetime format
     * @return  string  Date in ISO8061 Format
     * @access  public
     */
    function ToISO($datetime)
    {
        if (strpos($datetime, '-')) {
            return $this->DateTimeToISO8601($datetime);
        }

        return $this->TimeStampToISO8601($datetime);
    }

    /**
     * Format the input date.
     *
     * @param   string  $date   Date string
     * @param   string  $format Format to use
     * @param   bool    $utc2local  UTC to user local time
     * @return The original date with a new format
     */
    function Format($date, $format = null, $utc2local = true)
    {
    }

   /**
    * Output the date in since format
    *
    * @param   string  $date   Date String
    * @return   string  since formatted
    */
    function SinceFormat($date)
    {
        $diff = (time() - $date);
        if ($diff <= 3600) {
            $mins  = round($diff / 60);
            $since = ($mins <= 1) ?($mins == 1) ? Jaws::t('DATE_1_MINUTE') : Jaws::t('DATE_FEW_SECONDS') :
                Jaws::t('DATE_MINUTES', $mins);
        } elseif (($diff <= 86400) &&($diff > 3600)) {
            $hours = round($diff / 3600);
            $since = ($hours <= 1) ? Jaws::t('DATE_1_HOUR') : Jaws::t('DATE_HOURS', $hours);
        } elseif ($diff >= 86400) {
            $days  = round($diff / 86400);
            $since = ($days <= 1) ? Jaws::t('DATE_1_DAY') : Jaws::t('DATE_DAYS', $days);
        }

        return Jaws::t('DATE_AGO', $since);
    }

    /**
     * Return the day number in string
     *
     * @param   int    $d   Numeric day (0..6)
     * @return  string      The day in string not in number
     * @access  public
     */
    function DayString($d = '')
    {
        if (!isset($this->_Days['long'])) {
            $days = array(
                Jaws::t('DAY_0'),
                Jaws::t('DAY_1'),
                Jaws::t('DAY_2'),
                Jaws::t('DAY_3'),
                Jaws::t('DAY_4'),
                Jaws::t('DAY_5'),
                Jaws::t('DAY_6'),
            );
            $this->_Days['long'] =& $days;
        }

        if (is_numeric($d)) {
            return $this->_Days['long'][$d];
        }

        return $this->_Days['long'];
    }

    /**
     * Return the day number in string
     *
     * @param   int    $d   Numeric day (0..6)
     * @return  string      The day in string not in number
     * @access  public
     */
    function DayShortString($d)
    {
        if (!isset($this->_Days['short'])) {
            $days = array(
                Jaws::t('DAY_SHORT_0'),
                Jaws::t('DAY_SHORT_1'),
                Jaws::t('DAY_SHORT_2'),
                Jaws::t('DAY_SHORT_3'),
                Jaws::t('DAY_SHORT_4'),
                Jaws::t('DAY_SHORT_5'),
                Jaws::t('DAY_SHORT_6'),
            );
            $this->_Days['short'] =& $days;
        }

        if (is_numeric($d)) {
            return $this->_Days['short'][$d];
        }

        return $this->_Days['short'];
    }

    /**
     * Valid a date
     * Based on http://php.net/manual/en/function.checkdate.php#54948 (Zoe Blade)
     *
     * @params string $date Date to valid
     * @return  bool    True if successful
     * @access  public
     */
    function ValidDBDate($date)
    {
        if (preg_match("/^([123456789][[:digit:]]{3})\-(0[1-9]|1[012])\-(0[1-9]|[12][[:digit:]]|3[01]) ([01][[:digit:]]|2[0123]):([0-5][[:digit:]]):([0-5][[:digit:]])$/", $date, $date_part) &&
            checkdate($date_part[2], $date_part[3], $date_part[1])) {
           return true;
        }

        return false;
    }

    /**
     * ISO8601 to db date (without timezone)
     *
     * @params string $isodate Date to convert
     * @return  string Date formatted as YYYY-MM-DD HH:mm:ss
     * @access  public
     */
    function ISOToDBDate($isodate)
    {
        return substr($isodate, 0, 4)  . '-' .
               substr($isodate, 5, 2)  . '-' .
               substr($isodate, 8, 2)  . ' ' .
               substr($isodate, 11, 2) . ':' .
               substr($isodate, 14, 2) . ':' .
               substr($isodate, 17, 2);
    }

}