<?php
/**
 * Class of functions for manage database file system!
 *
 * @category    Jaws_FileManagement
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_FileManagement_Database extends Jaws_FileManagement
{
    /**
     * Returns information about a file path
     *
     * @access  public
     * @param   string  $path       The path to be parsed
     * #param   int     $options    If present, specifies a specific element to be returned
     * @return  mixed   Returns an associative array containing the following elements:
     *                  dirname, basename, extension (if any), and filename
     * @see     http://www.php.net/pathinfo 
     */
    static function pathinfo($path, $options = 0x0f)
    {
        if (str_starts_with($path, ROOT_DATA_PATH)) {
            $path = Jaws_UTF8::substr($path, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($path);
        $basename = basename($path);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $res = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select(
                'id:integer', 'parent:integer', 'pathname as dirname', 'basename', 'filename', 'extension',
                'type:integer', 'size:integer', 'mode:integer', 'ctime:integer', 'mtime:integer'
            )
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchRow();

        return Jaws_Error::IsError($res)? false : $res;
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $res = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('id')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        return Jaws_Error::IsError($res)? false : !empty($res);
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $type = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('type:integer')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        return Jaws_Error::IsError($type)? false : ($type == 4);
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $type = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('type:integer')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        return Jaws_Error::IsError($type)? false : ($type == 8);
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $size = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('size:integer')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        return Jaws_Error::IsError($size)? false : $size;
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $mtime = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('mtime:integer')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        return Jaws_Error::IsError($mtime)? false : $mtime;
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
        return true;
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
        return true;
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
        return true;
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
        if (str_starts_with($directory, ROOT_DATA_PATH)) {
            $directory = Jaws_UTF8::substr($directory, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $objORM = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('basename')
            ->where('pathhash', hash64($directory))
            ->and()
            ->where('pathname', $directory);

        switch ($sorting_order) {
            case SCANDIR_SORT_ASCENDING:
                $objORM->orderBy('basename asc');
                break;

            case SCANDIR_SORT_DESCENDING:
                $objORM->orderBy('basename desc');
                break;

            default: // SCANDIR_SORT_NONE
                // do nothing
        }

        $list = $objORM->fetchColumn();
        return Jaws_Error::IsError($list)? false : $list;
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);
        $fileinfo = pathinfo($basename);

        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'pathhash' => $pathhash,
                    'basehash' => $basehash,
                    'pathname' => $pathname,
                    'basename' => $basename,
                    'filename' => $fileinfo['filename'],
                    'extension'=> (string)@$fileinfo['extension'],
                    'type'     => 8,
                    'size'     => strlen($data),
                    'ctime'    => time(),
                    'mtime'    => time(),
                    'data'     => array($data, 'blob')
                ),
                array(
                    'size'  => strlen($data),
                    'mtime' => time(),
                    'data'  => array($data, 'blob')
                )
            )
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->exec();

        return Jaws_Error::IsError($result)? false : strlen($data);
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $blob = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('data:blob')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        if (Jaws_Error::IsError($blob)) {
            return false;
        }

        $data = '';
        if (is_resource($blob)) {
            while (!feof($blob)) {
                $data.= fread($blob, 8192);
            }
        }

        return $data;
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
        if (false === $ini = self::file_get_contents($filename)) {
            return false;
        }

        return parse_ini_string($ini, $process_sections, $scanner_mode);
    }

    /**
     * Reads the EXIF headers from an image file
     *
     * @access  public
     * @param   mixed   $stream     The location of the image file or a stream resource
     * @param   string  $sections   Is a comma separated list of sections
     * @param   bool    $arrays     Specifies whether or not each section becomes an array
     * @param   bool    $thumbnail  Thumbnail itself is read, Otherwise only thetagged data is read
     * @return  mixed   Returns an associative array or FALSE on failure
     * @see     https://www.php.net/exif_read_data
     */
    static function exif_read_data($stream, $sections = null, $arrays = false, $thumbnail = false)
    {
        if (str_starts_with($stream, ROOT_DATA_PATH)) {
            $stream = Jaws_UTF8::substr($stream, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($stream);
        $basename = basename($stream);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $blob = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('data:blob')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();
        if (Jaws_Error::IsError($blob) || !is_resource($blob)) {
            return false;
        }

        return @exif_read_data($blob, $sections, $arrays, $thumbnail);
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
        if (false === $content = self::file_get_contents($filename)) {
            return false;
        }

        $pregFlags = 0;
        if ($flags & FILE_SKIP_EMPTY_LINES) {
            $pregFlags = $pregFlags | PREG_SPLIT_NO_EMPTY;
        }
        if (!(bool)($flags & FILE_IGNORE_NEW_LINES)) {
            $pregFlags = $pregFlags | PREG_SPLIT_DELIM_CAPTURE;
        }

        return @preg_split('/(?<=[\r\n]|[\n]|[\r])/', $content, -1, $pregFlags);
    }

    /**
     * Make directory
     *
     * @access  public
     * @param   string  $path       Path to the directory
     * @param   int     $mode       Directory permissions
     * @param   int     $recursive  Make up directories if not exists
     * @param   bool    $first_iteration
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/mkdir
     */
    static function mkdir($path, $mode = 0, $recursive = 0, $first_iteration = true)
    {
        if ($first_iteration) {
            if (str_starts_with($path, ROOT_DATA_PATH)) {
                $path = Jaws_UTF8::substr($path, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }
        }

        $result = true;
        if (!self::file_exists($path) || !self::is_dir($path)) {
            if ($recursive && !self::file_exists(dirname($path))) {
                $recursive--;
                self::mkdir(dirname($path), $mode, $recursive, false);
            }

            $pathname = dirname($path);
            $basename = basename($path);
            $pathhash = hash64($pathname);
            $basehash = hash64($basename);
            $fileinfo = pathinfo($dstName);

            $result = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->insert(
                    array(
                        'pathhash' => $pathhash,
                        'basehash' => $basehash,
                        'pathname' => $pathname,
                        'basename' => $basename,
                        'filename' => $fileinfo['filename'],
                        'extension'=> (string)@$fileinfo['extension'],
                        'type'     => 4,
                        'size'     => 0,
                        'ctime' => time(),
                        'mtime' => time(),
                        'data'  => null
                    )
                )
                ->exec();

            $result = Jaws_Error::IsError($result)? false : !empty($result);
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
     * @param   bool    $first_iteration
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/rename
     */
    static function rename($source, $dest, $overwrite = true, $first_iteration = true)
    {
        if ($first_iteration) {
            if (str_starts_with($source, ROOT_DATA_PATH)) {
                $source = Jaws_UTF8::substr($source, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }

            if (str_starts_with($dest, ROOT_DATA_PATH)) {
                $dest = Jaws_UTF8::substr($dest, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }

            $srcInfo = self::pathinfo($source);
            if (empty($srcInfo)) {
                return false;
            }

            $dstInfo = self::pathinfo($dest);
            if (empty($dstInfo) || !$overwrite) {
                return false;
            }

            if ($overwrite) {
                // delete destination if exists
                if (false == self::delete($dest, true)) {
                    return false;
                }
            }
        }

        // source
        $srcPath = dirname($source);
        $srcName = basename($source);
        $src_hash_path = hash64($srcPath);
        $src_hash_name = hash64($srcName);

        // destination
        $dstPath = dirname($dest);
        $dstName = basename($dest);
        $dst_hash_path = hash64($dstPath);
        $dst_hash_name = hash64($dstName);
        $dst_fileinfo = pathinfo($dstName);

        // move file or directory(without sub files) to new destination
        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->update(
                array(
                    'pathhash' => $dst_hash_path,
                    'basehash' => $dst_hash_name,
                    'pathname' => $dstPath,
                    'basename' => $dstName,
                    'filename' => $dst_fileinfo['filename'],
                    'extension'=> (string)@$dst_fileinfo['extension'],
                )
            )->where('pathhash', $src_hash_path)
            ->and()
            ->where('basehash', $src_hash_name)
            ->and()
            ->where('pathname', $srcPath)
            ->and()
            ->where('basename', $srcName)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        if ($srcInfo['type'] == 4) { // is directory
            $files = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->select('id:integer', 'basename', 'type:integer')
                ->where('pathhash', hash64($source))
                ->and()
                ->where('pathname', $source)
                ->fetchAll();
            if (Jaws_Error::IsError($files)) {
                return false;
            }

            // sub files/directories
            foreach ($files as $file) {
                if (false === self::rename(
                    "$source/" . $file['basename'], "$dest/". $file['basename'], $overwrite, false
                )) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Copies file
     *
     * @access  public
     * @param   string  $source     Path to the source file
     * @param   string  $dest       The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @param   int     $mode       see php chmod() function
     * @param   bool    $first_iteration
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/copy
     */
    static function copy($source, $dest, $overwrite = true, $mode = null, $first_iteration = true)
    {
        return true;
    }

    /**
     * Delete directories and files
     *
     * @access  public
     * @param   string  $filename   File/Directory path
     * @param   bool    $itself     Include self directory
     * @param   bool    $first_iteration
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see http://www.php.net/rmdir & http://www.php.net/unlink
     */
    static function delete($filename, $itself = true, $first_iteration = true)
    {
        if ($first_iteration) {
            if (str_starts_with($filename, ROOT_DATA_PATH)) {
                $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }
        }

        $files = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('id:integer', 'basename')
            ->where('pathhash', hash64($filename))
            ->and()
            ->where('pathname', $filename)
            ->fetchAll();
        if (Jaws_Error::IsError($files)) {
            return false;
        }

        foreach ($files as $file) {
            if (false === self::delete($filename. '/' . $file, $itself, false)) {
                return false;
            }
        }

        if ($itself) {
            $pathname = dirname($filename);
            $basename = basename($filename);
            $pathhash = hash64($pathname);
            $basehash = hash64($basename);

            $result = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->delete()
                ->where('pathhash', $pathhash)
                ->and()
                ->where('basehash', $basehash)
                ->and()
                ->where('pathname', $pathname)
                ->and()
                ->where('basename', $basename)
                ->exec();

            return (Jaws_Error::IsError($result) || empty($result))? false : true;
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
        if (str_starts_with($newname, ROOT_DATA_PATH)) {
            $newname = Jaws_UTF8::substr($newname, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($newname);
        $basename = basename($newname);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);
        $fileinfo = pathinfo($basename);

        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'pathhash' => $pathhash,
                    'basehash' => $basehash,
                    'pathname' => $pathname,
                    'basename' => $basename,
                    'filename' => $fileinfo['filename'],
                    'extension'=> (string)@$fileinfo['extension'],
                    'type'     => 8,
                    'size'     => filesize($oldname),
                    'ctime' => time(),
                    'mtime' => time(),
                    'data'  => array('File://' . $oldname, 'blob')
                ),
                array(
                    'size'  => filesize($oldname),
                    'mtime' => time(),
                    'data'  => array('File://' . $oldname, 'blob')
                )
            )
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->exec();

        if (!Jaws_Error::IsError($result)) {
            @unlink($oldname);
        }

        return !Jaws_Error::IsError($result);
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
        if (str_starts_with($dest, ROOT_DATA_PATH)) {
            $dest = Jaws_UTF8::substr($dest, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($dest);
        $basename = basename($dest);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);
        $fileinfo = pathinfo($basename);

        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'pathhash' => $pathhash,
                    'basehash' => $basehash,
                    'pathname' => $pathname,
                    'basename' => $basename,
                    'filename' => $fileinfo['filename'],
                    'extension'=> (string)@$fileinfo['extension'],
                    'type'     => 8,
                    'size'     => filesize($source),
                    'ctime' => time(),
                    'mtime' => time(),
                    'data'  => array('File://' . $source, 'blob')
                ),
                array(
                    'size'  => filesize($source),
                    'mtime' => time(),
                    'data'  => array('File://' . $source, 'blob')
                )
            )
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->exec();

        return !Jaws_Error::IsError($result);
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
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $pathname = dirname($filename);
        $basename = basename($filename);
        $pathhash = hash64($pathname);
        $basehash = hash64($basename);

        $blob = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('data:blob')
            ->where('pathhash', $pathhash)
            ->and()
            ->where('basehash', $basehash)
            ->and()
            ->where('pathname', $pathname)
            ->and()
            ->where('basename', $basename)
            ->fetchOne();

        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $blob;
    }

    /**
     * get upload temp directory
     *
     * @return  string  upload temp directory path
     */
    static function upload_tmp_dir()
    {
        return 'upload_tmp_dir';
    }

}