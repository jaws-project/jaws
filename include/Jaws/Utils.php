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
     * @param   array   $complexity Determine which type of characters must include in random text
     * @return  string  Random text
     */
    static function RandomText($length = 5, $complexity = array())
    {
        $string = '';
        $lngmin = 0;

        if (array_key_exists('collection', $complexity) && !empty($complexity['collection'])) {
            $allPossibleChars = $complexity['collection'];
        } else {
            unset($complexity['collection']);
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

            $allPossibleChars = '';
            foreach ($complexity as $key => $keyValue) {
                if ($keyValue) {
                    $lngmin ++;
                    $string.= $possible[$key][mt_rand(0, strlen($possible[$key])-1)];
                    $allPossibleChars.= $possible[$key];
                }
            }
        }

        $length = ($length < $lngmin)? $lngmin : $length;
        $string.= Jaws_UTF8::substr(Jaws_UTF8::str_shuffle($allPossibleChars), 0, $length - strlen($string));
        return Jaws_UTF8::str_shuffle($string);
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
            // server port
            if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
                $parts['port'] = (int)$_SERVER['HTTP_X_FORWARDED_PORT'];
            } else {
                $parts['port'] = (int)$_SERVER['SERVER_PORT'];
            }

            //$parts['host'] = $_SERVER['SERVER_NAME'];
            $parts['host'] = current(explode(':', $_SERVER['HTTP_HOST']));
            // server port
            if (($parts['scheme'] == 'http'  && $parts['port'] == 80) ||
                ($parts['scheme'] == 'https' && $parts['port'] == 443)
            ) {
                $parts['port'] = '';
            }

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

            // base-script
            $parts['script'] = BASE_SCRIPT;

            $parts['resource'] = '';
            if (isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL'])) {
                $parts['resource'] = $_SERVER['REDIRECT_URL'];
            } elseif (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
                $parts['resource'] = $_SERVER['REQUEST_URI'];
            } else {
                $parts['resource'] = $_SERVER['PHP_SELF'];
            }
            $parts['resource'] = strtok($parts['resource'], '?');
            $parts['resource'] = substr($parts['resource'], strlen($parts['path']));
            if (1 === stripos($parts['resource'], BASE_SCRIPT)) {
                $parts['resource'] = substr($parts['resource'], strlen(BASE_SCRIPT) + 1);
            }
            //$parts['resource'] = '/'. ltrim($parts['resource'], '/');

            $parts['query'] = '';
            if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
                $parts['query'] = $_SERVER['REDIRECT_QUERY_STRING'];
            } elseif (isset($_SERVER['QUERY_STRING'])) {
                $parts['query'] = $_SERVER['QUERY_STRING'];
            }
            $query = array();
            parse_str($parts['query'], $query);
            //_log_var_dump($query);
            //_log_var_dump($parts);
        }

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
        $url = $site_url['path'];
        if (!$rel_url) {
            if (!empty($site_url['port'])) {
                $site_url['port'] = ':' . $site_url['port'];
            }
            $url = $site_url['scheme']. '://'. $site_url['host']. $site_url['port']. $url;
        }

        $suffix = implode('/', array_map('rawurlencode', explode('/', $suffix)));
        return $url . $suffix;
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
        static $addr;

        if (!isset($addr)) {
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

            $client = @inet_pton($client);
            $direct = @inet_pton($direct);
            if (empty($client)) {
                $proxy  = '';
                $client = (string)$direct;
            } else {
                $proxy  = (string)$direct;
            }

            $addr = array(
                'proxy'  => rtrim(base64_encode($proxy),  '='),
                'client' => rtrim(base64_encode($client), '=')
            );
        }

        return $addr;
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
                    if ($file[0] != '.'  && is_dir($langdir . $file)) {
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
                        if ($file[0] != '.'  && is_dir($langdir . $file)) {
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