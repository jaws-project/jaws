<?php
/**
 * File cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_File extends Jaws_Cache
{
    /**
     * Cache path
     * @access  private
     */
    var $_path = '';

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function __construct()
    {
        // initializing driver
        $this->_path = JAWS_CACHE;
    }

    /**
     * Store cache of given component/section
     *
     * @access  public
     */
    function set($component, $section, $params, &$data, $lifetime = 0)
    {
        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        $file = $this->_path. $component. '.'. $section. (empty($params)? '' : ('.'. $params));
        return (bool) Jaws_Utils::file_put_contents($file, $data);
    }

    /**
     * Get cached data of given component/section
     *
     * @access  public
     * @param   string  $component
     * @param   string  $section
     * @param   string  $params
     * @return  string
     */
    function get($component, $section, $params = null)
    {
        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        $file = $this->_path. $component. '.'. $section. (empty($params)? '' : ('.'. $params));
        $res = @file_get_contents($file);
        return ($res === false) ? null : $res;
    }

    /**
     * Delete cached data of given component/section
     *
     * @access  public
     * @param   string  $component
     * @param   string  $section
     * @param   string  $params
     * @return  bool
     */
    function delete($component = null, $section = null, $params = null)
    {
        if (!is_null($params)) {
            $params = is_array($params)? implode('_', $params) : $params;
        } else {
            $params = '';
        }

        require_once PEAR_PATH. 'File/Find.php';
        $match  = is_null($component)? '' : $component;
        $match .= '.' . (is_null($section)? '' : $section);
        $match .= empty($params)? '' : ('.' . $params);
        $files = &File_Find::search('/'.$match.'/i', $this->_path, 'perl', false, 'files');
        foreach ($files as $file) {
            Jaws_Utils::Delete($file);
        }

        return true;
    }

}