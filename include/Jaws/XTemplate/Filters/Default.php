<?php
/**
 * Template engine default registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Default
{
    /**
     * Formats a date using Jaws_Date::Format
     *
     * @param mixed $input
     * @param string $format
     *
     * @return string
     */
    public static function date($input, $format = '', $calendar = '')
    {
        return Jaws_Date::getInstance($calendar)->Format($input, $format);
    }

    /**
     * Default
     *
     * @param   string    $input
     * @param   string    $default_value
     *
     * @return  string
     */
    public static function default($input, $default_value)
    {
        return empty($input)? $default_value : $input;
    }

    /**
     * Determine input is different than NULL
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     Is set result
     * @param   mixed   $falseResult    Is not set result
     *
     * @return  mixed
     */
    public static function isset($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return isset($input)? $trueResult : $falseResult;
    }

    /**
     * Determine input is empty(equal PHP empty function)
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     Is empty result
     * @param   mixed   $falseResult    Is not empty result
     *
     * @return  mixed
     */
    public static function empty($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return empty($input)? $trueResult : $falseResult;
    }

    /**
     * check statement result is true?
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     If true result
     * @param   mixed   $falseResult    If false result
     *
     * @return  bool
     */
    public static function true($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return (bool)$input? $trueResult : $falseResult;
    }

    /**
     * check statement result is false?
     *
     * @param   mixed   $input
     * @param   mixed   $trueResult     If true result
     * @param   mixed   $falseResult    If false result
     *
     * @return  bool
     */
    public static function false($input, $trueResult = null, $falseResult = null)
    {
        $trueResult = isset($trueResult)? $trueResult : true;
        $falseResult = isset($falseResult)? $falseResult : false;

        return !(bool)$input? $trueResult : $falseResult;
    }

    /**
     * equal
     *
     * @param   mixed   $input1
     * @param   mixed   $input2
     * @param   mixed   $yesResult
     * @param   mixed   $noResult
     *
     * @return  mixed
     */
    public static function equal($input1, $input2, $yesResult, $noResult = null)
    {
        return $input1 == $input2 ? $yesResult : $noResult;
    }

    /**
     * Pseudo-filter: negates auto-added escape filter
     *
     * @param string $input
     *
     * @return string
     */
    public static function raw($input)
    {
        return $input;
    }

    /**
     * Return the size of an array or of an string
     *
     * @param   mixed   $input
     * @return int
     */
    public static function size($input)
    {
        if ($input instanceof \Iterator) {
            return iterator_count($input);
        }

        if (is_array($input)) {
            return count($input);
        }

        if (is_object($input)) {
            if (method_exists($input, 'size')) {
                return $input->size();
            }

            if (!method_exists($input, '__toString')) {
                $class = get_class($input);
                throw new Exception("Size of $class cannot be estimated: it has no method 'size' nor can be converted to a string");
            }
        }

        // only plain values and string-able objects left at this point
        return strlen($input);
    }

    /**
     * Return a PHP value from a stored representation
     *
     * @param   mixed   $input  The value to be serialized
     * @return  string
     */
    public static function serialize($input)
    {
        return serialize($input);
    }

    /**
     * Return a PHP value from a stored representation
     *
     * @param   string  $input  The serialized string
     * @return  mixed
     */
    public static function unserialize($input)
    {
        return @unserialize($input);
    }

    /**
     * Checks if input statement contains given value
     *
     * @param   mixed   $input      The array|string to search in
     * @param   mixed   $needle     The searched value
     * @param   mixed   $column_key The column of values to search(int|string|null)
     * @return  bool    Returns true if needle is found, false otherwise
     */
    public static function contains($input, $needle, $column_key = null)
    {
        if (!isset($input)) {
            return false;
        }

        if (is_array($input)) {
            return in_array($needle, is_null($column_key)? $input : array_column($input, $column_key));
        } else {
            return strpos($input, $needle) !== false;
        }
    }

    /**
     * Get map version of url if found
     *
     * @param   string   $args      Map arguments(comma seprated)
     * @param   string   $gadget    Gadget name
     * @param   string   $action    Action name
     * @param   array    $params    Map parameters
     *
     * @return string
     */
    public static function urlmap($gadget, $action, ...$params)
    {
        $pairs = array_chunk(array_pad($params, round(count($params)/2)*2, null), 2);
        $params = array_combine(array_column($pairs, 0), array_column($pairs, 1));

        return Jaws::getInstance()->map->GetMappedURL(
            $gadget,
            $action,
            $params
        );
    }

    /**
     * Get data url of given file/path
     *
     * @param   string  $suffix     suffix part of url
     * @param   bool    $rel_url    relative url
     * @param   bool    $base_data  use JAWS_BASE_DATA instead of ROOT_DATA_PATH
     *
     * @return string
     */
    public static function dataURL($suffixt = '', $rel_url = true, $base_data = false)
    {
        return Jaws::getInstance()->getDataURL($suffixt, $rel_url, $base_data);
    }

    /**
     * get session variables/attributes(user, global, gadget)
     *
     * @param   string    $var
     * @param   string    $component
     *
     * @return  string
     */
    public static function session($var, $component = '')
    {
        if (empty($component)) {
            if (false !== $property = strstr($var, '.')) {
                $var = strstr($var, '.', true);
                $property = substr($property, 1);
                $objKey = Jaws::getInstance()->session->$var;
                if (is_object($objKey) && property_exists($objKey, $property)) {
                    return Jaws::getInstance()->session->$var->$property;
                }

                return null;
            }

            return Jaws::getInstance()->session->$var;
        } else {
            return Jaws::getInstance()->session->getAttribute($var, $component);
        }
    }

    /**
     * get global variables(theme_url, data_url, .dir, .browser, ...)
     *
     * @param   string    $var
     *
     * @return  string
     */
    public static function global($var)
    {
        static $globalVariables = array();
        if (empty($globalVariables)) {
            $thisApp = Jaws::getInstance();
            $globalVariables['.dir']     = Jaws::t('LANG_DIRECTION') == 'rtl'? '.rtl' : '';
            $globalVariables['base_url'] = Jaws_Utils::getBaseURL('/');
            $globalVariables['requested_url'] = Jaws_Utils::getRequestURL();
            $globalVariables['base_script']   = BASE_SCRIPT;
            $globalVariables['data_url']    = $thisApp->getDataURL();
            $globalVariables['main_index']  = $thisApp->mainIndex? 'index' : '';
            $globalVariables['main_gadget'] = strtolower($thisApp->mainRequest['gadget']);
            $globalVariables['main_action'] = strtolower($thisApp->mainRequest['action']);
            // browser flag
            $browser = $thisApp->GetBrowserFlag();
            $globalVariables['.browser'] = empty($browser)? '' : ".$browser";
            // theme
            $theme = $thisApp->GetTheme();
            $globalVariables['theme_url'] = $theme['url'];
            // layout
            $layout = $thisApp->layout->GetLayoutName();
            $layout = @is_dir($theme['path']. '/'. $layout)? $layout : '';
            $globalVariables['main_layout'] = strtolower(str_replace('.', '_', $layout));
        }

        return array_key_exists($var, $globalVariables)? $globalVariables[$var] : '';
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string   $input
     *
     * @return string
     */
    public static function t($input)
    {
        $args = func_get_args();
        array_shift($args);

        return Jaws_Translate::getInstance()->Translate(
            null,
            strtoupper(str_replace(array(' ', '.'), '_', $input)),
            $args
        );
    }

    /**
     * prints a variable in a human readable form to the jaws log specified
     *
     * @param   mixed   $input
     *
     * @return string
     */
    public static function log($input)
    {
        _log_var_dump($input);
        return '';
    }

}