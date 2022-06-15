<?php
/**
 * Class to manage Gregorian calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Amir Mohammad Saied <amir@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date_Gregorian extends Jaws_Date
{
    /**
     * Gets count of Month(s) days
     *
     * @access  public
     * @param   int     $year   Gregorian year
     * @param   int     $month  Gregorian month
     * @return  mixed   Count of Month days or array of count all months days 
     */
    function MonthDays($year, $month = 0)
    {
        $result = $this->_GregorianDaysInMonthes;
        if ($this->_IsLeapYear($year)) {
            $result[1]++;
        }
        return empty($month)? $result : $result[$month-1];
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

        $dt = mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
        return !empty($format)? date($format, $dt) :
                                array('timestamp' => $dt,
                                      'year'      => date("Y", $dt),
                                      'month'     => date("m", $dt),
                                      'day'       => date("d", $dt),
                                      'hour'      => date("H", $dt),
                                      'minute'    => date("i", $dt),
                                      'second'    => date("s", $dt),
                                      'monthDays' => date("t", $dt),
                                      'yearDay'   => date("z", $dt)
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
            @list($year, $month, $day, $hour, $minute, $second) = func_get_arg(0);
            $date = mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
        } elseif (func_num_args() > 1) {
            $date = mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
        } else { // unix timestamp
            $date = (int)$year;
        }

        $date = getdate($date);
        return array(
                'seconds' => str_pad($date['seconds'], 2, '0', STR_PAD_LEFT),
                'minutes' => str_pad($date['minutes'], 2, '0', STR_PAD_LEFT),
                'hours'   => str_pad($date['hours'],   2, '0', STR_PAD_LEFT),
                'mday'    => str_pad($date['mday'],    2, '0', STR_PAD_LEFT),
                'wday'    => $date['wday'],
                'mon'     => str_pad($date['mon'],  2, '0', STR_PAD_LEFT),
                'year'    => str_pad($date['year'], 4, '0', STR_PAD_LEFT),
                'yday'    => $date['yday'],
                'weekday' => $this->DayString($date['wday']),
                'month'   => $this->MonthString($date['mon']),
            );
    }

    /**
     * Format the input date.
     *
     * @access  public
     * @param   string  $date   Date string
     * @param   string  $format Format to use
     * @return  string  The original date with a new format
     */
    function Format($date, $format = null)
    {
        if (empty($date)) {
            return '';
        }

        $date = Jaws::getInstance()->UTC2UserTime($date);

        if (empty($format)) {
            $format = Jaws::getInstance()->registry->fetch('date_format', 'Settings');
        }

        if ($format == 'since') {
            return $this->SinceFormat($date);
        } else {
            $i = 0; 
            $return = '';
            while ($i < strlen($format)) {
                switch($format[$i]) {
                case 'A':
                    if (substr($format, $i, 3) == 'AGO') {
                        $return .= $this->SinceFormat($date);
                        $i = $i + 2;
                    }
                    break;
                case 'F':
                    $return .= $this->DayString(date('w', $date));
                    break;
                case 'D':
                    if (substr($format, $i, 2) == 'DN') {
                        $return .= $this->DayString(date('w', $date));
                        $i++;
                    } else {
                        $return .= $this->DayShortString(date('w', $date));
                    }
                    break;
                case 'l':
                    $return .= $this->DayString(date('w', $date));
                    break;
                case 'M':
                    if (substr($format, $i, 2) == 'MN') {
                        $return .= $this->MonthString(date('m', $date));
                        $i++;
                    } else {
                        $return .= $this->MonthShortString(date('m', $date));
                    }
                    break;
                case '\\':
                    // Do nothing 
                    break;
                default:
                    if (substr($format, $i - 1, 1) == '\\') {
                        $return .= $format[$i];
                    } else {
                        $return .= date($format[$i], $date);
                    }
                    break;
                }
                $i++;
            }

            return $return;
                 
        }
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
                Jaws::t('GREGORIAN_MONTH_FIRST'),
                Jaws::t('GREGORIAN_MONTH_SECOND'),
                Jaws::t('GREGORIAN_MONTH_THIRD'),
                Jaws::t('GREGORIAN_MONTH_FOURTH'),
                Jaws::t('GREGORIAN_MONTH_FIFTH'),
                Jaws::t('GREGORIAN_MONTH_SIXTH'),
                Jaws::t('GREGORIAN_MONTH_SEVENTH'),
                Jaws::t('GREGORIAN_MONTH_EIGHTH'),
                Jaws::t('GREGORIAN_MONTH_NINTH'),
                Jaws::t('GREGORIAN_MONTH_TENTH'),
                Jaws::t('GREGORIAN_MONTH_ELEVENTH'),
                Jaws::t('GREGORIAN_MONTH_TWELFTH'),
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
                Jaws::t('GREGORIAN_MONTH_SHORT_FIRST'),
                Jaws::t('GREGORIAN_MONTH_SHORT_SECOND'),
                Jaws::t('GREGORIAN_MONTH_SHORT_THIRD'),
                Jaws::t('GREGORIAN_MONTH_SHORT_FOURTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_FIFTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_SIXTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_SEVENTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_EIGHTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_NINTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_TENTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_ELEVENTH'),
                Jaws::t('GREGORIAN_MONTH_SHORT_TWELFTH'),
            );
            $this->_Months['short'] =& $months;
        }

        if ($m = (int)$m) {
            return $this->_Months['short'][$m - 1];
        }

        return $this->_Months['short'];
    }

}