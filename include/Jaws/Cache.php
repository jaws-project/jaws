<?php
/**
 * Base class of cache drivers
 *
 * @category    Cache
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache
{
    /**
     * An interface for available drivers
     *
     * @access  public
     * @param   string  $cacheDriver    Cache Driver name
     * @return  mixed   Cache driver object on success otherwise Jaws_Error on failure
     */
    static function &factory($cacheDriver = '')
    {
        if (empty($cacheDriver)) {
            $cacheDriver = Jaws::getInstance()->registry->fetch('cache_driver', 'Settings');
        }
        $cacheDriver = preg_replace('/[^[:alnum:]_\-]/', '', $cacheDriver);

        if (!empty($cacheDriver) &&
            !file_exists(ROOT_JAWS_PATH . "include/Jaws/Cache/{$cacheDriver}.php")
        ) {
            $GLOBALS['log']->Log(JAWS_ERROR, "Loading '$cacheDriver' cache driver failed.");
            $cacheDriver = '';
        }

        $className = empty($cacheDriver)? 'Jaws_Cache' : "Jaws_Cache_$cacheDriver";
        $obj = new $className();
        return $obj;
    }

    /**
     * Store value of given key
     *
     * @access  public
     * @param   int     $key    key
     * @param   mixed   $value  value
     * @param   bool    $serialize
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($key, $value, $serialize = false, $lifetime = 2592000)
    {
        return true;
    }

    /**
     * Get cached value of given key
     *
     * @access  public
     * @param   int     $key    key
     * @param   bool    $unserialize
     * @return  mixed   Returns key value
     */
    function get($key, $unserialize = false)
    {
        return false;
    }

    /**
     * Delete cached key
     *
     * @access  public
     * @param   int     $key    key
     * @return  mixed
     */
    function delete($key)
    {
        return true;
    }

    /**
     * Checks is cached key exists
     *
     * @access  public
     * @param   int     $key    key
     * @return  bool
     */
    function exists($key)
    {
        return true;
    }

    /**
     * Get cache key
     *
     * @access  public
     * @param   mixed   $params
     * @return  int     Returns cache key
     */
    static function key($params)
    {
        return Jaws_Utils::ftok(serialize(func_get_args()));
    }

    /**
     * Delete expired cached keys
     *
     * @access  public
     * @return  mixed
     */
    function deleteExpiredKeys()
    {
        return true;
    }

}