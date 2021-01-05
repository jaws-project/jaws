<?php
/**
 * Determine server operation system
 */
//define('JAWS_OS_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
/*
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
*/
/**
 * Some useful file management functions
 *
 * @category   JawsType
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2020-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_FileManagement
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
     * Creates the Jaws_FileManagement instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   string  $fmDriver   Filesystem management driver
     * @return  object  Jaws_FileManagement type object
     */
    static function getInstance($fmDriver = 'File')
    {
        $fmDriver = preg_replace('/[^[:alnum:]_\-]/', '', $fmDriver);
        if (empty($fmDriver)) {
            $fmDriver = 'File';
        }

        static $instances = array();
        if (!isset($instances[$fmDriver])) {
            $className = "Jaws_FileManagement_$fmDriver";
            $instances[$fmDriver] = new $className();
        }

        return $instances[$fmDriver];
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
     * @param   string  $dimension      resize image file to given dimension
     * @return  mixed   Returns uploaded files array on success or Jaws_Error/FALSE on failure
     */
    static function uploadFiles(
        $files, $dest = '', $allow_formats = '',
        $overwrite = true, $move_files = true, $max_size = null, $dimension = ''
    ) {
        if (empty($files) || !is_array($files)) {
            return false;
        }

        $result = array();
        if (isset($files['tmp_name'])) {
            $files = array($files);
        }

        $dest = $dest?: static::upload_tmp_dir();
        $dest = rtrim($dest, "\\/"). '/';
        if (!static::mkdir($dest, 2)) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_FAILED_CREATING_DIR'. $dest),
                __FUNCTION__
            );
        }

        $dimension = empty($dimension)? '': explode('x', $dimension);
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
                        Jaws::t('ERROR_UPLOAD_'.$file['error']),
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
                $fileExtType = static::mime_extension_type($host_filename);
                $file['type'] = $fileExtType['type'];

                // file mime-type
                $file['mime'] = @mime_content_type($file['tmp_name']);
                $file['mime'] = $file['mime']?: $fileExtType['mime'];

                $fileinfo = pathinfo($host_filename);
                if (isset($fileinfo['extension'])) {
                    if (!empty($allow_formats) && !in_array($fileinfo['extension'], $allow_formats)) {
                        return Jaws_Error::raiseError(
                            Jaws::t('ERROR_UPLOAD_INVALID_FORMAT', $host_filename),
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
                    $host_filename = $fileinfo['filename']. '_'. time(). mt_rand(). $fileinfo['extension'];
                }

                // Check if the file has been altered or is corrupted
                if (filesize($file['tmp_name']) != $file['size']) {
                    @unlink($file['tmp_name']);
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_UPLOAD_CORRUPTED', $host_filename),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE,
                        1
                    );
                }

                // resize image file
                if (!empty($dimension) && strpos($file['mime'], 'image/') !== false) {
                    $res = Jaws_Image::getInstance()
                        ->load($file['tmp_name'])
                        ->resize($dimension[0], $dimension[1])
                        ->save($file['tmp_name'])
                        ->free();
                    if (Jaws_Error::IsError($res)) {
                        return Jaws_Error::raiseError(
                            $res->getMessage(),
                            __FUNCTION__,
                            JAWS_ERROR_NOTICE,
                            1
                        );
                    }

                    // set new file size
                    $file['size'] = filesize($file['tmp_name']);
                }

                // Check if the file size exceeds defined max file size
                if (!empty($max_size) && $file['size'] > $max_size) {
                    @unlink($file['tmp_name']);
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_UPLOAD_2'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE,
                        1
                    );
                }

                $uploadfile = $dest . $host_filename;
                // On windows-systems can't rename a file to an existing destination,
                // So we must delete destination file
                if (file_exists($uploadfile)) {
                    @unlink($uploadfile);
                }
                $res = $move_files?
                    static::rename_from_file($file['tmp_name'], $uploadfile):
                    static::copy_from_file($file['tmp_name'], $uploadfile);
                if (!$res) {
                    return Jaws_Error::raiseError(
                        Jaws::t('ERROR_UPLOAD', $host_filename),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE,
                        1
                    );
                }

                static::chmod($uploadfile);
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
    static function extractFiles($files, $dest, $extractToDir = true, $overwrite = true, $max_size = null)
    {
        if (empty($files) || !is_array($files)) {
            return new Jaws_Error(Jaws::t('ERROR_UPLOAD'),
                                     __FUNCTION__);
        }

        if (isset($files['name'])) {
            $files = array($files);
        }

        require_once PEAR_PATH. 'File/Archive.php';
        foreach($files as $key => $file) {
            if ((isset($file['error']) && !empty($file['error'])) || !isset($file['name'])) {
                return new Jaws_Error(Jaws::t('ERROR_UPLOAD_'.$file['error']),
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
                return new Jaws_Error(Jaws::t('ERROR_UPLOAD_INVALID_FORMAT', $file['name']),
                                      __FUNCTION__);
            }

            if ($extractToDir) {
                $dest = $dest . $filename;
            }

            if ($extractToDir && !static::mkdir($dest)) {
                return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $dest),
                                      __FUNCTION__);
            }

            if (!static::is_writable($dest)) {
                return new Jaws_Error(Jaws::t('ERROR_FAILED_DIRECTORY_UNWRITABLE', $dest),
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
    static function download($fpath, $fname, $mimetype = '', $expires = 0, $inline = true)
    {
        if (false === $fhandle = static::fopen($fpath, 'rb')) {
            return false;
        }

        $fsize  = static::filesize($fpath);
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
            header('Cache-Control: no-store, no-cache, must-revalidate'); // no cache
            header('Pragma: no-cache');
        } else {
            header("Cache-Control: max-age=$expires");
        }
        // content mime type
        $mimetype = empty($mimetype)? static::mime_extension_type($fpath)['mime'] : $mimetype;
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
            $chunk = fread($fhandle, 64*1024); // 64k
            print($chunk);
            // if handle is stream and not local file, size of read chunk maybe not equal to 64K,
            // so we must check length of chunk
            $fposition += strlen($chunk);
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

        $ext = static::pathinfo($filename, PATHINFO_EXTENSION);
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