<?php
/**
 * Determine server operation system
 */
define('JAWS_OS_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

const JAWS_FILE_TYPE =  array(
        'FOLDER'  => 1,
        'TEXT'    => 2,
        'IMAGE'   => 3,
        'AUDIO'   => 4,
        'VIDEO'   => 5,
        'FONT'    => 6,
        'ARCHIVE' => 7,
        'UNKNOWN' => 255,
);

/**
 * Some useful functions
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Utils
{
    /**
     * Deny file upload extensions
     *
     * @var     array
     * @access  private
     */
    private static $deny_formats = array(
        'php', 'phpt', 'phtml', 'php3', 'php4', 'php5', 'php6',
        'pl', 'py', 'cgi', 'pcgi', 'pcgi5', 'pcgi4', 'htaccess', 'htpasswd'
    );

    /**
     * Change the color of a row from a given color
     *
     * @access  public
     * @param   string  $color  Original color(so we don't return the same color)
     * @return  string  New color
     */
    static function RowColor($color)
    {
        if ($color == '#fff') {
            return '#eee';
        }

        return '#fff';
    }

    /**
     * Get a random text
     *
     * @access  public
     * @param   int     $length     String length
     * @param   bool    $use_lower  Include lower characters
     * @param   bool    $use_upper  Include upper characters
     * @param   bool    $use_number Include numbers
     * @return  string  Random text
     */
    static function RandomText($length = 5, $complexity = array())
    {
        // default complexity if not set is: lower & upper
        if (empty($complexity)) {
            $complexity = array(
                'lower' => true,
                'upper' => true
            );
        }

        $possible = array(
            'lower'   => 'abcdefghijklmnopqrstuvwxyz',
            'upper'   => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'number'  => '0123456789',
            'special' => '!@#$%^-,~?*',
        );

        $string = '';
        $lngmin = 0;
        $allPossibleChars = '';
        foreach ($complexity as $key => $keyValue) {
            if ($keyValue) {
                $lngmin ++;
                $string.= $possible[$key][mt_rand(0, strlen($possible[$key])-1)];
                $allPossibleChars.= $possible[$key];
            }
        }

        $length = ($length < $lngmin)? $lngmin : $length;
        $string.= substr(str_shuffle($allPossibleChars), 0, $length - strlen($string));
        return str_shuffle($string);
    }

    /**
     * Convert a number in bytes, kilobytes,...
     *
     * @access  public
     * @param   int     $num
     * @return  string  The converted number in string
     */
    static function FormatSize($num)
    {
        $unims = array("B", "KB", "MB", "GB", "TB");
        $i = 0;
        while ($num >= 1024) {
            $i++;
            $num = $num/1024;
        }

        return number_format($num, 2). " ". $unims[$i];
    }

    /**
     * Parse request URL
     *
     * @access  public
     * @return  array   Parse request URL and return its components
     */
    static function parseRequestURL()
    {
        static $parts;
        if (!isset($parts)) {
            $parts = array();
            // server schema
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $parts['scheme'] = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
            } else {
                $parts['scheme'] = empty($_SERVER['HTTPS'])? 'http' : 'https';
            }

            //$parts['host'] = $_SERVER['SERVER_NAME'];
            $parts['host'] = current(explode(':', $_SERVER['HTTP_HOST']));
            // server port
            $parts['port'] = $_SERVER['SERVER_PORT']==80? '' : $_SERVER['SERVER_PORT'];

            $path = strip_tags($_SERVER['PHP_SELF']);
            if (false === stripos($path, BASE_SCRIPT)) {
                $path = strip_tags($_SERVER['SCRIPT_NAME']);
                if (false === stripos($path, BASE_SCRIPT)) {
                    $pInfo = isset($_SERVER['PATH_INFO'])? $_SERVER['PATH_INFO'] : '';
                    $pInfo = (empty($pInfo) && isset($_SERVER['ORIG_PATH_INFO']))? $_SERVER['ORIG_PATH_INFO'] : '';
                    $pInfo = (empty($pInfo) && isset($_ENV['PATH_INFO']))? $_ENV['PATH_INFO'] : '';
                    $pInfo = (empty($pInfo) && isset($_ENV['ORIG_PATH_INFO']))? $_ENV['ORIG_PATH_INFO'] : '';
                    $pInfo = strip_tags($pInfo);
                    if (!empty($pInfo)) {
                        $path = substr($path, 0, strpos($path, $pInfo)+1);
                    }
                }
            }

            $parts['path'] = substr($path, 0, stripos($path, BASE_SCRIPT)-1);
            $parts['path'] = explode('/', $parts['path']);
            $parts['path'] = implode('/', array_map('rawurlencode', $parts['path']));
            $parts['path'] = rtrim($parts['path'], '/');
        }

        // FIXME:: add query part
        // REDIRECT_URL, REDIRECT_QUERY_STRING, REQUEST_URI, QUERY_STRING

        return $parts;
    }

    /**
     * Get base url
     *
     * @access  public
     * @param   string  $suffix     suffix for add to base url
     * @param   bool    $rel_url    relative url
     * @return  string  url of base script
     */
    static function getBaseURL($suffix = '', $rel_url = true)
    {
        $site_url = Jaws_Utils::parseRequestURL();
        $site_url['port'] = $site_url['port']?: (':'.$_SERVER['SERVER_PORT']);

        $url = $site_url['path'];
        if (!$rel_url) {
            $url = $site_url['scheme']. '://'. $site_url['host']. $site_url['port']. $url;
        }

        return $url . (is_bool($suffix)? '' : $suffix);
    }

    /**
     * Get request url
     *
     * @access  public
     * @param   bool    $rel_url    relative or full URL
     * @return  string  get url without base url
     */
    static function getRequestURL($rel_url = true)
    {
        static $uri;
        if (!isset($uri)) {
            if (isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL'])) {
                $uri = $_SERVER['REDIRECT_URL'];
                if (isset($_SERVER['REDIRECT_QUERY_STRING']) && !empty($_SERVER['REDIRECT_QUERY_STRING'])) {
                    $uri.= '?'. $_SERVER['REDIRECT_QUERY_STRING'];
                }
            } elseif (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
                $uri = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['PHP_SELF'] . '?' .$_SERVER['QUERY_STRING'];
            } else {
                $uri = '';
            }

            $rel_base = Jaws_Utils::getBaseURL('', true);
            $uri = substr($uri, strlen($rel_base));
        }

        return $rel_url? ltrim($uri, '/') : (Jaws_Utils::getBaseURL('', false) .$uri);
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
                if (!Jaws_Utils::mkdir($tmpdir)) {
                    return false;
                }
                return Jaws_Utils::delete($tmpdir);
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
     * @access  public
     * @param   string      $file               file path
     * @param   string      $data               file content
     * @param   int         $flags              file opening flag
     * @param   resource    $resource_context   context resource 
     * @return  mixed       returns the number of bytes that were written to the file, or FALSE on failure
     * @see     http://www.php.net/file_put_contents
     */
    static function file_put_contents($file, $data, $flags = null, $resource_context = null)
    {
        $res = @file_put_contents($file, $data, $flags, $resource_context);
        if ($res !== false) {
            $mode = @fileperms(dirname($file));
            if (!empty($mode)) {
                Jaws_Utils::chmod($file, $mode);
            }
        }

        return $res;
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
                Jaws_Utils::mkdir(dirname($path), $recursive, $mode);
            }
            $result = @mkdir($path);
        }

        if (empty($mode)) {
            $mode = @fileperms(dirname($path));
        }

        if ($result && !empty($mode)) {
            Jaws_Utils::chmod($path, $mode);
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
                    if ($result = Jaws_Utils::mkdir($dest, 0, $mode)) {
                        while(false !== ($file = @readdir($hDir))) {
                            if($file == '.' || $file == '..') {
                                continue;
                            }

                            $result = Jaws_Utils::copy(
                                $source. DIRECTORY_SEPARATOR . $file,
                                $dest. DIRECTORY_SEPARATOR . $file,
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
                    $dest = $destinfo['dirname']. DIRECTORY_SEPARATOR .
                        $destinfo['filename']. '_'. uniqid(floor(microtime()*1000));
                    if (isset($destinfo['extension']) && !empty($destinfo['extension'])) {
                        $dest.= '.'. $destinfo['extension'];
                    }
                }

                $result = @copy($source, $dest);
                if ($result) {
                    $result = $dest;
                    if (!empty($mode)) {
                        Jaws_Utils::chmod($dest, $mode);
                    }
                }
            }
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
                            $result = Jaws_Utils::rename(
                                $source. DIRECTORY_SEPARATOR . $file,
                                $dest. DIRECTORY_SEPARATOR . $file,
                                $overwrite
                            );
                            if (!$result) {
                                break;
                            }
                        }

                        closedir($hDir);
                        Jaws_Utils::delete($source);
                    }
                } else {
                    if (!$overwrite) {
                        $destinfo = pathinfo($dest);
                        $dest = $destinfo['dirname']. DIRECTORY_SEPARATOR .
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
                @chmod($path, 0777); 
            }

            return @unlink($path);
        }

        if (false !== $files = @scandir($path)) {
            foreach ($files as $file) {
                if($file == '.' || $file == '..') {
                    continue;
                }

                if (!Jaws_Utils::delete($path. DIRECTORY_SEPARATOR. $file, true)) {
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
     * Map pathname and a project identifier to a token
     *
     * @access  public
     * @param   string  $pathname   File path name
     * @param   string  $proj       Project name
     * @return  int     Returns     file token
     */
    static function ftok($pathname, $proj = '')
    {
        return crc32($proj . $pathname);
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

    /**
     * Upload Files
     *
     * @access  public
     * @param   array   $files          $_FILES array
     * @param   string  $dest           destination directory(include end directory separator)
     * @param   string  $allow_formats  permitted file format
     * @param   bool    $overwrite      overwrite file or generate random filename
     *                                  null: random, true/false: overwrite?
     * @param   bool    $move_files     moving or only copying files. this param avail for non-uploaded files
     * @param   int     $max_size       max size of file
     * @return  mixed   Returns uploaded files array on success or Jaws_Error/FALSE on failure
     */
    static function UploadFiles($files, $dest, $allow_formats = '',
                                $overwrite = true, $move_files = true, $max_size = null)
    {
        if (empty($files) || !is_array($files)) {
            return false;
        }

        $result = array();
        if (isset($files['tmp_name'])) {
            $files = array($files);
        }

        $dest = rtrim($dest, "\\/"). DIRECTORY_SEPARATOR;
        $allow_formats = array_filter(explode(',', $allow_formats));
        foreach($files as $key => $listFiles) {
            if (!is_array($listFiles['tmp_name'])) {
                $listFiles = array_map(
                    function($item) {
                        return array($item);
                    },
                    $listFiles
                );
            }

            for($i=0; $i < count($listFiles['name']); ++$i) {
                $file = array();
                $file['name']     = $listFiles['name'][$i];
                $file['tmp_name'] = $listFiles['tmp_name'][$i];
                $file['size']     = $listFiles['size'][$i];
                if (isset($listFiles['error'])) {
                    $file['error'] = $listFiles['error'][$i];
                }

                if (isset($file['error']) && !empty($file['error']) && $file['error'] != 4) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_UPLOAD_'.$file['error']),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE,
                        1
                    );
                }

                if (empty($file['tmp_name'])) {
                    continue;
                }

                $user_filename = isset($file['name']) ? $file['name'] : '';
                $host_filename = strtolower(preg_replace('/[^[:alnum:]_\.\-]/', '', $user_filename));
                // remove deny_formats extension, even double extension
                $host_filename = implode(
                    '.',
                    array_diff(explode('.', $host_filename), self::$deny_formats)
                );

                // file category type
                $fileExtType = Jaws_Utils::mime_extension_type($host_filename);
                $file['type'] = $fileExtType['type'];

                // file mime-type
                $file['mime'] = @mime_content_type($file['tmp_name']);
                $file['mime'] = $file['mime']?: $fileExtType['mime'];

                $fileinfo = pathinfo($host_filename);
                if (isset($fileinfo['extension'])) {
                    if (!empty($allow_formats) && !in_array($fileinfo['extension'], $allow_formats)) {
                        return Jaws_Error::raiseError(
                            _t('GLOBAL_ERROR_UPLOAD_INVALID_FORMAT', $host_filename),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE,
                            1
                        );
                    }
                    $fileinfo['extension'] = '.'. $fileinfo['extension'];
                } else {
                    $fileinfo['extension'] = '';
                }

                if (is_null($overwrite) || empty($fileinfo['filename'])) {
                    $host_filename = time(). mt_rand() . $fileinfo['extension'];
                } elseif (!$overwrite && file_exists($dest . $host_filename)) {
                    $host_filename.= $fileinfo['filename']. '_'. time(). mt_rand(). $fileinfo['extension'];
                }

                $uploadfile = $dest . $host_filename;
                if (is_uploaded_file($file['tmp_name'])) {
                    if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
                        return Jaws_Error::raiseError(
                            _t('GLOBAL_ERROR_UPLOAD', $host_filename),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE,
                            1
                        );
                    }
                } else {
                    // On windows-systems we can't rename a file to an existing destination,
                    // So we first delete destination file
                    if (file_exists($uploadfile)) {
                        @unlink($uploadfile);
                    }
                    $res = $move_files?
                        @rename($file['tmp_name'], $uploadfile):
                        @copy($file['tmp_name'], $uploadfile);
                    if (!$res) {
                        return Jaws_Error::raiseError(
                            _t('GLOBAL_ERROR_UPLOAD', $host_filename),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE,
                            1
                        );
                    }
                }

                // Check if the file has been altered or is corrupted
                if (filesize($uploadfile) != $file['size']) {
                    @unlink($uploadfile);
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_UPLOAD_CORRUPTED', $host_filename),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE,
                        1
                    );
                }

                Jaws_Utils::chmod($uploadfile);
                $result[$key][$i]['user_filename'] = $user_filename;
                $result[$key][$i]['host_filename'] = $host_filename;
                $result[$key][$i]['host_mimetype'] = $file['mime'];
                $result[$key][$i]['host_filetype'] = $file['type'];
                $result[$key][$i]['host_filesize'] = $file['size'];
            }
        }

        return $result;
    }

    /**
     * Extract archive Files
     *
     * @access  public
     * @param   array   $files        $_FILES array
     * @param   string  $dest         Destination directory(include end directory separator)
     * @param   bool    $extractToDir Create separate directory for extracted files
     * @param   bool    $overwrite    Overwrite directory if exist
     * @param   int     $max_size     Max size of file
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    static function ExtractFiles($files, $dest, $extractToDir = true, $overwrite = true, $max_size = null)
    {
        if (empty($files) || !is_array($files)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_UPLOAD'),
                                     __FUNCTION__);
        }

        if (isset($files['name'])) {
            $files = array($files);
        }

        require_once PEAR_PATH. 'File/Archive.php';
        foreach($files as $key => $file) {
            if ((isset($file['error']) && !empty($file['error'])) || !isset($file['name'])) {
                return new Jaws_Error(_t('GLOBAL_ERROR_UPLOAD_'.$file['error']),
                                      __FUNCTION__);
            }

            if (empty($file['tmp_name'])) {
                continue;
            }

            $ext = strrchr($file['name'], '.');
            $filename = substr($file['name'], 0, -strlen($ext));
            if (false !== stristr($filename, '.tar')) {
                $filename = substr($filename, 0, strrpos($filename, '.'));
                switch ($ext) {
                    case '.gz':
                        $ext = '.tgz';
                        break;

                    case '.bz2':
                    case '.bzip2':
                        $ext = '.tbz';
                        break;

                    default:
                        $ext = '.tar' . $ext;
                }
            }

            $ext = strtolower(substr($ext, 1));
            if (!File_Archive::isKnownExtension($ext)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_UPLOAD_INVALID_FORMAT', $file['name']),
                                      __FUNCTION__);
            }

            if ($extractToDir) {
                $dest = $dest . $filename;
            }

            if ($extractToDir && !Jaws_Utils::mkdir($dest)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $dest),
                                      __FUNCTION__);
            }

            if (!Jaws_Utils::is_writable($dest)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', $dest),
                                      __FUNCTION__);
            }

            $archive = File_Archive::readArchive($ext, $file['tmp_name']);
            if (PEAR::isError($archive)) {
                return new Jaws_Error($archive->getMessage(),
                                      __FUNCTION__);
            }
            $writer = File_Archive::_convertToWriter($dest);
            $result = $archive->extract($writer);
            if (PEAR::isError($result)) {
                return new Jaws_Error($result->getMessage(),
                                      __FUNCTION__);
            }

            //@unlink($file['tmp_name']);
        }

        return true;
    }

    /**
     * Get referrer host
     *
     * @access  public
     * @return  string  Referrer host
     */
    static function getReferrerHost()
    {
        $referrer = @parse_url($_SERVER['HTTP_REFERER']);
        if (array_key_exists('port', $referrer)) {
            $referrer = $referrer['host'] . ':' . $referrer['port'];
        } elseif (array_key_exists('host', $referrer)) {
            $referrer = $referrer['host'];
        } else {
            $referrer = $_SERVER['HTTP_HOST'];
        }

        return $referrer;
    }

    /**
     * Get information of remote IP address
     *
     * @access  public
     * @return  array   include proxy and client ip addresses
     */
    static function GetRemoteAddress()
    {
        static $proxy, $client;

        if (!isset($proxy) and !isset($client)) {
            if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
                $direct = $_SERVER['REMOTE_ADDR'];
            } else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
                $direct = $_ENV['REMOTE_ADDR'];
            } else if (@getenv('REMOTE_ADDR')) {
                $direct = getenv('REMOTE_ADDR');
            }

            $proxy_flags = array('HTTP_CLIENT_IP',
                                 'HTTP_X_FORWARDED_FOR',
                                 'HTTP_X_FORWARDED',
                                 'HTTP_FORWARDED_FOR',
                                 'HTTP_FORWARDED',
                                 'HTTP_VIA',
                                 'HTTP_X_COMING_FROM',
                                 'HTTP_COMING_FROM',
                                );

            $client = '';
            foreach ($proxy_flags as $flag) {
                if (!empty($_SERVER) && isset($_SERVER[$flag])) {
                    $client = $_SERVER[$flag];
                    break;
                } else if (!empty($_ENV) && isset($_ENV[$flag])) {
                    $client = $_ENV[$flag];
                    break;
                } else if (@getenv($flag)) {
                    $client = getenv($flag);
                    break;
                }
            }

            if (empty($client)) {
                $proxy  = '';
                $client = $direct;
            } else {
                $is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $client, $regs);
                $client = $is_ip? $regs[0] : '';
                $proxy  = $direct;
            }

        }

        return array('proxy' => $proxy, 'client' => $client);
    }

    /**
     * Returns an array of languages
     *
     * @access  public
     * @param   bool    $use_data_lang  Include language added into Jaws data directory
     * @return  array   A list of available languages
     */
    static function GetLanguagesList($use_data_lang = true)
    {
        static $langs;
        if (!isset($langs)) {
            $langs = array('en' => 'International English');
            $langdir = ROOT_JAWS_PATH . 'languages/';
            $files = @scandir($langdir);
            if ($files !== false) {
                foreach($files as $file) {
                    if ($file{0} != '.'  && is_dir($langdir . $file)) {
                        if (is_file($langdir.$file.'/FullName')) {
                            $fullname = implode('', @file($langdir.$file.'/FullName'));
                            if (!empty($fullname)) {
                                $langs[$file] = $fullname;
                            }
                        }
                    }
                }
                asort($langs);
            }
        }

        if ($use_data_lang) {
            static $dLangs;
            if (!isset($dLangs)) {
                $dLangs = array();
                $langdir = ROOT_DATA_PATH . 'languages/';
                $files = @scandir($langdir);
                if ($files !== false) {
                    foreach($files as $file) {
                        if ($file{0} != '.'  && is_dir($langdir . $file)) {
                            if (is_file($langdir.$file.'/FullName')) {
                                $fullname = implode('', @file($langdir.$file.'/FullName'));
                                if (!empty($fullname)) {
                                    $dLangs[$file] = $fullname;
                                }
                            }
                        }
                    }
                }
                $dLangs = array_unique(array_merge($langs, $dLangs));
                asort($dLangs);
            }

            return $dLangs;
        }

        return $langs;
    }

    /**
     * Get a list of the available themes
     *
     * @access  public
     * @param   int     $locality   Theme locality(0: native theme, 1: unnative theme)
     * @param   string  $theme      Theme name
     * @return  array   An array of themes information
     */
    static function GetThemesInfo($locality = null, $theme = null)
    {
        if (!function_exists('is_vaild_theme')) {
            /**
             * is theme valid?
             */
            function is_vaild_theme(&$item, $key, $path)
            {
                if (!file_exists($path . $item . DIRECTORY_SEPARATOR. 'Layout.html')) {
                    $item = '';
                }
                return true;
            }
        }

        static $themes;
        if (!isset($themes)) {
            $themes = array(0 => array(), 1 => array());
            $lThemes = array_map('basename', glob(JAWS_THEMES. '*', GLOB_ONLYDIR));
            array_walk($lThemes, 'is_vaild_theme', JAWS_THEMES);
            $lThemes = array_flip(array_filter($lThemes));
            foreach($lThemes as $tname => $key) {
                $themes[0][$tname] = array(
                    'name'  => $tname,
                    'title' => $tname,
                    'desc'  => '',
                    'image' => '',
                    'version' => '0.1',
                    'license' => '',
                    'authors' => array(),
                    'deps'      => '',
                    'copyright' => '',
                    'download'  => 1,
                );
                if (file_exists(JAWS_THEMES. $tname. '/example.png')) {
                    $themes[0][$tname]['image'] = Jaws::getInstance()->getThemeURL("$tname/example.png");
                }

                $iniFile = JAWS_THEMES. $tname. '/Info.ini';
                if (file_exists($iniFile)) {
                    $tInfo = @parse_ini_file($iniFile, true);
                    if (!empty($tInfo) && array_key_exists('info', $tInfo)) {
                        $themes[0][$tname] = array_merge($themes[0][$tname], $tInfo['info']);
                    }
                }
            }
            unset($lThemes);

            if (JAWS_THEMES != JAWS_BASE_THEMES) {
                $rThemes = array_map('basename', glob(JAWS_BASE_THEMES. '*', GLOB_ONLYDIR));
                array_walk($rThemes, 'is_vaild_theme', JAWS_BASE_THEMES);
                $rThemes = array_flip(array_filter($rThemes));
                foreach($rThemes as $tname => $key) {
                    $themes[1][$tname] = array(
                        'name'  => $tname,
                        'title' => $tname,
                        'desc'  => '',
                        'image' => '',
                        'version' => '0.1',
                        'license' => '',
                        'authors' => array(),
                        'deps'      => '',
                        'copyright' => '',
                        'download'  => 0,
                    );
                    if (file_exists(JAWS_BASE_THEMES. $tname. '/example.png')) {
                        $themes[1][$tname]['image'] = Jaws::getInstance()->getThemeURL("$tname/example.png", true, true);
                    }

                    $iniFile = JAWS_BASE_THEMES. $tname. '/Info.ini';
                    if (file_exists($iniFile)) {
                        $tInfo = @parse_ini_file($iniFile, true);
                        if (!empty($tInfo) && array_key_exists('info', $tInfo)) {
                            $themes[1][$tname] = array_merge($themes[1][$tname], $tInfo['info']);
                        }
                    }
                }
                unset($rThemes);
            }
        }

        return is_null($locality)? $themes : (is_null($theme)? $themes[$locality] : @$themes[$locality][$theme]);
    }

    /**
     * Providing download file
     *
     * @access  public
     * @param   string  $fpath      File path
     * @param   string  $fname      File name
     * @param   string  $mimetype   File mime type
     * @param   int     $expires    Max age of expire
     * @param   string  $inline     Inline disposition?
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    static function Download($fpath, $fname, $mimetype = '', $expires = 0, $inline = true)
    {
        if (false === $fhandle = @fopen($fpath, 'rb')) {
            return false;
        }

        $fsize  = @filesize($fpath);
        $fstart = 0;
        $fstop  = $fsize - 1;

        if (isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])) {
            $frange = explode('-', substr($_SERVER['HTTP_RANGE'], strlen('bytes=')));
            $fstart = (int) $frange[0];
            if (isset($frange[1]) && ($frange[1] > 0)) {
                $fstop = (int) $frange[1];
            }

            header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 206 Partial Content");
            header('Content-Range: bytes '.$fstart.'-'.$fstop.'/'.$fsize);
        }

        // ranges unit
        header("Accept-Ranges: bytes");

        // cache control
        if (empty($expires)) {
            header('Cache-Control: no-store'); // no cache
        } else {
            header("Cache-Control: max-age=$expires");
        }
        // content mime type
        $mimetype = empty($mimetype)? Jaws_Utils::mime_extension_type($fpath)['mime'] : $mimetype;
        header('Content-Type: '. $mimetype);
        // content disposition and filename
        $disposition = $inline? 'inline' : 'attachment';
        header("Content-Disposition: $disposition; filename=$fname");
        // content length
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: '.($fstop - $fstart + 1));

        //jump to start position
        if ($fstart > 0) {
            fseek($fhandle, $fstart);
        }

        $fposition = $fstart;
        while (!feof($fhandle) &&
               !connection_aborted() &&
               (connection_status() == 0) &&
               $fposition <= $fstop)
        {
            $fposition += 64*1024; //64 kbytes
            print(fread($fhandle, 64*1024));
            flush();
        }
        fclose($fhandle);
        return true;
    }

    /**
     * Detect MIME Content-type by extension for a file
     *
     * @access  public
     * @param   string  $filename   File name
     * @return  string  Returns the content type in MIME format
     * @see     https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    static function mime_extension_type($filename)
    {
        static $mime_types;
        if (!isset($mime_types)) {
            $mime_types = array(
                // directory/folder
                JAWS_FILE_TYPE['FOLDER'] => array(
                    'folder' => 'inode/directory',
                ),
                // archive
                JAWS_FILE_TYPE['ARCHIVE'] => array(
                    'tar'   => 'application/x-tar',
                    'zip'   => 'application/zip',
                    'rar'   => 'application/x-rar-compressed',
                    '7z'    => 'application/x-7z-compressed',
                    'bz'    => 'application/x-bzip',
                    'bz2'   => 'application/x-bzip2',
                    'gz'    => 'application/x-gzip',
                ),
                // audio
                JAWS_FILE_TYPE['AUDIO'] => array(
                    'mid'   => 'audio/midi',
                    'm4a'   => 'audio/mp4',
                    'mp4a'  => 'audio/mp4',
                    'mp3'   => 'audio/mpeg',
                    'mpga'  => 'audio/mpeg',
                    'ogg'   => 'audio/ogg',
                    'weba'  => 'audio/webm',
                    'aac'   => 'audio/x-aac',
                    'mka'   => 'audio/x-matroska',
                    'wma'   => 'audio/x-ms-wma',
                    'wav'   => 'audio/x-wav',
                ),
                // font
                JAWS_FILE_TYPE['FONT'] => array(
                    'otf'   => 'font/otf',
                    'ttf'   => 'font/ttf',
                    'woff'  => 'font/woff',
                    'woff2' => 'font/woff2',
                ),
                // image
                JAWS_FILE_TYPE['IMAGE'] => array(
                    'bmp'   => 'image/bmp',
                    'gif'   => 'image/gif',
                    'jpeg'  => 'image/jpeg',
                    'jpg'   => 'image/jpeg',
                    'png'   => 'image/png',
                    'svg'   => 'image/svg+xml',
                    'tiff'  => 'image/tiff',
                    'webp'  => 'image/webp',
                    'ico'   => 'image/x-icon',
                    'pcx'   => 'image/x-pcx',
                ),
                // text
                JAWS_FILE_TYPE['TEXT'] => array(
                    'css'   => 'text/css',
                    'csv'   => 'text/csv',
                    'html'  => 'text/html',
                    'htm'   => 'text/html',
                    'txt'   => 'text/plain',
                    'text'  => 'text/plain',
                    'conf'  => 'text/plain',
                    'log'   => 'text/plain',
                    'ini'   => 'text/plain',
                    'php'   => 'text/x-php',
                    'java'  => 'text/x-java-source',
                    'opml'  => 'text/x-opml',
                    'vcf'   => 'text/x-vcard',
                ),
                // video
                JAWS_FILE_TYPE['VIDEO'] => array(
                    '3gp'   => 'video/3gpp',
                    'mp4'   => 'video/mp4',
                    'mpg4'  => 'video/mp4',
                    'mpeg'  => 'video/mpeg',
                    'mpg'   => 'video/mpeg',
                    'ogv'   => 'video/ogg',
                    'mov'   => 'video/quicktime',
                    'webm'  => 'video/webm',
                    'flv'   => 'video/x-flv',
                    'mkv'   => 'video/x-matroska',
                    'avi'   => 'video/x-msvideo',
                ),
                // unknown
                JAWS_FILE_TYPE['UNKNOWN'] => array(
                    'atom'  => 'application/atom+xml',
                    'jar'   => 'application/java-archive',
                    'js'    => 'application/javascript',
                    'json'  => 'application/json',
                    'pdf'   => 'application/pdf',
                    'rss'   => 'application/rss+xml',
                    'apk'   => 'application/vnd.android.package-archive',
                    'wsdl'  => 'application/wsdl+xml',
                    'lnk'   => 'application/x-ms-shortcut',
                    'sql'   => 'application/x-sql',
                    'swf'   => 'application/x-shockwave-flash',
                    'xhtml' => 'application/xhtml+xml',
                    'xml'   => 'application/xml',
                    'xsl'   => 'application/xml',
                    'dtd'   => 'application/xml-dtd',
                    'xslt'  => 'application/xslt+xml',
                ),
            );
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        foreach (JAWS_FILE_TYPE as $type) {
            if (array_key_exists($ext, $mime_types[$type])) {
                return array(
                    'mime' => $mime_types[$type][$ext],
                    'type' => $type
                );
            }
        }

        return array(
            'mime' => 'application/octet-stream',
            'type' => JAWS_FILE_TYPE['UNKNOWN']
        );
    }

}