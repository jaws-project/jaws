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
     * @param   string  $input
     * @param   string  $calendar
     *
     * @return int
     */
    public static function str2date($input, $calendar = '')
    {
        $result = 0;
        if (!empty($input)) {
            $result = (int)Jaws_Date::getInstance($calendar)->ToBaseDate(preg_split('/[\/\- \:]/', $input), 'U');
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
     * @param   string  $input  time string (for example: 13:45)
     *
     * @return int
     */
    public static function str2time($input)
    {
        $time = new Jaws_Regexp('/([0-9]+)\:([0-9]+)(?:\:([0-9]+))?\s*([ap]m)?/i');
        if (false === $time->match($input)) {
            return 0;
        }
        @list($all, $hours, $minutes, $seconds, $meridiem) = $time->matches;
        if (isset($meridiem) && $meridiem == 'pm' && $hours != 12) {
            $hours = $hours + 12;
        }

        return $hours*3600 + $minutes*60 + $seconds;
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