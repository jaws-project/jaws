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
     * Cache driver
     * @access  private
     */
    var $_Driver;

    /**
     * An interface for available drivers
     *
     * @access  public
     */
    function &factory()
    {
        $this->_Driver = $this->Registry->fetch('cache_driver', 'Settings');
        $this->_Driver = preg_replace('/[^[:alnum:]_\-]/', '', $this->_Driver);
        $driverFile = JAWS_PATH . 'include/Jaws/Cache/'. $this->_Driver . '.php';
        if (!file_exists($driverFile)) {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loading cache driver $driverFile failed.");
            $this->_Driver = 'File';
            $driverFile = JAWS_PATH . 'include/Jaws/Cache/'. $this->_Driver . '.php';
        }

        include_once $driverFile;
        $className = 'Jaws_Cache_' . $this->_Driver;
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
    function set($key, &$value, $lifetime = 2592000)
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