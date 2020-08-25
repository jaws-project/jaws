<?php
/**
 * Context keeps the template variables stack and resolves those
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_Context
{
    /**
     * Local scopes
     *
     * @var array
     */
    protected $assigns;

    /**
     * Registers for non-variable state data
     *
     * @var array
     */
    public $registers = array();

    /**
     * A map of all filters and the class that contain them (in the case of methods)
     *
     * @var array
     */
    private $filters = array();

    /**
     * Global scopes
     *
     * @var array
     */
    public $environments = array();

    /**
     * Constructor
     *
     * @param array $assigns
     * @param array $registers
     */
    public function __construct(array $assigns = array())
    {
        $this->assigns = array($assigns);
        $this->registers = array();

        $files = array_map('basename', glob(__DIR__ . '/Filters/*.php'));
        foreach ($files as $file) {
            $fileName = basename($file, '.php');
            $reflection = new \ReflectionClass("Jaws_XTemplate_Filters_$fileName");
            foreach ($reflection->getMethods(\ReflectionMethod::IS_STATIC) as $method) {
                $this->filters[$method->name] = $method->class;
            }
        }
    }

    /**
     * Adds a filter to the filters list
     *
     * @param   string      $filter     Filter name
     * @param   callable    $callback   Callable method
     *
     * @throws  Exception
     * @return  bool
     */
    public function addFilter($filter, callable $callback = null)
    {
        // If it is a callback, save it as it is
        if (is_string($filter) && $callback) {
            $this->filters[$filter] = $callback;
            return true;
        }

        // If it's a global function, register it simply
        if (is_string($filter) && function_exists($filter)) {
            $this->filters[$filter] = false;
            return true;
        }

        // it's a bad parameter
        throw new Exception(
            "Parameter passed to addFilter must be an object or a string"
        );

    }

    /**
     * Invokes the filter with the given name
     *
     * @param   string  $filter The name of the filter
     * @param   string  $value  The value to filter
     * @param   array   $args   The additional arguments for the filter
     *
     * @return string
     */
    public function invoke($filter, $value, array $args = array())
    {
        try {
            array_unshift($args, $value);

            // is filter name exists
            if (!array_key_exists($filter, $this->filters)) {
                return $value;
            }

            if (false === $mappedFunction = $this->filters[$filter]) {
                // filter function is global or native php method 
                return call_user_func_array($filter, $args);
            }

            // if callable
            if (is_callable($mappedFunction)) {
                return call_user_func_array($mappedFunction, $args);
            }

            // filter is part of a class/object
            return call_user_func_array(array($mappedFunction, $filter), $args);
        } catch (\TypeError $typeError) {
            throw new Exception($typeError->getMessage(), 0, $typeError);
        }
    }

    /**
     * Merges the given assigns into the current assigns
     *
     * @param array $newAssigns
     */
    public function merge($newAssigns)
    {
        $this->assigns[0] = array_merge($this->assigns[0], $newAssigns);
    }

    /**
     * return previous context reference
     *
     * @return  array
     */
    public function &parentContext()
    {
        return $this->assigns[1];
    }

    /**
     * Push new local scope on the stack.
     *
     * @return bool
     */
    public function push()
    {
        array_unshift($this->assigns, array());
        return true;
    }

    /**
     * Pops the current scope from the stack.
     *
     * @throws Exception
     * @return bool
     */
    public function pop()
    {
        if (count($this->assigns) == 1) {
            throw new Exception('No elements to pop');
        }

        array_shift($this->assigns);
    }

    /**
     * Replaces []
     *
     * @param string
     * @param mixed $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->resolve($key);
    }

    /**
     * Replaces []=
     *
     * @param string $key
     * @param mixed $value
     * @param bool $global
     */
    public function set($key, $value, $global = false)
    {
        if ($global) {
            for ($i = 0; $i < count($this->assigns); $i++) {
                $this->assigns[$i][$key] = $value;
            }
        } else {
            $this->assigns[0][$key] = $value;
        }
    }

    /**
     * Returns true if the given key will properly resolve
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key)
    {
        return (!is_null($this->resolve($key)));
    }

    /**
     * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
     *
     * Test for empty has been moved to interpret condition, in Decision
     *
     * @param string $key
     *
     * @throws Exception
     * @return mixed
     */
    private function resolve($key)
    {
        // This shouldn't happen
        if (is_array($key)) {
            throw new Exception("Cannot resolve arrays as key");
        }

        if (is_null($key) || $key == 'null') {
            return null;
        }

        if ($key == 'true') {
            return true;
        }

        if ($key == 'false') {
            return false;
        }

        if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^"(.*)"$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(-?\d+)$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(-?\d[\d\.]+)$/', $key, $matches)) {
            return $matches[1];
        }

        return $this->variable($key);
    }

    /**
     * Fetches the current key in all the scopes
     *
     * @param string $key
     *
     * @return mixed
     */
    private function fetch($key)
    {
        // TagDecrement depends on environments being checked before assigns
        if (array_key_exists($key, $this->environments)) {
            return $this->environments[$key];
        }

        foreach ($this->assigns as $scope) {
            if (array_key_exists($key, $scope)) {
                $obj = $scope[$key];
                return $obj;
            }
        }

        return null;
    }

    /**
     * Resolved the namespaced queries gracefully.
     *
     * @param string $key
     *
     * @see Decision::stringValue
     * @see AbstractBlock::renderAll
     *
     * @throws Exception
     * @return mixed
     */
    private function variable($key)
    {
        // Support numeric and variable array indicies
        if (preg_match("|\[[0-9]+\]|", $key)) {
            $key = preg_replace("|\[([0-9]+)\]|", ".$1", $key);
        } elseif (preg_match("|\[[0-9a-z._]+\]|", $key, $matches)) {
            $index = $this->get(str_replace(array("[", "]"), "", $matches[0]));
            if (strlen($index)) {
                $key = preg_replace("|\[([0-9a-z._]+)\]|", ".$index", $key);
            }
        }

        $parts = explode(Jaws_XTemplate::get('VARIABLE_ATTRIBUTE_SEPARATOR'), $key);

        $object = $this->fetch(array_shift($parts));

        while (count($parts) > 0) {
            // since we still have a part to consider
            // and since we can't dig deeper into plain values
            // it can be thought as if it has a property with a null value
            if (!is_object($object) && !is_array($object) && !is_string($object)) {
                return null;
            }

            if (is_null($object)) {
                return null;
            }

            $nextPartName = array_shift($parts);

            if (is_string($object)) {
                if ($nextPartName == 'size') {
                    // if the last part of the context variable is .size we return the string length
                    return mb_strlen($object);
                }

                // no other special properties for strings, yet
                return null;
            }

            if (is_array($object)) {
                // if the last part of the context variable is .first we return the first array element
                if ($nextPartName == 'first' && count($parts) == 0 && !array_key_exists('first', $object)) {
                    return Jaws_XTemplate_Filters_Default::first($object);
                }

                // if the last part of the context variable is .last we return the last array element
                if ($nextPartName == 'last' && count($parts) == 0 && !array_key_exists('last', $object)) {
                    return Jaws_XTemplate_Filters_Default::last($object);
                }

                // if the last part of the context variable is .size we just return the count
                if ($nextPartName == 'size' && count($parts) == 0 && !array_key_exists('size', $object)) {
                    return count($object);
                }

                // no key - no value
                if (!array_key_exists($nextPartName, $object)) {
                    return null;
                }

                $object = $object[$nextPartName];
                continue;
            }

            if (!is_object($object)) {
                // we got plain value, yet asked to resolve a part
                // think plain values have a null part with any name
                return null;
            }

            if ($object instanceof \Countable) {
                // if the last part of the context variable is .size we just return the count
                if ($nextPartName == 'size' && count($parts) == 0) {
                    return count($object);
                }
            }

            // if it has `get` or `field_exists` methods
            if (method_exists($object, Jaws_XTemplate::get('HAS_PROPERTY_METHOD'))) {
                if (!call_user_func(array($object, Jaws_XTemplate::get('HAS_PROPERTY_METHOD')), $nextPartName)) {
                    return null;
                }

                $object = call_user_func(array($object, Jaws_XTemplate::get('GET_PROPERTY_METHOD')), $nextPartName);
                continue;
            }

            // if it's just a regular object, attempt to access a public method
            if (is_callable(array($object, $nextPartName))) {
                $object = call_user_func(array($object, $nextPartName));
                continue;
            }

            // if a magic accessor method present...
            if (method_exists($object, '__get')) {
                $object = $object->$nextPartName;
                continue;
            }

            // Inexistent property is a null, PHP-speak
            if (!property_exists($object, $nextPartName)) {
                return null;
            }

            // then try a property (independent of accessibility)
            if (property_exists($object, $nextPartName)) {
                $object = $object->$nextPartName;
                continue;
            }

            // we'll try casting this object in the next iteration
        }

        return $object;
    }

}