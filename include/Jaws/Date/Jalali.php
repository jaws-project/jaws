<?php
/**
 * Class to manage Jalali calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Amir Mohammad Saied <amir@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date_Jalali extends Jaws_Date
{
    /**
     * @var     array
     * @access  private
     */
    var $_LeapYear = array(1, 5, 9, 13, 17, 22, 26, 30);

    /**
     * @var     array
     * @access  private
     */
    var $_JalaliDaysInMonthes = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    /**
     * Is leap year
     *
     * @param   int     $year  Jalali year
     * @access  private
     * @return  bool    True/False
     */
    function IsJalaliLeapYear($year)
    {
        return in_array(($year % 33), $this->_LeapYear);
    }

    /**
     * Computing total days of Jalali calendar
     *
     * @param   int     $year   Jalali year
     * @param   int     $month  Jalali month
     * @param   int     $day    Jalali day
     * @access  public
     * @return  bool    True/False
     */
    function JalaliTotalDays($year, $month, $day)
    {
        $ym = floor(($month-1)/12);
        $month = $month - $ym*12;
        $year  = $year + $ym - 1;
        $leap_days = floor($year/33)*8 + floor(($year%33)/4);

        $day_number =  365*$year + $leap_days;
        for ($i=0; $i < ($month-1); ++$i) {
            $day_number += $this->_JalaliDaysInMonthes[$i];
        }

        return $day_number + $day;
    }

    /**
     * Gets count of Month(s) days
     *
     * @access  public
     * @param   int     $year   Jalali year
     * @param   int     $month  Jalali month
     * @return  mixed   Count of Month days or array of count all months days 
     */
    function MonthDays($year, $month = 0)
    {
        $result = $this->_JalaliDaysInMonthes;
        if ($this->IsJalaliLeapYear($year)) {
            $result[11]++;
        }
        return empty($month)? $result : $result[$month-1];
    }

    /**
     * Jalali to Gregorian Convertor
     *
     * @access  public
     * @param   int     $year   Jalali year
     * @param   int     $month  Jalali month
     * @param   int     $day    Jalali day
     * @param   int     $hour   Hour
     * @param   int     $minute Minute
     * @param   int     $second Second
     * @param   string  $format Date/Time Format
     * @return  array   Converted time
     */
    function ToBaseDate($year, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0, $format = '')
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            @list($year, $month, $day, $hour, $minute, $second) = $args[0];
            $format = isset($args[1])? $args[1] : '';
        }

        if ($month == 0) {
            $year--;
            $month = 12;
        }

        if ($month == 13) {
            $year++;
            $month = 1;
        }

        $year = $year - 979;
        $gregorian_day = $this->JalaliTotalDays($year, $month, $day) + 79;
        return $this->ToGregorian($gregorian_day, 1601, $hour, $minute, $second, $format);
    }

    /**
     * Gregorian to Jalali Convertor
     *
     * @param   int $year  Gregorian year
     * @param   int $month Gregorian month
     * @param   int $day   Gregorian day
     * @access  protected
     * @return  array   Converted time
     */
    function GregorianToJalali($year, $month, $day)
    {
        $year = $year - 1600;
        $jalali_day = $this->GregorianTotalDays($year, $month, $day) - 79;
        $jalali_wday = ($jalali_day + 3) % 7;

        $jalali_year = floor($jalali_day/12053)*33; // 12053 = 33*365 + 8
        $jalali_day %= 12053;
        $jalali_year = 979 + $jalali_year + floor(($jalali_day - 1) / 1461)*4; // 1461 = 4*365 + 1
        $jalali_day  = ($jalali_day - 1) % 1461 + 1;

        $jalali_year++;
        $isLeap = (int)$this->IsJalaliLeapYear($jalali_year);
        while ($jalali_day > (365 + $isLeap)) {
            $jalali_day -= (365 + $isLeap);
            $jalali_year++;
            $isLeap = (int)$this->IsJalaliLeapYear($jalali_year);
        }

        $jalali_month = 0;
        $year_days = $jalali_day;

        while ($jalali_day > $this->_JalaliDaysInMonthes[$jalali_month] + (($jalali_month==11)? $isLeap : 0))
        {
            $jalali_day -= $this->_JalaliDaysInMonthes[$jalali_month];
            $jalali_month++;
        }

        return array('year'      => str_pad($jalali_year, 4, '0', STR_PAD_LEFT),
                     'month'     => str_pad($jalali_month + 1, 2, '0', STR_PAD_LEFT),
                     'day'       => str_pad($jalali_day,  2, '0', STR_PAD_LEFT),
                     'weekDay'   => $jalali_wday,
                     'monthDays' => $this->_JalaliDaysInMonthes[$jalali_month]+
                                    ($jalali_month==11 ? $isLeap : 0),
                     'yearDay'   => $year_days
                    );
    }

    /**
     * Get date information
     *
     * @access  public
     * @param   int     $year   Jalali year
     * @param   int     $month  Jalali month
     * @param   int     $day    Jalali day
     * @param   int     $hour   Hour
     * @param   int     $minute Minute
     * @param   int     $second Second
     * @return  array   Date time information
     */
    function GetDateInfo($year, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0)
    {
        if (is_array(func_get_arg(0))) {
            $date = $this->ToBaseDate($year);
            $date = $date['timestamp'];
        } elseif (func_num_args() > 1) {
            $date = $this->ToBaseDate($year, $month, $day, $hour, $minute, $second);
            $date = $date['timestamp'];
        } else { // unix timestamp
            $date = $year;
        }

        $date = $GLOBALS['app']->UTC2UserTime($date);
        $date_array = explode('-', date('Y-m-d-H-i-s', $date));
        $jalali_array = $this->GregorianToJalali($date_array[0], $date_array[1], $date_array[2]);
        $second = $date_array[5];
        $minute = $date_array[4];
        $hour   = $date_array[3];
        $day    = $jalali_array['day'];
        $wday   = $jalali_array['weekDay'];
        $month  = $jalali_array['month'];
        $year   = $jalali_array['year'];
        $yday   = $jalali_array['yearDay'];

        return array(
                'seconds' => $second,
                'minutes' => $minute,
                'hours'   => $hour,
                'mday'    => $day,
                'wday'    => $wday,
                'mon'     => $month,
                'year'    => $year,
                'yday'    => $yday,
                'weekday' => $this->DayString($wday),
                'month'   => $this->MonthString($month),
            );
    }

   /**
    * Format the input date.
    *
    * @access  public
    * @param   string   $date   Date string
    * @param   string   $format Format to use
    * @return  string   The original date with a new format
    */
    function Format($date, $format = null)
    {
        if (empty($date)) {
            return '';
        }

        $date = $GLOBALS['app']->UTC2UserTime($date);
        $date_array = explode('-', date('Y-m-d-H-i-s', $date));

        $jalali_array = $this->GregorianToJalali($date_array[0], $date_array[1], $date_array[2]);
        $jalali_array['hour']   = $date_array[3];
        $jalali_array['minute'] = $date_array[4];
        $jalali_array['second'] = $date_array[5];
        $jalali_array['date']   = $date;

        if (empty($format)) {
            $format = $GLOBALS['app']->Registry->fetch('date_format', 'Settings');
        }

        return ($format == 'since')? $this->SinceFormat($jalali_array['date']) : $this->DateFormat($format, $jalali_array);
    }

   /**
    * Format the input date.
    *
    * @access  public
    * @param   string   $date   Date string
    * @param   string   $format Format to use
    * @return  string   The original date with a new format
    */
    function DateFormat($format, $date)
    {
        if (empty($date)) {
            return;
        }
        $return = '';

        $i = 0;
        while ($i < strlen($format)) {
            switch($format[$i]) {
                case 'A':
                case 'a':
                    if (substr($format, $i, 3) == 'AGO') {
                        $return .= $this->SinceFormat($date['date']);
                        $i = $i + 2;
                    } else {
                        if (date('a', $date['date']) == 'pm') {
                            $return .= _t('GLOBAL_HOURS_PM');
                        } else {
                            $return .= _t('GLOBAL_HOURS_AM');
                        }
                    }
                    break;
                case 'c':
                    $return .= $this->DateFormat('Y-m-d H:i:s:P', $date);
                    break;
                case 'd':
                    $return .= $date['day'];
                    break;
                case 'D':
                case 'l':
                    if (substr($format, $i, 2) == 'DN') {
                        $return .= $this->DayString(date('w', $date['date']));
                        $i++;
                    } else {
                        $return .= $this->DayShortString(date('w', $date['date']));
                    }
                    break;
                case 'e':
                    $return .= date('e', $date['date']);
                    break;
                case 'F':
                case 'M':
                    if (substr($format, $i, 2) == 'MN') {
                        $return .= $this->MonthString($date['month']);
                        $i++;
                    } else {
                        $return .= $this->MonthShortString($date['month']);
                    }
                    break;
                case 'g':
                    $return .= date('g', $date['date']);
                    break;
                case 'G':
                case 'H':
                    $return .= $date['hour'];
                    break;
                case 'h':
                    $return .= date('h', $date['date']);
                    break;
                case 'i':
                    $return .= $date['minute'];
                    break;
                case 'j':
                    $return .= $date['day'];
                    break;
                case 'm':
                case 'n':
                    $return .= $date['month'];
                    break;
                case 'N':
                    $return .= date('N', $date['date']);
                    break;
                case 'O':
                    $return .= date('O', $date['date']);
                    break;
                case 'P':
                    $return .= date('P', $date['date']);
                    break;
                case 'o':
                case 'Y':
                    $return .= $date['year'];
                    break;
                case 'r':
                    $return .= $this->DateFormat('D, d M Y H:i:s O', $date);
                    break;
                case 's':
                    $return .= $date['second'];
                    break;
                case 'T':
                    $return .= date('T', $date['date']);
                    break;
                case 't':
                    $return .= $date['monthDays'];
                    break;
                case 'U':
                    $return .= date('U', $date['date']);
                    break;
                case 'y':
                    $return .= substr($date['year'], 2, 2);
                    break;
                case 'z':
                    $return .= $date['yearDay'];
                    break;
                default:
                    $return .= $format[$i];
                    break;
            }
            $i++;
        }

        return $return;
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
                _t('GLOBAL_DAY_SATURDAY'),
                _t('GLOBAL_DAY_SUNDAY'),
                _t('GLOBAL_DAY_MONDAY'),
                _t('GLOBAL_DAY_TUESDAY'),
                _t('GLOBAL_DAY_WEDNESDAY'),
                _t('GLOBAL_DAY_THURSDAY'),
                _t('GLOBAL_DAY_FRIDAY'),
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
                _t('GLOBAL_DAY_SHORT_SATURDAY'),
                _t('GLOBAL_DAY_SHORT_SUNDAY'),
                _t('GLOBAL_DAY_SHORT_MONDAY'),
                _t('GLOBAL_DAY_SHORT_TUESDAY'),
                _t('GLOBAL_DAY_SHORT_WEDNESDAY'),
                _t('GLOBAL_DAY_SHORT_THURSDAY'),
                _t('GLOBAL_DAY_SHORT_FRIDAY'),
            );
            $this->_Days['short'] =& $days;
        }

        if (is_numeric($d)) {
            return $this->_Days['short'][$d];
        }

        return $this->_Days['short'];
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
        if (!isset($this->_Months['long'])) {
            $months = array(
                _t('GLOBAL_JALALI_MONTH_FIRST'),
                _t('GLOBAL_JALALI_MONTH_SECOND'),
                _t('GLOBAL_JALALI_MONTH_THIRD'),
                _t('GLOBAL_JALALI_MONTH_FOURTH'),
                _t('GLOBAL_JALALI_MONTH_FIFTH'),
                _t('GLOBAL_JALALI_MONTH_SIXTH'),
                _t('GLOBAL_JALALI_MONTH_SEVENTH'),
                _t('GLOBAL_JALALI_MONTH_EIGHTH'),
                _t('GLOBAL_JALALI_MONTH_NINTH'),
                _t('GLOBAL_JALALI_MONTH_TENTH'),
                _t('GLOBAL_JALALI_MONTH_ELEVENTH'),
                _t('GLOBAL_JALALI_MONTH_TWELFTH'),
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
        if (!isset($this->_Months['short'])) {
            $months = array(
                _t('GLOBAL_JALALI_MONTH_SHORT_FIRST'),
                _t('GLOBAL_JALALI_MONTH_SHORT_SECOND'),
                _t('GLOBAL_JALALI_MONTH_SHORT_THIRD'),
                _t('GLOBAL_JALALI_MONTH_SHORT_FOURTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_FIFTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_SIXTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_SEVENTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_EIGHTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_NINTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_TENTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_ELEVENTH'),
                _t('GLOBAL_JALALI_MONTH_SHORT_TWELFTH'),
            );
            $this->_Months['short'] =& $months;
        }

        if ($m = (int)$m) {
            return $this->_Months['short'][$m - 1];
        }

        return $this->_Months['short'];
    }

}