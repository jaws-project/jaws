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
     * @param   string  $filename   The filename/directory to be parsed
     * #param   int     $options    If present, specifies a specific element to be returned
     * @return  mixed   Returns an associative array containing the following elements
     * @see     http://www.php.net/pathinfo 
     */
    static function pathinfo($filename, $options)
    {
        if (str_starts_with($filename, ROOT_DATA_PATH)) {
            $filename = Jaws_UTF8::substr($filename, Jaws_UTF8::strlen(ROOT_DATA_PATH));
        }

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $res = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select(
                'id:integer', 'path as dirname', 'name as basename', 'type:integer', 'size:integer',
                'insert_time:integer', 'update_time:integer'
            )
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $res = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('id')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $type = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('type:integer')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
            ->fetchOne();

        return Jaws_Error::IsError($type)? false : ($type == 1);
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $type = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('type:integer')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
            ->fetchOne();

        return Jaws_Error::IsError($type)? false : ($type != 1);
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $size = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('size:integer')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $time = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('time:integer')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
            ->fetchOne();

        return Jaws_Error::IsError($time)? false : $time;
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
     * Change file/directory mode
     *
     * @access  public
     * @param   string  $path file/directory path
     * @param   int     $mode see php chmod() function
     * @return  bool    True/False
     */
    static function chmod($path, $mode = null)
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
            ->select('name')
            ->where('hash_path', hash64($directory))
            ->and()
            ->where('path', $directory);

        switch ($sorting_order) {
            case SCANDIR_SORT_ASCENDING:
                $objORM->orderBy('name asc');
                break;

            case SCANDIR_SORT_DESCENDING:
                $objORM->orderBy('name desc');
                break;

            default: // SCANDIR_SORT_NONE
                // do nothing
        }

        $list = $objORM->fetchColumn();
        return Jaws_Error::IsError($list)? false : $list;
    }

    /**
     * Write a string to a file
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);
        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'hash_path' => $hash_path,
                    'hash_name' => $hash_name,
                    'path'      => $path,
                    'name'      => $name,
                    'type'      => 255,
                    'size'      => strlen($data),
                    'insert_time' => time(),
                    'update_time' => time(),
                    'data' => array($data, 'blob')
                ),
                array(
                    'size' => strlen($data),
                    'update_time' => time(),
                    'data' => array($data, 'blob')
                )
            )
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $blob = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('data:blob')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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
     * Make directory
     *
     * @access  public
     * @param   string  $path       Path to the directory
     * @param   int     $recursive  Make up directories if not exists
     * @param   int     $mode       Directory permissions
     * @param   bool    $first_iteration
     * @return  bool    Returns TRUE on success or FALSE on failure
     * @see     http://www.php.net/mkdir
     */
    static function mkdir($path, $recursive = 0, $mode = null, $first_iteration = true)
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
                self::mkdir(dirname($path), $recursive, $mode, false);
            }

            $dirpath = dirname($path);
            $dirname = basename($path);
            $hash_dirpath = hash64($dirpath);
            $hash_dirname = hash64($dirname);

            $result = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->insert(
                    array(
                        'hash_path' => $hash_dirpath,
                        'hash_name' => $hash_dirname,
                        'path'      => $dirpath,
                        'name'      => $dirname,
                        'type'      => 1,
                        'size'      => 0,
                        'insert_time' => time(),
                        'update_time' => time(),
                        'data' => null
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
     * @param   string  $src    Path to the source file or directory
     * @param   string  $dst    The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @param   bool    $first_iteration
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/rename
     */
    static function rename($src, $dst, $overwrite = true, $first_iteration = true)
    {
        if ($first_iteration) {
            if (str_starts_with($src, ROOT_DATA_PATH)) {
                $src = Jaws_UTF8::substr($src, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }

            if (str_starts_with($dst, ROOT_DATA_PATH)) {
                $dst = Jaws_UTF8::substr($dst, Jaws_UTF8::strlen(ROOT_DATA_PATH));
            }

            $srcInfo = self::pathinfo($src);
            if (empty($srcInfo)) {
                return false;
            }

            $dstInfo = self::pathinfo($dst);
            if (empty($dstInfo) || !$overwrite) {
                return false;
            }

            if ($overwrite) {
                // delete destination if exists
                if (false == self::delete($dst, true)) {
                    return false;
                }
            }
        }

        // source
        $srcPath = dirname($src);
        $srcName = basename($src);
        $src_hash_path = hash64($srcPath);
        $src_hash_name = hash64($srcName);
        // destination
        $dstPath = dirname($dst);
        $dstName = basename($dst);
        $dst_hash_path = hash64($dstPath);
        $dst_hash_name = hash64($dstName);

        // move file or directory(without sub files) to new destination
        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->update(
                array(
                    'hash_path' => $dst_hash_path,
                    'hash_name' => $dst_hash_name,
                    'path'      => $dstPath,
                    'name'      => $dstName
                )
            )->where('hash_path', $src_hash_path)
            ->and()
            ->where('hash_name', $src_hash_name)
            ->and()
            ->where('path', $srcPath)
            ->and()
            ->where('name', $srcName)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        if ($srcInfo['type'] == 1) { // is directory
            $files = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->select('id:integer', 'name', 'type:integer')
                ->where('hash_path', hash64($src))
                ->and()
                ->where('path', $src)
                ->fetchAll();
            if (Jaws_Error::IsError($files)) {
                return false;
            }

            // sub files/directories
            foreach ($files as $file) {
                if (false === self::rename(
                    "$src/" . $file['name'], "$dst/". $file['name'], $overwrite, false
                )) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Makes a copy of the source file or directory to dest
     *
     * @access  public
     * @param   string  $src        Path to the source file or directory
     * @param   string  $dst        The destination path
     * @param   bool    $overwrite  Overwrite files if exists
     * @param   int     $mode       see php chmod() function
     * @param   bool    $first_iteration
     * @return  bool    True if success, False otherwise
     * @see http://www.php.net/copy
     */
    static function copy($src, $dst, $overwrite = true, $mode = null, $first_iteration = true)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $files = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('id:integer', 'name')
            ->where('hash_path', hash64($filename))
            ->and()
            ->where('path', $filename)
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
            $result = Jaws_ORM::getInstance()
                ->table('dbfs')
                ->delete()
                ->where('hash_path', $hash_path)
                ->and()
                ->where('hash_name', $hash_name)
                ->and()
                ->where('path', $path)
                ->and()
                ->where('name', $name)
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

        $path = dirname($newname);
        $name = basename($newname);
        $hash_path = hash64($path);
        $hash_name = hash64($name);
        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'hash_path' => $hash_path,
                    'hash_name' => $hash_name,
                    'path'      => $path,
                    'name'      => $name,
                    'type'      => 255,
                    'size'      => filesize($oldname),
                    'insert_time' => time(),
                    'update_time' => time(),
                    'data' => array('File://' . $oldname, 'blob')
                ),
                array(
                    'size' => filesize($oldname),
                    'update_time' => time(),
                    'data' => array('File://' . $oldname, 'blob')
                )
            )
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($dest);
        $name = basename($dest);
        $hash_path = hash64($path);
        $hash_name = hash64($name);
        $result = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->upsert(
                array(
                    'hash_path' => $hash_path,
                    'hash_name' => $hash_name,
                    'path'      => $path,
                    'name'      => $name,
                    'type'      => 255,
                    'size'      => filesize($source),
                    'insert_time' => time(),
                    'update_time' => time(),
                    'data' => array('File://' . $source, 'blob')
                ),
                array(
                    'size' => filesize($source),
                    'update_time' => time(),
                    'data' => array('File://' . $source, 'blob')
                )
            )
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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

        $path = dirname($filename);
        $name = basename($filename);
        $hash_path = hash64($path);
        $hash_name = hash64($name);

        $blob = Jaws_ORM::getInstance()
            ->table('dbfs')
            ->select('data:blob')
            ->where('hash_path', $hash_path)
            ->and()
            ->where('hash_name', $hash_name)
            ->and()
            ->where('path', $path)
            ->and()
            ->where('name', $name)
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