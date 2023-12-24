<?php
/**
 * Class to manage Persian calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2022 Jaws Development Group
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
        $jd = floor($jd) + 0.5;
        $jwday = floor($jd + 1.5) % 7;

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
            'year'      => $date['year'],
            'month'     => $date['month'],
            'day'       => $date['day'],
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

        $date = Jaws::getInstance()->UTC2UserTime($date);
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
        if ($this->leap_persian($year)) {
            $result[11]++;
        }
        return empty($month)? $result : $result[$month-1];
    }

    /**
     * Format the input date.
     *
     * @access  public
     * @param   string  $date       Date string
     * @param   string  $format     Format to use
     * @param   bool    $utc2local  UTC to user local time
     * @return  string  The original date with a new format
     */
    function Format($date, $format = null, $utc2local = true)
    {
        if (empty($date)) {
            return '';
        }

        $date = $utc2local? Jaws::getInstance()->UTC2UserTime($date) : $date;
        $grdate = explode('-', date('Y-m-d-H-i-s', $date));

        $prdate = $this->gregorian_to_persian($grdate[0], $grdate[1], $grdate[2]);
        $prdate['hour']   = $grdate[3];
        $prdate['minute'] = $grdate[4];
        $prdate['second'] = $grdate[5];
        $prdate['date']   = $date;

        if (empty($format)) {
            $format = Jaws::getInstance()->registry->fetch('date_format', 'Settings');
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

        $i = 0;
        $return = '';
        while ($i < strlen($format)) {
            switch($format[$i]) {
                case 'T':
                    $return.= $date['date'] * 1000;
                    break;

                case 's':
                    if (substr($format, $i, 2) === 'ss') {
                        $return.= str_pad($date['second'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= $date['second'];
                    }
                    break;

                case 'm':
                    if (substr($format, $i, 2) === 'mm') {
                        $return.= str_pad($date['minute'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= $date['minute'];
                    }
                    break;

                case 'h':
                    if (substr($format, $i, 2) === 'hh') {
                        $return.= str_pad(($date['hour']>=12)? ($date['hour']-12) : $date['hour'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= ($date['hour']>=12)? ($date['hour']-12) : $date['hour'];
                    }
                    break;

                case 'H':
                    if (substr($format, $i, 2) === 'HH') {
                        $return.= str_pad($date['hour'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= $date['hour'];
                    }
                    break;

                case 'a':
                case 'A':
                    if (substr($format, $i, 3) == 'AGO') {
                        $return .= $this->SinceFormat($date['date']);
                        $i = $i + 2;
                    } elseif (substr($format, $i, 2) == 'aa') {
                        $return .= Jaws::t(($date['hour']>=12)? 'HOURS_PM' : 'HOURS_AM');
                        $i++;
                    }
                    break;

                case 'E':
                    if (substr($format, $i, 4) === 'EEEE') {
                        $return.= this->DayString($date['weekDay']);
                        $i+=3;
                    } else {
                        $return.= $this->DayShortString($date['weekDay']);
                    }
                    break;

                case 'd':
                    if (substr($format, $i, 2) === 'dd') {
                        $return.= str_pad($date['day'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= $date['day'];
                    }
                    break;

                case 'M':
                    if (substr($format, $i, 4) === 'MMMM') {
                        $return.= this->MonthString($date['month']);
                        $i+=3;
                    } elseif (substr($format, $i, 3) === 'MMM') {
                        $return.= $this->MonthShortString($date['month']);
                        $i+=2;
                    } elseif (substr($format, $i, 2) === 'MM') {
                        $return.= str_pad($date['month'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    } else {
                        $return.= $date['month'];
                    }
                    break;

                case 'y':
                    if (substr($format, $i, 4) === 'yyyy') {
                        $return.= str_pad($date['year'], 4, '0', STR_PAD_LEFT);
                        $i+=3;
                    } elseif (substr($format, $i, 2) === 'yy') {
                        $return.= str_pad($date['year'], 2, '0', STR_PAD_LEFT);
                        $i++;
                    }
                    break;

                case '\\':
                    $return.= substr($format, $i, 2);
                    $i++;
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
                Jaws::t('JALALI_MONTH_0'),
                Jaws::t('JALALI_MONTH_1'),
                Jaws::t('JALALI_MONTH_2'),
                Jaws::t('JALALI_MONTH_3'),
                Jaws::t('JALALI_MONTH_4'),
                Jaws::t('JALALI_MONTH_5'),
                Jaws::t('JALALI_MONTH_6'),
                Jaws::t('JALALI_MONTH_7'),
                Jaws::t('JALALI_MONTH_8'),
                Jaws::t('JALALI_MONTH_9'),
                Jaws::t('JALALI_MONTH_10'),
                Jaws::t('JALALI_MONTH_11'),
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
                Jaws::t('JALALI_MONTH_SHORT_0'),
                Jaws::t('JALALI_MONTH_SHORT_1'),
                Jaws::t('JALALI_MONTH_SHORT_2'),
                Jaws::t('JALALI_MONTH_SHORT_3'),
                Jaws::t('JALALI_MONTH_SHORT_4'),
                Jaws::t('JALALI_MONTH_SHORT_5'),
                Jaws::t('JALALI_MONTH_SHORT_6'),
                Jaws::t('JALALI_MONTH_SHORT_7'),
                Jaws::t('JALALI_MONTH_SHORT_8'),
                Jaws::t('JALALI_MONTH_SHORT_9'),
                Jaws::t('JALALI_MONTH_SHORT_10'),
                Jaws::t('JALALI_MONTH_SHORT_11'),
            );
            $this->_Months['short'] =& $months;
        }

        if ($m = (int)$m) {
            return $this->_Months['short'][$m - 1];
        }

        return $this->_Months['short'];
    }

}