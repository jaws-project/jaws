<?php
/**
 * Base class of cache drivers
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
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
            $cacheDriver = $GLOBALS['app']->Registry->fetch('cache_driver', 'Settings');
        }
        $cacheDriver = preg_replace('/[^[:alnum:]_-]/', '', $cacheDriver);

        $cacheDriverFile = JAWS_PATH . 'include/Jaws/Cache/'. $cacheDriver .'.php';
        if (!file_exists($cacheDriverFile)) {
            return Jaws_Error::raiseError(
                "Loading '$cacheDriver' cache driver failed.",
                __FUNCTION__
            );
        }

        $className = 'Jaws_Cache_' . $cacheDriver;
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
        return false;
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
        return null;
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
     * Delete all expired cached data
     *
     * @access  public
     */
    function delete_expired()
    {
        return true;
    }

}