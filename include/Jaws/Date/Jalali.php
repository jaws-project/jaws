<?php
/**
 * Class to manage Persian calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date_Jalali extends Jaws_Date
{
    /**
     * Converts a Gregorian date to Julian Day Number.
     *
     * @access  private
     * @param   int $gy Gregorian year
     * @param   int $gm Gregorian month (1–12)
     * @param   int $gd Gregorian day (1–31)
     * @return  int Julian Day Number
     */
    private static function gregorian_to_jdn($gy, $gm, $gd)
    {
        // Adjust month and year for January and February (they are treated as months 13 and 14 of the previous year)
        if ($gm <= 2) {
            $gm += 12;
            $gy--;
        }

        // Century correction (account for the 100-year rule in Gregorian leap years)
        $A = floor($gy / 100);
        // This corrects the leap year discrepancies in the Gregorian calendar
        $B = 2 - $A + floor($A / 4);

        // JDN formula based on Gregorian calendar rules
        return floor(365.25 * ($gy + 4716)) + floor(30.6001 * ($gm + 1)) + $gd + $B - 1524.5;
    }

    /**
     * Converts a Julian Day Number (JDN) to Gregorian date.
     *
     * @param   float   $jdn    Julian Day Number
     * @return  array   Gregorian date (year, month, day)
     */
    private static function jdn_to_gregorian($jdn)
    {
        // Step 1: Adjust JDN to account for fractional days
        $N = $jdn + 0.5;  // Add 0.5 to account for fractional days in JDN.

        // Step 2: Extract the integer part of the Julian Day Number (Z) and the fractional part (F)
        $Z = floor($N);  // The integer part represents the Julian date number.
        $F = $N - $Z;  // The fractional part represents the time of day.

        // Step 3: Calculate the number of days since a given reference date
        // The constants below are used for corrections in the Julian calendar system:
        $A = floor(($Z - 1867216.25) / 36524.25);  // Adjust for the leap year cycle difference between Julian and Gregorian calendars
        $B = $Z + 1 + $A - floor($A / 4);  // Apply further correction for leap years and century adjustments.

        // Step 4: Calculate the Julian year (D) and convert Julian Day to Gregorian date
        $C = $B + 1524;  // Get the final value after all corrections
        $D = floor(($C - 122.1) / 365.25);  // Calculate the year by considering the leap years and regular years
        $E = floor(365.25 * $D);  // Number of days in the year
        $G = floor(($C - $E) / 30.6001);  // Calculate month and adjust based on the fractional days

        // Step 5: Convert to day, month, and year
        $gd = $C - $E - floor(30.6001 * $G) + $F;  // Calculate the day of the month based on the remaining days

        // Step 6: Determine the month
        if ($G < 14) {
            $gm = $G - 1;  // If month is less than 14, it's between March and December
        } else {
            $gm = $G - 13;  // If month is greater than or equal to 14, it's January or February
        }

        // Step 7: Adjust the year based on the month
        if ($gm > 2) {
            $gy = $D - 4716;  // For months March to December, adjust based on the Julian year calculation
        } else {
            $gy = $D - 4715;  // For months January and February, adjust the year for leap years and calendar transitions
        }

        // Return the final Gregorian date
        return array(
            'year'  => $gy,
            'month' => $gm,
            'day'   => floor($gd),
            'leap'  => (($gy % 4) == 0) && ((($gy % 100) != 0) || (($gy % 400) == 0)),
        );
    }

    /**
     * Converts a Persian (Jalali) date to Julian Day Number.
     *
     * @access  private
     * @param   int $gy Gregorian year
     * @param   int $gm Gregorian month (1–12)
     * @param   int $gd Gregorian day (1–31)
     * @return  int Julian Day Number
     */
    private static function persian_to_jdn($jy, $jm, $jd, &$leap = false)
    {
        $breaks = [
            -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210,
            1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178
        ];

        $jump = 0;
        $leapJ = -14;
        $lastBreak = $breaks[0];

        if ($jy < $lastBreak || $jy >= end($breaks)) {
            throw new \InvalidArgumentException('Invalid Persian/Jalali year : ' . $jy);
        }

        // Loop to determine leap years in the Jalali cycle
        for ($i = 1; $i < count($breaks); $i += 1) {
            $breakYear = $breaks[$i];
            $jump = $breakYear - $lastBreak;
            if ($jy < $breakYear) {
                break;
            }

            $leapJ = $leapJ +  intdiv($jump, 33) * 8 + intdiv($jump % 33, 4);
            $lastBreak = $breakYear;
        }

        // Calculate the leap years from the start of Jalali epoch to the current year
        $n = $jy - $lastBreak;
        $leapJ = $leapJ + intdiv($n, 33) * 8 + intdiv(($n % 33) + 3, 4);

        // Special adjustment
        if (($jump % 33) == 4 && ($jump - $n) == 4) {
            $leapJ += 1;
        }

        // Initialize Gregorian year and leap year count
        $gy = $jy + 621;

        // Calculate the leap years in the Gregorian calendar
        $leapG = intdiv($gy, 4) - intdiv((intdiv($gy, 100) + 1) * 3, 4) - 150;

        // differ days between year's first day of two calendar
        $day1f = 20 + $leapJ - $leapG;

        // determine this Persian/Jalali year is leap?
        if ($jump - $n < 6) {
            $n = $n - $jump + intdiv($jump + 4, 33) * 33;
        }
        $leap = (int)fmod(fmod($n + 1, 33) - 1, 4) == 0;

        // JDN of day 1 month 1 of Jalali year
        $jy_jdn1f = self::gregorian_to_jdn($gy, 3, $day1f);

        // Standard days in Persian/Jalali calendar
        $j_d_m = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        // Calculate the number of days for the current year
        $days_in_year = 0;

        // Add the days in the months of the current year
        for ($i = 0; $i < $jm - 1; $i++) {
            $days_in_year += $j_d_m[$i];
        }

        // Add given day
        $days_in_year += $jd - 1;

        return $jy_jdn1f + $days_in_year;
    }

    /**
     * Converts Julian Day Number to Persian/Jalali date.
     *
     * @access  private
     * @param   int     $jdn    Julian Day Number
     * @return  array   Persian date (year, month, day)
     */
    private static function jdn_to_persian($jdn)
    {
        $gDate = self::jdn_to_gregorian($jdn);
        $jLeap = false;
        $jy = $gDate['year'] - 621;

        jdn1f:
        // JDN of day 1 month 1 of Jalali year
        $jy_jdn1f = self::persian_to_jdn($jy, 1, 1, $jLeap);

        $remain_days = $jdn - $jy_jdn1f;
        if ($remain_days < 0) {
            $jy = $jy - 1;
            goto jdn1f;
        }

        if ($remain_days <= 185) {
            $jm = 1 + intdiv($remain_days, 31);
            $jd = ($remain_days % 31) + 1;
        } else {
            $remain_days = $remain_days - 186;
            $jm = 7 + intdiv($remain_days, 30);
            $jd = ($remain_days % 30) + 1;
        }

        return array(
            'year' => $jy,
            'month' => $jm,
            'day' => $jd,
            'leap' => $jLeap,
        );
    }

    /**
     * Converts a Gregorian date to a Jalali (Persian) date.
     *
     * @access  protected
     * @param   int $gy Gregorian year
     * @param   int $gm Gregorian month (1–12)
     * @param   int $gd Gregorian day (1–31)
     * @return  array   Jalali date
     */
    protected function gregorian_to_persian(int $gy, int $gm, int $gd)
    {
        // Adjust year if month greater than 12 or lesser than 1
        $gy = $gy + (($gm <= 0)? -1 : ($gm > 12 ? 1 : 0));
        // Adjust month
        $gm = (($gm + 12) % 12)?: 12;

        $jDate = self::jdn_to_persian(self::gregorian_to_jdn($gy, $gm, $gd));

        // start day of week, 0 = Sunday, 6 = Saturday
        $wday = (int)date('w', mktime(0, 0, 0, $gm, $gd, $gy));

        return array(
            'year'  => $jDate['year'],
            'month' => $jDate['month'],
            'day'   => $jDate['day'],
            'wday'  => $wday,
            'mday'  => ($jDate['month'] < 7)? 31 : (($jDate['month'] < 12)? 30 : ($jDate['leap']? 30 : 29)),
        );
    }

    /**
     * Converts a Jalali (Persian) date to a Gregorian date.
     *
     * @access  protected
     * @param   int $jy Jalali year
     * @param   int $jm Jalali month (1–12)
     * @param   int $jd Jalali day (1–31)
     * @return  array   Gregorian date
     */
    protected function persian_to_gregorian(int $jy, int $jm, int $jd): array
    {
        // Adjust year if month greater than 12 or lesser than 1
        $jy = $jy + (($jm <= 0)? -1 : ($jm > 12 ? 1 : 0));
        // Adjust month
        $jm = (($jm + 12) % 12)?: 12;

        $jLeap = false;
        $gDate = self::jdn_to_gregorian(self::persian_to_jdn($jy, $jm, $jd, $jLeap));

        // Days in Gregorian months
        $g_d_m = [31, $gDate['leap']? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        // start day of week, 0 = Sunday, 6 = Saturday
        $wday = (int)date('w', mktime(0, 0, 0, $gDate['month'], $gDate['day'], $gDate['year']));

        return array(
            'year'  => $gDate['year'],
            'month' => $gDate['month'],
            'day'   => $gDate['day'],
            'wday'  => $wday,
            'mday'  => $g_d_m[$gDate['month'] - 1],
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

        $date = $this->persian_to_gregorian((int)$year, (int)$month, (int)$day);
        $date = mktime((int)$hour, (int)$minute, (int)$second, $date['month'], $date['day'], $date['year']);
        return !empty($format)?
            date($format, $date) :
            array(
                'timestamp' => $date,
                'year'      => date("Y", $date),
                'month'     => date("m", $date),
                'day'       => date("d", $date),
                'hour'      => date("H", $date),
                'minute'    => date("i", $date),
                'second'    => date("s", $date),
                'mday'      => date("t", $date),
                'yearDay'   => date("z", $date),
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
        $wday   = $prdate['wday'];
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
                'wday'     => $this->DayString($wday),
                'month'   => $this->MonthString($month),
            );
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
                        $return.= $this->DayString($date['wday']);
                        $i+=3;
                    } else {
                        $return.= $this->DayShortString($date['wday']);
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
                        $return.= $this->MonthString($date['month']);
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