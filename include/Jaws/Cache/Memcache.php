<?php
/**
 * Memcache cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_Memcache extends Jaws_Cache
{
    /**
     * Memcache object
     * @access  private
     */
    var $_memcache;

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function Jaws_Cache_File()
    {
        // initializing driver
        $this->_memcache = new Memcache;
        $this->_memcache->connect('localhost', 11211);
    }

    /**
     * Store cache of given component/section
     *
     * @access  public
     * @param   string  $component
     * @param   string  $section
     * @param   string  $params
     * @param   $data
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($component, $section, $params, &$data, $lifetime = 0)
    {
        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        $key = $component. '.'. $section. (empty($params)? '' : ('.'. $params));
        return $this->_memcache->set($key, $data, 0, $lifetime);
    }

    /**
     * Get cached data of given component/section
     *
     * @access  public
     * @param   string  $component
     * @param   string  $section
     * @param   string  $params
     * @return  mixed
     */
    function get($component, $section, $params = null)
    {
        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        $key = $component. '.'. $section. (empty($params)? '' : ('.'. $params));
        $res = $this->_memcache->get($key);
        return ($res === false) ? null : $res;
    }

    /**
     * Delete cached data of given component/section
     *
     * @access  public
     * @param   string  $component
     * @param   string  $section
     * @param   string  $params
     * @return  mixed
     */
    function delete($component = null, $section = null, $params = null)
    {
        if (is_null($component)) {
            return $this->_memcache->flush();
        }

        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        $key = $component. '.'. $section. (empty($params)? '' : ('.'. $params));
        return $this->_memcache->delete($key);
    }

}