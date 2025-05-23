<?php
/**
 * Template engine Array registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Array extends Jaws_XTemplate_Filters
{
    /*
    TODO:
        concat/merge
        where
    */
    /**
     * Array keys
     *
     * @param array $input
     *
     * @return array
     */
    public static function keys(array $input)
    {
        return array_keys($input);
    }

    /**
     * Array values
     *
     * @param array $input
     *
     * @return array
     */
    public static function values(array $input)
    {
        return array_values($input);
    }

    /**
     * Returns the first element of an array
     *
     * @param array|\Iterator $input
     *
     * @return mixed
     */
    public static function first($input)
    {
        if ($input instanceof \Iterator) {
            $input->rewind();
            return $input->current();
        }

        return is_array($input) ? reset($input) : $input;
    }

    /**
     * Returns the last element of an array
     *
     * @param array|\Traversable $input
     *
     * @return mixed
     */
    public static function last($input)
    {
        if ($input instanceof \Traversable) {
            $last = null;
            foreach ($input as $elem) {
                $last = $elem;
            }
            return $last;
        }

        return is_array($input) ? end($input) : $input;
    }

    /**
     * Returns the associated index element of an array
     *
     * @param   array   $input
     * @param   string  $index
     *
     * @return mixed
     */
    public static function index(array|null $input, $index)
    {
        if (is_array($input) && array_key_exists($index, $input)) {
            return $input[$index];
        }

        return null;
    }

    /**
     * Returns the associated index element of an array
     *
     * @param   string  $index
     * @param   array   $input
     *
     * @return mixed
     */
    public static function indexof($index, array|null $input)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if (is_array($input)) {
            if (is_array($index)) {
                return array_intersect_key($input, array_flip($index));
            } else {
                if (array_key_exists($index, $input)) {
                    return $input[$index];
                }
            }
        }

        return null;
    }

    /**
     * Joins elements of an array with a given character between them
     *
     * @param array|\Traversable $input
     * @param string $glue
     *
     * @return string
     */
    public static function join($input, $glue = ',')
    {
        if ($input instanceof \Traversable) {
            $str = '';
            foreach ($input as $elem) {
                if ($str) {
                    $str .= $glue;
                }
                $str .= $elem;
            }
            return $str;
        }

        return is_array($input) ? implode($glue, $input) : $input;
    }

    /**
     * Push one or more elements onto the end of array
     *
     * @param   array|\Traversable  $input
     * @param   mixed   $values
     *
     * @return array
     */
    public static function push($input, ...$values)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        array_push($input, ...$values);
        return $input;
    }

    /**
     * Pop the element off the end of array
     *
     * @param   array|\Traversable  $input
     *
     * @return array
     */
    public static function pop($input)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        array_pop($input);
        return $input;
    }

    /**
     * Merge one or more arrays
     *
     * @param array|\Traversable $input
     * @param array|\Traversable $property
     *
     * @return array
     */
    public static function merge(...$inputs)
    {
        $result = array();
        foreach ($inputs as &$input) {
            if ($input instanceof \Traversable) {
                $input = iterator_to_array($input);
            }
            if (!is_array($input)) {
                break;
            }

            $result = array_merge($result, $input);
        }

        return $result;
    }

    /**
     * Map/collect on a given property
     *
     * @param array|\Traversable $input
     * @param string $property
     *
     * @return array
     */
    public static function map($input, $property)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if (!is_array($input)) {
            return $input;
        }

        return array_map(function ($elem) use ($property) {
            if (is_callable($elem)) {
                return $elem();
            } elseif (is_array($elem) && array_key_exists($property, $elem)) {
                return $elem[$property];
            }
            return null;
        }, $input);
    }


    /**
     * Group array by given property
     *
     * @param   array|\Traversable  $input
     * @param   string|array        $properties
     *
     * @return array
     */
    public static function groupby($input, $properties)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if (!is_array($input)) {
            return $input;
        }

        $result = array();
        foreach ($input as $element) {
            if (!is_array($properties)) {
                $groupkey = $element[$properties];
            } else {
                $groupkey = json_encode(array_map(function($property) use ($element) { return $element[$property]; }, $properties));
            }
            $result[$groupkey][] = $element;
        }

        return $result;
    }

    /**
     * Filter array by specific property value
     *
     * @param array|\Traversable $input
     * @param string    $property
     * @param mixed     $value
     * @param bool      $logic
     * @param bool      $strict case sensitive compare
     *
     * @return mixed    filtered array if success or given input on failure
     */
    public static function filter($input, $property = null, $value = null, $logic = true, $strict = true)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if (!is_array($input)) {
            return $input;
        }

        $condition = array($property, $value, $logic, $strict);
        return array_filter(
            $input,
            function ($elem) use ($condition) {
                $key = $condition[0];
                $val = $condition[1];
                $logic = $condition[2];
                $strict = $condition[3];
                // check key exist in sub-dimensions
                if (!is_null($key)) {
                    $keys = explode('.', $key);
                    foreach ($keys as $level => $key) {
                        if ($key === '') {
                            return !empty(self::filter($elem, implode('.', array_slice($keys, $level + 1)), $val, $logic, $strict));
                        }

                        if (!array_key_exists($key, $elem)) {
                            return false;
                        }
                        $elem = $elem[$key];
                    }
                }

                if (is_null($val)) {
                    return empty($elem)? !$logic : $logic;
                }
                if (is_array($val) && is_array($elem)) {
                    if ($strict) {
                        return empty(array_intersect($val, $elem))? !$logic : $logic;
                    }

                    return empty(
                        array_intersect(
                            array_map('strtolower', $val),
                            array_map('strtolower', $elem)
                        ))? !$logic : $logic;
                }
                if (is_array($val)) {
                    if ($strict) {
                        return in_array($elem, $val)? $logic : !$logic;
                    }
                    return in_array(Jaws_UTF8::strtolower($elem), array_map('strtolower', $val))? $logic : !$logic;
                }
                if (is_array($elem)) {
                    if ($strict) {
                        return in_array($val, $elem)? $logic : !$logic;
                    }
                    return in_array(Jaws_UTF8::strtolower($val), array_map('strtolower', $elem))? $logic : !$logic;
                }
                if ($strict) {
                    return ($elem == $val)? $logic : !$logic;
                }
                return (Jaws_UTF8::strtolower($elem) == Jaws_UTF8::strtolower($val))? $logic : !$logic;
            }
        );
    }

    /**
     * Reverse the elements of an array
     *
     * @param array|\Traversable $input
     *
     * @return array
     */
    public static function reverse($input)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        return array_reverse($input);
    }

    /**
     * Sort the elements of an array
     *
     * @param array|\Traversable $input
     * @param string $property use this property of an array element
     *
     * @return array
     */
    public static function sort($input, $property = null)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if ($property === null) {
            asort($input);
        } else {
            $first = reset($input);
            if ($first !== false && is_array($first) && array_key_exists($property, $first)) {
                uasort($input, function ($a, $b) use ($property) {
                    if ($a[$property] == $b[$property]) {
                        return 0;
                    }

                    return $a[$property] < $b[$property] ? -1 : 1;
                });
            }
        }

        return $input;
    }

    /**
     * Remove duplicate elements from an array
     *
     * @param array|\Traversable $input
     *
     * @return array
     */
    public static function uniq($input)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        return array_unique($input);
    }

    /**
     * Exchanges all keys with their associated values in an array
     *
     * @param array|\Traversable $input
     *
     * @return array
     */
    public static function flip($input, $safe = false)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        if ($safe) {
            return array_reduce(array_keys($input), function ($carry, $key) use (&$input) {
                $carry[$input[$key]] = $carry[$input[$key]] ?? [];
                $carry[$input[$key]][] = $key;
                return $carry;
            }, []);
        } else {
            return array_flip($input);
        }
    }

    /**
     *  Counts values of an array
     *
     * @param array|\Traversable $input
     * @param string $property use this property of an array element
     *
     * @return array
     */
    public static function count_values($input, $property = null)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }
        if (!is_array($input)) {
            return $input;
        }

        $array_counts = array_count_values($input);
        return is_null($property)? $array_counts : (array_key_exists($property, $array_counts)? $array_counts[$property] : 0);
    }

}