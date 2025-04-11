<?php
/**
 * Class to manage Gregorian calendar
 *
 * @category    Jaws_Date
 * @package     Core
 * @author      Amir Mohammad Saied <amir@php.net>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Date_Gregorian extends Jaws_Date
{
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

        $date = mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
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
                'wday'    => $this->DayString($date['wday']),
                'month'   => $this->MonthString($date['mon']),
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
                    case 'T':
                        $return.= date('U', $date) * 1000;
                        break;

                    case 's':
                        if (substr($format, $i, 2) === 'ss') {
                            $return.= date('s', $date);
                            $i++;
                        } else {
                            $return.= intval(date('s', $date));
                        }
                        break;

                    case 'm':
                        if (substr($format, $i, 2) === 'mm') {
                            $return.= date('i', $date);
                            $i++;
                        } else {
                            $return.= intval(date('i', $date));
                        }
                        break;

                    case 'h':
                        if (substr($format, $i, 2) === 'hh') {
                            $return.= date('h', $date);
                            $i++;
                        } else {
                            $return.= date('g', $date);
                        }
                        break;

                    case 'H':
                        if (substr($format, $i, 2) === 'HH') {
                            $return.= date('H', $date);
                            $i++;
                        } else {
                            $return.= date('G', $date);
                        }
                        break;

                    case 'a':
                        if (substr($format, $i, 3) == 'ago') {
                            $return .= $this->SinceFormat($date);
                            $i = $i + 2;
                        } elseif (substr($format, $i, 2) === 'aa') {
                            $return.= date('a', $date);
                            $i++;
                        }
                        break;

                    case 'A':
                        if (substr($format, $i, 3) == 'AGO') {
                            $return .= $this->SinceFormat($date);
                            $i = $i + 2;
                        } elseif (substr($format, $i, 2) === 'AA') {
                            $return.= date('A', $date);
                            $i++;
                        }
                        break;

                    case 'E':
                        if (substr($format, $i, 4) === 'EEEE') {
                            $return.= $this->DayString(date('w', $date));
                            $i+=3;
                        } else {
                            $return.= $this->DayShortString(date('w', $date));
                        }
                        break;

                    case 'd':
                        if (substr($format, $i, 2) === 'dd') {
                            $return.= date('d', $date);
                            $i++;
                        } else {
                            $return.= date('j', $date);
                        }
                        break;

                    case 'M':
                        if (substr($format, $i, 4) === 'MMMM') {
                            $return.= $this->MonthString(date('m', $date));
                            $i+=3;
                        } elseif (substr($format, $i, 3) === 'MMM') {
                            $return.= $this->MonthShortString(date('m', $date));
                            $i+=2;
                        } elseif (substr($format, $i, 2) === 'MM') {
                            $return.= date('m', $date);
                            $i++;
                        } else {
                            $return.= date('n', $date);
                        }
                        break;

                    case 'y':
                        if (substr($format, $i, 4) === 'yyyy') {
                            $return.= date('Y', $date);
                            $i+=3;
                        } elseif (substr($format, $i, 2) === 'yy') {
                            $return.= date('y', $date);
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
                Jaws::t('GREGORIAN_MONTH_0'),
                Jaws::t('GREGORIAN_MONTH_1'),
                Jaws::t('GREGORIAN_MONTH_2'),
                Jaws::t('GREGORIAN_MONTH_3'),
                Jaws::t('GREGORIAN_MONTH_4'),
                Jaws::t('GREGORIAN_MONTH_5'),
                Jaws::t('GREGORIAN_MONTH_6'),
                Jaws::t('GREGORIAN_MONTH_7'),
                Jaws::t('GREGORIAN_MONTH_8'),
                Jaws::t('GREGORIAN_MONTH_9'),
                Jaws::t('GREGORIAN_MONTH_10'),
                Jaws::t('GREGORIAN_MONTH_11'),
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
                Jaws::t('GREGORIAN_MONTH_SHORT_0'),
                Jaws::t('GREGORIAN_MONTH_SHORT_1'),
                Jaws::t('GREGORIAN_MONTH_SHORT_2'),
                Jaws::t('GREGORIAN_MONTH_SHORT_3'),
                Jaws::t('GREGORIAN_MONTH_SHORT_4'),
                Jaws::t('GREGORIAN_MONTH_SHORT_5'),
                Jaws::t('GREGORIAN_MONTH_SHORT_6'),
                Jaws::t('GREGORIAN_MONTH_SHORT_7'),
                Jaws::t('GREGORIAN_MONTH_SHORT_8'),
                Jaws::t('GREGORIAN_MONTH_SHORT_9'),
                Jaws::t('GREGORIAN_MONTH_SHORT_10'),
                Jaws::t('GREGORIAN_MONTH_SHORT_11'),
            );
            $this->_Months['short'] =& $months;
        }

        if ($m = (int)$m) {
            return $this->_Months['short'][$m - 1];
        }

        return $this->_Months['short'];
    }

}