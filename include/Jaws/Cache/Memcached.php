<?php
/**
 * Memcached cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_Memcached extends Jaws_Cache
{
    /**
     * Memcached object
     * @access  private
     */
    private $memcache;

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function __construct()
    {
        // initializing driver
        $this->memcache = new Memcache;
        $this->memcache->connect('localhost', 11211);
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
        if ($serialize) {
            $value = serialize($value);
        }

        return empty($lifetime)? false : $this->memcache->set($key, $value, 0, $lifetime);
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
        if ($unserialize) {
            return @unserialize($this->memcache->get($key));
        }

        return $this->memcache->get($key);
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
        return $this->memcache->delete($key);
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
        return $this->memcache->get($key) !== false;
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