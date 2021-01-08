<?php
/**
 * File cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2020 Jaws Development Group
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
        parent::__construct();
        $this->cacheDirectory = rtrim(sys_get_temp_dir(), '/\\') . '/';
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
        $result = false;
        if ($serialize) {
            $value = serialize($value);
        }

        if (!empty($lifetime)) {
            $file = $this->cacheDirectory . $this->cachePrefix. $key;
            if ($result = Jaws_FileManagement_File::file_put_contents($file, $value)) {
                @touch($file, time() + $lifetime);
            }
        }

        return $result;
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
        $file = $this->cacheDirectory . $this->cachePrefix. $key;
        $ftime = (int)Jaws_FileManagement_File::filemtime($file);
        if ((int)$ftime > time()) {
            if ($unserialize) {
                return @unserialize(Jaws_FileManagement_File::file_get_contents($file));
            }

            return Jaws_FileManagement_File::file_get_contents($file);
        }

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
        $file = $this->cacheDirectory . $this->cachePrefix. $key;
        return Jaws_FileManagement_File::delete($file);
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
        $file = $this->cacheDirectory . $this->cachePrefix. $key;
        $ftime = (int)Jaws_FileManagement_File::filemtime($file);
        if ((int)$ftime > time()) {
            return true;
        }

        return false;
    }

    /**
     * Delete expired cached keys
     *
     * @access  public
     * @return  mixed
     */
    function deleteExpiredKeys()
    {
        try {
            if ($hDir = opendir($this->cacheDirectory)) {
                while (($fname = readdir($hDir)) !== false) {
                    if (@is_file($this->cacheDirectory . $fname)) {
                        $ftime = (int)Jaws_FileManagement_File::filemtime($this->cacheDirectory . $fname);
                        if ((int)$ftime < time()) {
                            Jaws_FileManagement_File::delete($this->cacheDirectory . $fname);
                        }
                    }
                }

                closedir($hDir);
            }
        } catch (Exception $error) {
            // do nothing
        }

        return true;
    }

}