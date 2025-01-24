<?php
/**
 * Template engine date/time registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Date extends Jaws_XTemplate_Filters
{
    /**
     * Formats UTC timestamp to string using Jaws_Date::Format
     *
     * @param   int     $input
     * @param   string  $format
     * @param   string  $calendar
     *
     * @return string
     */
    public static function date2str($input, $format = '', $calendar = '')
    {
        return Jaws_Date::getInstance($calendar)->Format($input, $format, false);
    }

    /**
     * Convert datetime string to UTC timestamp
     *
     * @param   string|array    $input
     * @param   string          $calendar
     *
     * @return int
     */
    public static function str2date($input, $calendar = '')
    {
        $result = null;
        if (!empty($input)) {
            $result = is_array($input)? $input : [$input];
            foreach ($result as $key => $val) {
                if (empty($val)) {
                    unset($result[$key]);
                    continue;
                }
                if ($val == 'now') {
                    $result[$key] = time();
                } else {
                    $result[$key] = (int)Jaws_Date::getInstance($calendar)->ToBaseDate(preg_split('/[\/\- \:]/', $val), 'U');
                }
                $result[$key] = Jaws::getInstance()->UserTime2UTC($result[$key]);
            }

            $result = empty($result)? null : (is_array($input)? $result : $result[0]);
        }

        return $result;
    }

    /**
     * Formats time to string
     *
     * @param   int     $input  seconds since midnight
     * @param   string  $format
     *
     * @return string
     */
    public static function time2str($input, $format = '')
    {
        return Jaws_Date::getInstance()->Format($input, $format, false);
    }

    /**
     * Convert time string to seconds since midnight
     *
     * @param   string|array    $input  time string (for example: 13:45)
     *
     * @return int
     */
    public static function str2time($input)
    {
        $result = 0;
        if (!empty($input)) {
            $result = is_array($input)? $input : [$input];
            $time = new Jaws_Regexp('/([0-9]+)\:([0-9]+)(?:\:([0-9]+))?\s*([ap]m)?/i');
            foreach ($result as $key => $val) {
                if (false === $time->match($val)) {
                    $result[$key] = 0;
                    continue;
                }

                @list($all, $hours, $minutes, $seconds, $meridiem) = $time->matches;
                if (isset($meridiem) && $meridiem == 'pm' && $hours != 12) {
                    $hours = $hours + 12;
                }
                $result[$key] = $hours*3600 + $minutes*60 + $seconds;
                $result[$key] = Jaws::getInstance()->UserTime2UTC($result[$key]);
            }

            $result = is_array($input)? $result : $result[0];
        }

        return $result;
    }

    /**
     * UTC to local date/time
     *
     * @param   int $input  time timestamp
     *
     * @return int
     */
    public static function utc2local($input)
    {
        return Jaws_Date::getInstance()->utc2local($input);
    }

    /**
     * local date/time to UTC 
     *
     * @param   int $input  time timestamp
     *
     * @return int
     */
    public static function local2utc($input)
    {
        return Jaws_Date::getInstance()->local2utc($input);
    }

}