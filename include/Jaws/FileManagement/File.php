<?php
/**
 * Class of functions for manage file system
 *
 * @category    Jaws_FileManagement
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_FileManagement_File extends Jaws_FileManagement
{
    /**
     * Returns information about a file path
     *
     * @access  public
     * @param   string  $path       The path to be parsed
     * #param   int     $options    If present, specifies a specific element to be returned
     * @return  mixed   Returns an associative array containing the following elements
     * @see     http://www.php.net/pathinfo 
     */
    static function pathinfo($path, $options = 0x0f)
    {
        return pathinfo($path, $options);
    }

    /**
     * Checks whether a file or directory exists
     *
     * @access  public
     * @param   string  $filename   Path to the file or directory
     * @return  bool    Returns TRUE if the file or directory exists, FALSE otherwise
     * @see     http://www.php.net/file_exists
     */
    static function file_exists($filename)
    {
        return @file_exists($filename);
    }

    /**
     * Tells whether the filename is a directory
     *
     * @access  public
     * @param   string  $filename   Path to the file or directory
     * @return  bool    Returns TRUE if the filename exists and is a directory, FALSE otherwise
     * @see     http://www.php.net/is_dir
     */
    static function is_dir($filename)
    {
        return is_dir($filename);
    }

    /**
     * Tells whether the filename is a regular file
     *
     * @access  public
     * @param   string  $filename   Path to the file
     * @return  bool    Returns TRUE if the filename exists and is a regular file, FALSE otherwise
     * @see     http://www.php.net/is_file
     */
    static function is_file($filename)
    {
        return is_file($filename);
    }

    /**
     * Gets file size
     *
     * @access  public
     * @param   string  $filename   Path to the file
     * @return  mixed   Returns the size of the file in bytes, FALSE otherwise
     * @see     http://www.php.net/filesize
     */
    static function filesize($filename)
    {
        return @filesize($filename);
    }

    /**
     * Gets file modification time
     *
     * @access  public
     * @param   string  $filename   Path to the file
     * @return  mixed   Returns the time the file was last modified, or FALSE on failure
     * @see     http://www.php.net/filemtime
     */
    static function filemtime($filename)
    {
        return @filemtime($filename);
    }

    /**
     * Tells whether the filename is writable
     *
     * @access  public
     * @param   string  $filename   The filename being checked
     * @return  bool    Returns TRUE if the filename exists and iswritable, FALSE otherwise
     */
    static function is_writable($filename)
    {
        clearstatcache();
        $filename = rtrim($filename, "\\/");
        if (!file_exists($filename)) {
            return false;
        }

        /* Take care of the safe mode limitations if safe_mode=1 */
        if (ini_get('safe_mode')) {
            if (is_dir($filename)) {
                $tmpdir = $filename.'/'. uniqid(mt_rand());
                if (!self::mkdir($tmpdir)) {
                    return false;
                }
                return self::delete($tmpdir);
            } else {
                if (false === $file = @fopen($filename, 'r+')) {
                    return false;
                }
                return fclose($file);
            }
        }

        return is_writeable($filename);
    }

    /**
     * Tells whether a file exists and is readable
     *
     * @access  public
     * @param   string  $filename   Path to the file
     * @return  bool    Returns TRUE if the file or directory exists and is readable, FALSE otherwise
     */
    static function is_readable($filename)
    {
        clearstatcache();
        return is_readable($filename);
    }

    /**
     * Changes file mode
     *
     * @access  public
     * @param   string  $filename   Path to the file
     * @param   int     $mode       see php chmod() function
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/chmod
     */
    static function chmod($filename, $mode = null)
    {
        $result = false;
        if (is_null($mode)) {
            $php_as_owner = (function_exists('posix_getuid') && posix_getuid() === @fileowner($filename));
            $php_as_group = (function_exists('posix_getgid') && posix_getgid() === @filegroup($filename));
            if (is_dir($filename)) {
                $mode = $php_as_owner? 0755 : ($php_as_group? 0775 : 0777);
            } else {
                $mode = $php_as_owner? 0644 : ($php_as_group? 0664 : 0666);
            }
        }

        $mode = is_int($mode)? $mode : octdec($mode);
        $mask = umask(0);
        /* Take care of the safe mode limitations if safe_mode=1 */
        if (ini_get('safe_mode')) {
            /* GID check */
            if (ini_get('safe_mode_gid')) {
                if (@filegroup($filename) == getmygid()) {
                    $result = @chmod($filename, $mode);
                }
            } else {
                if (@fileowner($filename) == @getmyuid()) {
                    $result = @chmod($filename, $mode);
                }
            }
        } else {
            $result = @chmod($filename, $mode);
        }

        umask($mask);
        return $result;
    }

    /**
     * List files and directories inside the specified path
     *
     * @access  public
     * @param   string      $directory      The directory that will be scanned
     * @param   int         $sorting_order  Sorting type by alphabetical
     *                      SCANDIR_SORT_ASCENDING, SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE
     * @param   resource    $context
     * @return  mixed       Returns an array of filenames on success, or FALSE on failure
     * @see     http://www.php.net/scandir
     */
    static function scandir($directory, $sorting_order = SCANDIR_SORT_ASCENDING, $context = null)
    {
        $list = @scandir($directory, $sorting_order);
        if (!empty($list)) {
            $list = array_diff($list, array('..', '.'));
        }

        return $list;
    }

    /**
     * Write a string to a file
     *
     * @access  public
     * @param   string      $filename   Path to the file where to write the data
     * @param   string      $data       file content
     * @param   int         $flags      file opening flag
     * @param   resource    $context    context resource 
     * @return  mixed       returns the number of bytes that were written to the file, or FALSE on failure
     * @see     http://www.php.net/file_put_contents
     */
    static function file_put_contents($filename, $data, $flags = null, $context = null)
    {
        $res = @file_put_contents($filename, $data, $flags, $context);
        if ($res !== false) {
            $mode = @fileperms(dirname($filename));
            if (!empty($mode)) {
                self::chmod($filename, $mode);
            }
        }

        return $res;
    }

    /**
     * Reads entire file into a string
     *
     * @access  public
     * @param   string      $filename           Name of the file to read
     * @param   bool        $use_include_path
     * @param   resource    $context            context resource 
     * @param   int         $offset             he offset where the reading starts on the original stream
     * @param   int         $maxlen             Maximum length of data read
     * @return  mixed       The function returns the read data or FALSE on failure
     * @see     http://www.php.net/file_get_contents
     */
    static function file_get_contents(
        $filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null
    ) {
        if (empty($maxlen)) {
            return @file_get_contents($filename, $use_include_path, $context, $offset);
        } else {
            return @file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
        }
    }

    /**
     * Parse a configuration file
     *
     * @access  public
     * @param   string      $filename           The filename of the ini file being parsed
     * @param   string      $process_sections   
     * @param   int         $scanner_mode       Can either be INI_SCANNER_NORMAL or INI_SCANNER_RAW
     * @return  mixed       The settings are returned as an associative array on success,and FALSE on failure
     * @see     http://www.php.net/parse_ini_file
     */
    static function parse_ini_file($filename, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL)
    {
        return parse_ini_file($filename, $process_sections, $scanner_mode);
    }

    /**
     * Reads the EXIF headers from an image file
     *
     * @access  public
     * @param   mixed   $stream     The location of the image file or a stream resource
     * @param   string  $sections   Is a comma separated list of sections
     * @param   bool    $arrays     Specifies whether or not each section becomes an array
     * @param   bool    $thumbnail  Thumbnail itself is read, Otherwise only the tagged data is read
     * @return  mixed   Returns an associative array or FALSE on failure
     * @see     http://www.php.net/exif_read_data
     */
    static function exif_read_data($stream, $sections = null, $arrays = false, $thumbnail = false)
    {
        return @exif_read_data($stream, $sections, $arrays, $thumbnail);
    }

    /**
     * Reads entire file into an array
     *
     * @access  public
     * @param   string      $filename   Path to the file
     * @param   int         $flags      Flags can be one, ormore, of the following constants:
     *                                  FILE_USE_INCLUDE_PATH, FILE_IGNORE_NEW_LINES, FILE_SKIP_EMPTY_LINES 
     * @param   resource    $context    context resource 
     * @return  mixed       Returns the file in an array or FALSE on failure
     * @see     http://www.php.net/file
     */
    static function file($filename, $flags = 0, $context = null)
    {
        return @file($filename, $flags, $context);
    }

    /**
     * Make directory
     *
     * @access  public
     * @param   string  $path       Path to the directory
     * @param   int     $mode       Directory permissions
     * @param   int     $recursive  Make up directories if not exists
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/chmod
     */
    static function mkdir($path, $mode = 0, $recursive = 0)
    {
        $result = true;
        if (!file_exists($path) || !is_dir($path)) {
            if ($recursive && !file_exists(dirname($path))) {
                $recursive--;
                self::mkdir(dirname($path), $mode, $recursive);
            }
            $result = @mkdir($path);
        }

        if (empty($mode)) {
            $mode = @fileperms(dirname($path));
        }

        if ($result && !empty($mode)) {
            self::chmod($path, $mode);
        }

        return $result;
    }

    /**
     * Renames/Moves a file or directory
     *
     * @access  public
     * @param   string  $source     Path to the source file or directory
     * @param   string  $dest       The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/rename
     */
    static function rename($source, $dest, $overwrite = true)
    {
        $result = false;
        if (file_exists($source)) {
            if (file_exists($dest)) {
                if (is_dir($source)) {
                    if (false !== $hDir = @opendir($source)) {
                        while(false !== ($file = @readdir($hDir))) {
                            if($file == '.' || $file == '..') {
                                continue;
                            }
                            $result = self::rename(
                                $source. '/' . $file,
                                $dest. '/' . $file,
                                $overwrite
                            );
                            if (!$result) {
                                break;
                            }
                        }

                        closedir($hDir);
                        self::delete($source);
                    }
                } else {
                    if (!$overwrite) {
                        $destinfo = self::pathinfo($dest);
                        $dest = $destinfo['dirname']. '/' .
                            $destinfo['filename']. '_'. uniqid(floor(microtime()*1000));
                        if (isset($destinfo['extension']) && !empty($destinfo['extension'])) {
                            $dest.= '.'. $destinfo['extension'];
                        }
                    }

                    $result = @rename($source, $dest);
                    if ($result) {
                        $result = $dest;
                    }
                }
            } else {
                $result = @rename($source, $dest);
                if ($result) {
                    $result = $dest;
                }
            }
        }

        return $result;
    }

    /**
     * Makes a copy of the source file or directory to dest
     *
     * @access  public
     * @param   string  $source     Path to the source file or directory
     * @param   string  $dest       The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @param   int     $mode       see php chmod() function
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/copy
     */
    static function copy($source, $dest, $overwrite = true, $mode = null)
    {
        $result = false;
        if (file_exists($source)) {
            if (is_dir($source)) {
                if (false !== $hDir = @opendir($source)) {
                    if ($result = self::mkdir($dest, $mode, 0)) {
                        while(false !== ($file = @readdir($hDir))) {
                            if($file == '.' || $file == '..') {
                                continue;
                            }

                            $result = self::copy(
                                $source. '/' . $file,
                                $dest. '/' . $file,
                                $overwrite,
                                $mode
                            );
                            if (!$result) {
                                break;
                            }
                        }
                    }

                    closedir($hDir);
                }
            } else {
                if (file_exists($dest) && !$overwrite) {
                    $destinfo = self::pathinfo($dest);
                    $dest = $destinfo['dirname']. '/' .
                        $destinfo['filename']. '_'. uniqid(floor(microtime()*1000));
                    if (isset($destinfo['extension']) && !empty($destinfo['extension'])) {
                        $dest.= '.'. $destinfo['extension'];
                    }
                }

                $result = @copy($source, $dest);
                if ($result) {
                    $result = $dest;
                    if (!empty($mode)) {
                        self::chmod($dest, $mode);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Delete directories and files
     *
     * @access  public
     * @param   string  $path       File/Directory path
     * @param   bool    $itself     Include self directory
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see http://www.php.net/rmdir & http://www.php.net/unlink
     */
    static function delete($path, $itself = true)
    {
        if (!file_exists($path)) {
            return true;
        }

        if (is_file($path) || is_link($path)) {
            // unlink can't delete read-only files in windows os
            if (JAWS_OS_WIN) {
                self::chmod($path, 0777); 
            }

            return @unlink($path);
        }

        if (false !== $files = @scandir($path)) {
            foreach ($files as $file) {
                if($file == '.' || $file == '..') {
                    continue;
                }

                if (!self::delete($path. '/'. $file, true)) {
                    return false;
                }
            }
        }

        if ($itself) {
            return @rmdir($path);
        }

        return true;
    }

    /**
     *  Renames a file or directory
     *
     * @access  public
     * @param   string      $oldname    The old name
     * @param   string      $newname    The new name
     * @param   resource    $context    context resource 
     * @return  bool        Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/rename
     */
    static function rename_from_file($oldname, $newname, $context = null)
    {
        return @rename($oldname, $newname, $context);
    }

    /**
     *  Copies file
     *
     * @access  public
     * @param   string      $source     Path to the source file
     * @param   string      $dest       The destination path
     * @param   resource    $context    context resource 
     * @return  bool        Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/copy
     */
    static function copy_from_file($source, $dest, $context = null)
    {
        return @copy($source, $dest, $context);
    }

    /**
     * Opens file
     *
     * @access  public
     * @param   string      $filename           Path to the file
     * @param   string      $mode               Type of access require to the stream
     * @param   bool        $use_include_path
     * @param   resource    $context            context resource 
     * @return  mixed       Returns a file pointer resource on success or FALSE on failure
     * @see     http://www.php.net/fopen
     */
    static function fopen($filename, $mode, $use_include_path = false, $context = null)
    {
        return @fopen($filename, $mode, $use_include_path, $context);
    }

    /**
     * get upload temp directory
     *
     * @return  string  upload temp directory path
     */
    static function upload_tmp_dir()
    {
        $upload_dir = ini_get('upload_tmp_dir')? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        return rtrim($upload_dir, "\\/");
    }

}