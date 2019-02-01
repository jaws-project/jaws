<?php
/**
 * Class to manage Persian calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date_Jalali extends Jaws_Date
{
    protected static $jalaliEpoch = 1948320.5;
    protected static $gregorianEpoch = 1721425.5;

    /**
     * @var     array
     * @access  private
     */
    var $_JalaliDaysInMonthes = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    /*
     *
     */
    public function leap_persian($year)
    {
        return (((((($year - (($year > 0) ? 474 : 473)) % 2820) + 474) + 38) * 682) % 2816) < 682;
    }

    /*
     *
     */
    public function leap_gregorian($year)
    {
        return (($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0));
    }

    /*
     *
     */
    function persian_to_jd($year, $month, $day)
    {
        $epbase = $epyear = 0;
        $exyear = floor(($month - 1) / 12);
        $year   = $year + $exyear;
        $month  = $month - $exyear * 12;

        $epbase = $year - (($year >= 0)? 474 : 473);
        $epyear = 474 + ($epbase % 2820);

        return $day +
            (($month <= 7)? (($month - 1) * 31) : ((($month - 1) * 30) + 6)) +
            floor((($epyear * 682) - 110) / 2816) +
            ($epyear - 1) * 365 +
            floor($epbase / 2820) * 1029983 +
            (self::$jalaliEpoch - 1);
    }

    /*
     *
     */
    public function gregorian_to_jd($year, $month, $day)
    {
        return (self::$gregorianEpoch - 1) +
            (365 * ($year - 1)) +
            floor(($year - 1) / 4) +
            (-floor(($year - 1) / 100)) +
            floor(($year - 1) / 400) +
            floor(
                (((367 * $month) - 362) / 12) +
                (($month <= 2)? 0 : ($this->leap_gregorian($year) ? -1 : -2)) +
                $day
            );
    }

    /*
     *
     */
    public function jd_to_gregorian($jd)
    {
        $wjd = floor($jd - 0.5) + 0.5;
        $depoch = $wjd - self::$gregorianEpoch;
        $quadricent = floor($depoch / 146097);
        $dqc = $depoch % 146097;
        $cent = floor($dqc / 36524);
        $dcent = $dqc % 36524;
        $quad = floor($dcent / 1461);
        $dquad = $dcent % 1461;
        $yindex = floor($dquad / 365);
        $year = ($quadricent * 400) + ($cent * 100) + ($quad * 4) + $yindex;
        if (!(($cent == 4) || ($yindex == 4))) {
            $year++;
        }
        $yearday = $wjd - $this->gregorian_to_jd($year, 1, 1);
        $leapadj = (($wjd < $this->gregorian_to_jd($year, 3, 1))? 0 : ($this->leap_gregorian($year) ? 1 : 2));
        $month = floor(((($yearday + $leapadj) * 12) + 373) / 367);
        $day = ($wjd - $this->gregorian_to_jd($year, $month, 1)) + 1;

        return array(
            'year'  => $year,
            'month' => $month,
            'day'   => $day
        );
    }

    /*
     *
     */
    public function jd_to_persian($jd)
    {
        $jwday = floor(($jd + 1.5)) % 7;
        $jd = floor($jd) + 0.5;

        $depoch = $jd - $this->persian_to_jd(475, 1, 1);
        $cycle = floor($depoch / 1029983);
        $cyear = $depoch % 1029983;
        if ($cyear == 1029982) {
            $ycycle = 2820;
        } else {
            $aux1 = floor($cyear / 366);
            $aux2 = $cyear % 366;
            $ycycle = floor(((2134 * $aux1) + (2816 * $aux2) + 2815) / 1028522) +
            $aux1 + 1;
        }
        $year = $ycycle + (2820 * $cycle) + 474;
        if ($year <= 0) {
            $year--;
        }
        $yday = ($jd - $this->persian_to_jd($year, 1, 1)) + 1;
        $month = ($yday <= 186) ? ceil($yday / 31) : ceil(($yday - 6) / 30);
        $day = ($jd - $this->persian_to_jd($year, $month, 1)) + 1;

        return array(
            'year'      => $year,
            'month'     => $month,
            'day'       => $day,
            'weekDay'   => $jwday,
            'monthDays' =>
                $this->_JalaliDaysInMonthes[$month - 1] +
                ($month == 12 ? (int)$this->leap_persian($year) : 0),
            'yearDay'   => $yday,
        );
    }

    /**
     * Gregorian to Jalali Converter
     *
     * @param   int $year  Gregorian year
     * @param   int $month Gregorian month
     * @param   int $day   Gregorian day
     * @access  protected
     * @return  array   Converted time
     */
    function gregorian_to_persian($year, $month, $day)
    {
        $date = $this->jd_to_persian($this->gregorian_to_jd($year, $month, $day));
        return array(
            'year'      => str_pad($date['year'], 4, '0', STR_PAD_LEFT),
            'month'     => str_pad($date['month'], 2, '0', STR_PAD_LEFT),
            'day'       => str_pad($date['day'], 2, '0', STR_PAD_LEFT),
            'weekDay'   => $date['weekDay'],
            'monthDays' => $date['monthDays'],
            'yearDay'   => $date['yearDay']
        );
    }

    /**
     *
     * @access  public
     * @param   int     $year   Gregorian year
     * @param   int     $month  Gregorian month
     * @param   int     $day    Gregorian day
     * @param   int     $hour   Hour
     * @param   int     $minute Minute
     * @param   int     $second Second
     * @param   string  $format Date/Time format
     * @return  array   Converted time
     */
    function ToBaseDate($year, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0, $format = '')
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            @list($year, $month, $day, $hour, $minute, $second) = $args[0];
            $format = isset($args[1])? $args[1] : '';
        }

        $date = $this->jd_to_gregorian($this->persian_to_jd($year, $month, $day));
        $date = mktime((int)$hour, (int)$minute, (int)$second, $date['month'], $date['day'], $date['year']);
        return !empty($format)?
            date($format, $date) :
            array('timestamp' => $date,
                  'year'      => date("Y", $date),
                  'month'     => date("m", $date),
                  'day'       => date("d", $date),
                  'hour'      => date("H", $date),
                  'minute'    => date("i", $date),
                  'second'    => date("s", $date),
                  'monthDays' => date("t", $date),
                  'yearDay'   => date("z", $date)
                );
    }

    /**
     * Get date information
     *
     * @access  public
     * @param   int     $year   Year
     * @param   int     $month  Month
     * @param   int     $day    Day
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
        $grdate = explode('-', date('Y-m-d-H-i-s', $date));
        $prdate = $this->gregorian_to_persian($grdate[0], $grdate[1], $grdate[2]);
        $second = $grdate[5];
        $minute = $grdate[4];
        $hour   = $grdate[3];
        $day    = $prdate['day'];
        $wday   = $prdate['weekDay'];
        $month  = $prdate['month'];
        $year   = $prdate['year'];
        $yday   = $prdate['yearDay'];

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
     * Gets count of Month(s) days
     *
     * @access  public
     * @param   int     $year   Year
     * @param   int     $month  Month
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
        $grdate = explode('-', date('Y-m-d-H-i-s', $date));

        $prdate = $this->gregorian_to_persian($grdate[0], $grdate[1], $grdate[2]);
        $prdate['hour']   = $grdate[3];
        $prdate['minute'] = $grdate[4];
        $prdate['second'] = $grdate[5];
        $prdate['date']   = $date;

        if (empty($format)) {
            $format = $GLOBALS['app']->Registry->fetch('date_format', 'Settings');
        }

        return ($format == 'since')? $this->SinceFormat($prdate['date']) : $this->DateFormat($format, $prdate);
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
                    if (substr($format, $i, 2) == 'DN') {
                        $return .= $this->DayString($date['weekDay']);
                        $i++;
                    } else {
                        $return .= $this->DayShortString($date['weekDay']);
                    }
                    break;
                case 'l':
                    $return .= $this->DayString($date['weekDay']);
                    break;
                case 'e':
                    $return .= date('e', $date['date']);
                    break;
                case 'F':
                    $return .= $this->MonthString($date['month']);
                    break;
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
                    $return .= $date['weekDay'];
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