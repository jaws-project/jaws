<?php
/**
 * File cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2019 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_File extends Jaws_Cache
{
    /**
     * cache files prefix
     * @var     string  $cachePrefix
     * @access  private
     */
    private $cachePrefix = 'cache_';

    /**
     * cache files directory
     * @var     string  $cacheDirectory
     * @access  private
     */
    private $cacheDirectory;

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function __construct()
    {
        $this->cacheDirectory = rtrim(sys_get_temp_dir(), '/\\');
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
        $file = $this->cacheDirectory . '/'. $this->cachePrefix. $key;
        return empty($lifetime)? false : (bool)Jaws_Utils::file_put_contents($file, $value);
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
        $file = $this->cacheDirectory . '/'. $this->cachePrefix. $key;
        return @file_get_contents($file);
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
        $file = $this->cacheDirectory . '/'. $this->cachePrefix. $key;
        return Jaws_Utils::delete($file);
    }

}