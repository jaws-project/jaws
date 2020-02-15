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
     * @param   string  $key    key
     * @param   mixed   $value  value 
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($key, $value, $lifetime = 2592000)
    {
        return empty($lifetime)? false : $this->memcache->set($key, $value, 0, $lifetime);
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
        return $this->memcache->get($key);
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
        return $this->memcache->delete($key);
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