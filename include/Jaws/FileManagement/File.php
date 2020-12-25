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
     * is directory writable?
     *
     * @access  public
     * @param   string  $path directory path
     * @return  bool    True/False
     */
    static function is_writable($path)
    {
        clearstatcache();
        $path = rtrim($path, "\\/");
        if (!file_exists($path)) {
            return false;
        }

        /* Take care of the safe mode limitations if safe_mode=1 */
        if (ini_get('safe_mode')) {
            if (is_dir($path)) {
                $tmpdir = $path.'/'. uniqid(mt_rand());
                if (!self::mkdir($tmpdir)) {
                    return false;
                }
                return self::delete($tmpdir);
            } else {
                if (false === $file = @fopen($path, 'r+')) {
                    return false;
                }
                return fclose($file);
            }
        }

        return is_writeable($path);
    }

    /**
     * Write a string to a file
     *
     * @access  public
     * @param   string      $file       file path
     * @param   string      $data       file content
     * @param   int         $flags      file opening flag
     * @param   resource    $context    context resource 
     * @return  mixed       returns the number of bytes that were written to the file, or FALSE on failure
     * @see     http://www.php.net/file_put_contents
     */
    static function file_put_contents($file, $data, $flags = 0, $context = null)
    {
        $res = @file_put_contents($file, $data, $flags, $context);
        if ($res !== false) {
            $mode = @fileperms(dirname($file));
            if (!empty($mode)) {
                self::chmod($file, $mode);
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
        return @file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
    }

    /**
     * Make directory
     *
     * @access  public
     * @param   string  $path       Path to the directory
     * @param   int     $recursive  Make up directories if not exists
     * @param   int     $mode       Directory permissions
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/chmod
     */
    static function mkdir($path, $recursive = 0, $mode = null)
    {
        $result = true;
        if (!file_exists($path) || !is_dir($path)) {
            if ($recursive && !file_exists(dirname($path))) {
                $recursive--;
                self::mkdir(dirname($path), $recursive, $mode);
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
     * Change file/directory mode
     *
     * @access  public
     * @param   string  $path file/directory path
     * @param   int     $mode see php chmod() function
     * @return  bool    True/False
     */
    static function chmod($path, $mode = null)
    {
        $result = false;
        if (is_null($mode)) {
            $php_as_owner = (function_exists('posix_getuid') && posix_getuid() === @fileowner($path));
            $php_as_group = (function_exists('posix_getgid') && posix_getgid() === @filegroup($path));
            if (is_dir($path)) {
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
                if (@filegroup($path) == getmygid()) {
                    $result = @chmod($path, $mode);
                }
            } else {
                if (@fileowner($path) == @getmyuid()) {
                    $result = @chmod($path, $mode);
                }
            }
        } else {
            $result = @chmod($path, $mode);
        }

        umask($mask);
        return $result;
    }

    /**
     * Renames/Moves a file or directory
     *
     * @access  public
     * @param   string  $src    Path to the source file or directory
     * @param   string  $dst    The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/rename
     */
    static function rename($src, $dst, $overwrite = true)
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
                        $destinfo = pathinfo($dest);
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
     * @param   string  $src    Path to the source file or directory
     * @param   string  $dst    The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @param   int     $mode       see php chmod() function
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/copy
     */
    static function copy($src, $dst, $overwrite = true, $mode = null)
    {
        $result = false;
        if (file_exists($source)) {
            if (is_dir($source)) {
                if (false !== $hDir = @opendir($source)) {
                    if ($result = self::mkdir($dest, 0, $mode)) {
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
                    $destinfo = pathinfo($dest);
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

}