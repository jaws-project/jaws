<?php
/**
 * Base class of cache drivers
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2019 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
            !file_exists(JAWS_PATH . "include/Jaws/Cache/{$cacheDriver}.php")
        ) {
            $GLOBALS['log']->Log(JAWS_LOG_ERR, "Loading '$cacheDriver' cache driver failed.");
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
     * @param   string  $key    key
     * @param   mixed   $value  value 
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($key, $value, $lifetime = 2592000)
    {
        return true;
    }

    /**
     * Get cached value of given key
     *
     * @access  public
     * @param   string  $key    key
     * @return  mixed   Returns key value
     */
    function get($key)
    {
        return false;
    }

    /**
     * Delete cached key
     *
     * @access  public
     * @param   string  $key    key
     * @return  mixed
     */
    function delete($key)
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

}