<?php
/**
 * Class to manage dates
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Amir Mohammad Saied <amir@php.net>
 * @autho      Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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
     * An interface for available drivers
     *
     * @param   string  $calendar   The calendar that's required
     * @access  public
     */
    function &factory($calendar)
    {
        if (!file_exists(JAWS_PATH . 'include/Jaws/Date/'. $calendar .'.php')) {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG,
                                 'Loading calendar '.$calendar.' failed, Attempting to load default calendar');
            $calendar = 'Gregorian';
        }

        require_once JAWS_PATH . "include/Jaws/Date/$calendar.php";
        $class = 'Jaws_Date_' . $calendar;
        $calendar = new $class();
        return $calendar;
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
     * @return The original date with a new format
     */
    function Format()
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
            $since = ($mins <= 1) ?($mins == 1) ? _t('GLOBAL_DATE_1_MINUTE') : _t('GLOBAL_DATE_FEW_SECONDS') :
                _t('GLOBAL_DATE_MINUTES', $mins);
        } elseif (($diff <= 86400) &&($diff > 3600)) {
            $hours = round($diff / 3600);
            $since = ($hours <= 1) ? _t('GLOBAL_DATE_1_HOUR') : _t('GLOBAL_DATE_HOURS', $hours);
        } elseif ($diff >= 86400) {
            $days  = round($diff / 86400);
            $since = ($days <= 1) ? _t('GLOBAL_DATE_1_DAY') : _t('GLOBAL_DATE_DAYS', $days);
        }

        return _t('GLOBAL_DATE_AGO', $since);
    }

    /**
     * Return the month number in string
     *
     * @param  int    $m  Numeric month(1..12)
     * @return  string     The month in string not in number
     * @access  public
     */
    function MonthString($m)
    {
        $cal_name = strtoupper(isset($GLOBALS['app']) ? $GLOBALS['app']->GetCalendar() : 'Gregorian');
        if (!isset($this->_Months['long'])) {
            $months = array(
                _t('GLOBAL_'.$cal_name.'_MONTH_FIRST'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SECOND'),
                _t('GLOBAL_'.$cal_name.'_MONTH_THIRD'),
                _t('GLOBAL_'.$cal_name.'_MONTH_FOURTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_FIFTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SIXTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SEVENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_EIGHTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_NINTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_TENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_ELEVENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_TWELFTH'),
            );
            $this->_Months['long'] =& $months;
        }

        if ($m != '') {
            $m = (int)$m;
            return $this->_Months['long'][$m - 1];
        }

        return $this->_Months['long'];
    }

    /**
     * Return the month number in string
     *
     * @param  int    $m  Numeric month(1..12)
     * @return  string     The month in string not in number
     * @access  public
     */
    function MonthShortString($m = '')
    {
        $cal_name = strtoupper(isset($GLOBALS['app']) ? $GLOBALS['app']->GetCalendar() : 'Gregorian');
        if (!isset($this->_Months['short'])) {
            $months = array(
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_FIRST'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_SECOND'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_THIRD'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_FOURTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_FIFTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_SIXTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_SEVENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_EIGHTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_NINTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_TENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_ELEVENTH'),
                _t('GLOBAL_'.$cal_name.'_MONTH_SHORT_TWELFTH'),
            );
            $this->_Months['short'] =& $months;
        }

        if ($m = (int)$m) {
            return $this->_Months['short'][$m - 1];
        }

        return $this->_Months['short'];
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
                _t('GLOBAL_DAY_SUNDAY'),
                _t('GLOBAL_DAY_MONDAY'),
                _t('GLOBAL_DAY_TUESDAY'),
                _t('GLOBAL_DAY_WEDNESDAY'),
                _t('GLOBAL_DAY_THURSDAY'),
                _t('GLOBAL_DAY_FRIDAY'),
                _t('GLOBAL_DAY_SATURDAY'),
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
                _t('GLOBAL_DAY_SHORT_SUNDAY'),
                _t('GLOBAL_DAY_SHORT_MONDAY'),
                _t('GLOBAL_DAY_SHORT_TUESDAY'),
                _t('GLOBAL_DAY_SHORT_WEDNESDAY'),
                _t('GLOBAL_DAY_SHORT_THURSDAY'),
                _t('GLOBAL_DAY_SHORT_FRIDAY'),
                _t('GLOBAL_DAY_SHORT_SATURDAY'),
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
        if (preg_match("/^([123456789][[:digit:]]{3})-(0[1-9]|1[012])-(0[1-9]|[12][[:digit:]]|3[01]) ([01][[:digit:]]|2[0123]):([0-5][[:digit:]]):([0-5][[:digit:]])$/", $date, $date_part) &&
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

    /**
     * Is leap year
     *
     * @param   int     $year  Gregorian year
     * @access  private
     * @return  bool    True/False
     */
    function _IsLeapYear($year)
    {
        return (($year%4) == 0 && (($year%100) != 0 || ($year%400) == 0));
    }

    /**
     * Computing total days of Gregorian calendar
     *
     * @param   int     $year   Gregorian year
     * @param   int     $month  Gregorian month
     * @param   int     $day    Gregorian day
     * @access  public
     * @return  bool    True/False
     */
    function GregorianTotalDays($year, $month, $day)
    {
        $year--;
        $day_number =  365*$year + floor($year/4) - floor($year/100) + floor($year/400);
        $year++;
        for ($i=0; $i < ($month-1); ++$i) {
            $day_number += $this->_GregorianDaysInMonthes[$i];
        }

        if ($month > 2 && $this->_IsLeapYear($year)) {
            $day_number++;
        }

        return $day_number + $day;
    }

    /**
     * N days To Gregorian Convertor
     *
     * @param   int $days   Number of days
     * @param   int $offset Year offset
     * @access  protected
     * @return  array   Converted time
     */
    function ToGregorian($days, $offset = 0, $hour = 0, $minute = 0, $second = 0, $format = '')
    {
        $days--;
        $year = $offset;
        $year += floor($days/146097)*400; // 146097 = 365*400 + 400/4 - 400/100 + 1
        $days %= 146097;

        $year += floor($days / 36524)*100; // 36524 = 365*100 + 100/4 - 1
        $days  %= 36524;

        $year += floor($days / 1461)*4; // 1461 = 4*365 + 1
        $days  %= 1461;
        $days++;

        $isLeap = (int)$this->_IsLeapYear($year);
        while ($days > (365 + $isLeap)) {
            $year++;
            $days -= (365 + $isLeap);
            $isLeap = (int)$this->_IsLeapYear($year);
        }

        $month = 0;
        $year_days = $days;

        while ($days > $this->_GregorianDaysInMonthes[$month])
        {
            if ($month==1 && $isLeap && $days == 29) {
                break;
            }

            $days -= $this->_GregorianDaysInMonthes[$month];
            $days -= $month==1 ? $isLeap : 0;
            $month++;
        }

        $dt = mktime($hour, $minute, $second, $month + 1, $days, $year);
        return !empty($format)?
            date($format, $dt) :
            array(
                'timestamp' => $dt,
                'year'      => $year,
                'month'     => $month + 1,
                'day'       => $days,
                'hour'      => $hour,
                'minute'    => $minute,
                'second'    => $second,
                'monthDays' => $this->_GregorianDaysInMonthes[$month] + ($month==1 ? $isLeap : 0),
                'yearDay'   => $year_days
            );
    }

}