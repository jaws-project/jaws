<?php
/**
 * Main application, the core
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws
{
    /**
     * The processed main request is in index page
     *
     * @var     bool
     * @access  public
     */
    var $mainIndex = false;

    /**
     * The main gadget
     * @var     string
     * @access  public
     */
    var $mainGadget = '';

    /**
     * The main action
     * @var     string
     * @access  public
     */
    var $mainAction = '';

    /**
     * Main request is running?
     * @var     bool
     * @access  public
     */
    var $inMainRequest = false;

    /**
     * The requested gadget
     * @var     string
     * @access  public
     */
    var $requestedGadget = '';

    /**
     * The requested action
     * @var     string
     * @access  public
     */
    var $requestedAction = '';

    /**
     * The section requested in
     * @var     string
     * @access  public
     */
    var $requestedSection = '';

    /**
     * The requested action mode
     * @var     string
     * @access  public
     */
    var $requestedActionMode = '';

    /**
     * Defines of the Jaws
     *
     * @var     array
     * @access  private
     */
    private $defines = array();

    /**
     * Default preferences
     * @var     array
     * @access  private
     */
    var $_Preferences = array(
        'theme'    => array('name' => 'jaws', 'locality' => 0),
        'language' => 'en',
        'editor'   => null,
        'timezone' => null,
        'site_timezone' => null,
        'calendar' => 'Gregorian',
    );

    /**
     * Browser flag
     * @var     string
     * @access  protected
     */
    var $_BrowserFlag = '';

    /**
     * Browser HTTP_ACCEPT_ENCODING
     * @var     string
     * @access  protected
     */
    var $_BrowserEncoding = '';

    /**
     * Should application use layout?
     * @var     bool
     * @access  protected
     */
    var $_UseLayout = false;

    /**
     * Store plugin object for later use so we aren't running
     * around with multiple copies
     * @var array
     * @access  protected
     */
    var $_Plugins = array();

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->loadObject('Jaws_Request', 'request', true);
        $this->loadObject('Jaws_Registry', 'Registry');
        $this->loadObject('Jaws_ACL', 'ACL');
        $this->loadObject('Jaws_Listener', 'Listener');
        $this->loadObject('Jaws_URLMapping', 'Map');
        $this->define('', 'script', JAWS_SCRIPT);
    }

    /**
     * Creates the Jaws instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance()
    {
        static $objJaws;
        if (!isset($objJaws)) {
            $objJaws = new Jaws();
        }

        return $objJaws;
    }

    /**
     * Initializes the Jaws application object
     *
     * @access  public
     */
    function init()
    {
        $this->Map->Init();
        $this->Session = Jaws_Session::factory();
        $this->Session->Init();
        $logged_user = $GLOBALS['app']->Session->GetAttributes('user', 'groups');
        $this->ACL->Init($logged_user['user'], $logged_user['groups']);
        $this->loadPreferences();
    }

    /**
     * Load the default application preferences(language, theme, ...)
     *
     * @access  public
     * @param   array   $preferences        Default preferences
     * @param   bool    $loadFromDatabase   Load preferences from database
     * @return  void
     */
    function loadPreferences($preferences = array(), $loadFromDatabase = true)
    {
        if ($loadFromDatabase) {
            $user   = $this->Session->GetAttribute('user');
            $layout = $this->Session->GetAttribute('layout');
            $this->_Preferences = array(
                'theme'         => (array)$this->Registry->fetch('theme', 'Settings'),
                'editor'        => $this->Registry->fetchByUser($user,   'editor',   'Settings'),
                'timezone'      => $this->Registry->fetchByUser($user,   'timezone', 'Settings'),
                'site_timezone' => $this->Registry->fetch('timezone', 'Settings'),
                'calendar'      => $this->Registry->fetchByUser($layout, 'calendar', 'Settings'),
            );

            if (JAWS_SCRIPT == 'index') {
                $this->_Preferences['language'] = $this->Registry->fetchByUser($layout, 'site_language', 'Settings');
            } else {
                $this->_Preferences['language'] = $this->Registry->fetchByUser($user, 'admin_language', 'Settings');
            }
        }

        // merge default with passed preferences
        $this->_Preferences = array_merge($this->_Preferences, $preferences);
        // filter non validate character
        $this->_Preferences['theme']['name'] = preg_replace(
            '/[^[:alnum:]_\-]/', '', $this->_Preferences['theme']['name']
        );
        $this->_Preferences['theme']['locality'] = (int)$this->_Preferences['theme']['locality'];
        $this->_Preferences['language'] = preg_replace('/[^[:alnum:]_\-]/', '', $this->_Preferences['language']);
        $this->_Preferences['editor'] = preg_replace('/[^[:alnum:]_\-]/', '', $this->_Preferences['editor']);
        $this->_Preferences['calendar'] = preg_replace('/[^[:alnum:]_]/',  '', $this->_Preferences['calendar']);

        // load the language translates
        Jaws_Translate::getInstance()->Init($this->_Preferences['language']);

        // pass preferences to client
        $this->define('', 'preferences', $this->_Preferences);
    }

    /**
     * Setup the applications cache.
     *
     * @return  void
     * @access  public
     */
    function InstanceCache()
    {
        require_once JAWS_PATH . 'include/Jaws/Cache.php';
        $this->Cache =& Jaws_Cache::factory();
    }

    /**
     * Setup the applications Layout object.
     *
     * @return  void
     * @access  public
     */
    function InstanceLayout()
    {
        $this->loadObject('Jaws_Layout', 'Layout');
        $this->_UseLayout = true;
    }

    /**
     * Get the boolean answer if application is using a layout
     *
     * @return  bool
     * @access  public
     */
    function IsUsingLayout()
    {
        return $this->_UseLayout;
    }

    /**
     * Get the name of the Theme
     *
     * @access  public
     * @param   bool    $rel_url relative url
     * @return  string The name of the theme
     */
    function GetTheme($rel_url = true)
    {
        static $theme;
        $currentTheme = $this->_Preferences['theme'];
        if (!isset($theme) ||
            $theme['locality'] != $currentTheme['locality'] ||
            $theme['name'] != $currentTheme['name']
        ) {
            // Check if valid theme name
            if (!preg_match('/^[[:alnum:]_\.-]+$/', $currentTheme['name'])) {
                return Jaws_Error::raiseError(_t('GLOBAL_ERROR_INVALID_NAME', 'Theme'));
            }

            $theme = array();
            $theme['name'] = $currentTheme['name'];
            $theme['locality'] = $currentTheme['locality'];
            $theme['path'] = JAWS_THEMES. $currentTheme['name'] . '/';
            if (!is_file($theme['path']. 'Layout.html')) {
                $theme['url']    = $this->getThemeURL($currentTheme['name'] . '/', $rel_url, true);
                $theme['path']   = JAWS_BASE_THEMES. $currentTheme['name'] . '/';
                $theme['exists'] = @is_file($theme['path']. 'Layout.html');
            } else {
                $theme['url']    = $this->getThemeURL($currentTheme['name'] . '/', $rel_url);
                $theme['exists'] = true;
            }
        }

        return $theme;
    }

    /**
     * Set default theme
     *
     * @access  public
     * @param   string  $theme      Theme name
     * @param   int     $locality   Theme locality(0,1)
     * @return  void
     */
    function SetTheme($theme, $locality = 0)
    {
        $this->_Preferences['theme'] = array('name' => $theme, 'locality' => (int)$locality);
    }

    /**
     * Get the default language
     *
     * @access  public
     * @return  string The default language
     */
    function GetLanguage()
    {
        $language = $this->_Preferences['language'];
        // Check if valid language name
        if (strpos($language, '..') !== false ||
            strpos($language, '%') !== false ||
            strpos($language, '\\') !== false ||
            strpos($language, '/') !== false) {
                return new Jaws_Error(_t('GLOBAL_ERROR_INVALID_NAME', 'GetLanguage'),
                                      'Getting language name');
        }
        return $language;
    }

    /**
     * Get the default editor
     *
     * @access  public
     * @return  string The default language
     */
    function GetEditor()
    {
        return $this->_Preferences['editor'];
    }

    /**
     * Get Browser flag
     *
     * @access  public
     * @return  string The type of browser
     */
    function GetBrowserFlag()
    {
        if (empty($this->_BrowserFlag)) {
            require_once PEAR_PATH. 'Net/Detect.php';
            $bFlags = explode(',', $this->Registry->fetch('browsers_flag', 'Settings'));
            $this->_BrowserFlag = Net_UserAgent_Detect::getBrowser($bFlags);
        }

        return $this->_BrowserFlag;
    }

    /**
     * Get the default calendar
     *
     * @access  public
     * @return  string The default language
     */
    function GetCalendar()
    {
        return $this->_Preferences['calendar'];
    }

    /**
     * Get the available authentication types
     *
     * @access  public
     * @return  array  Array with available authentication types
     */
    function GetAuthTypes()
    {
        $path = JAWS_PATH . 'include/Jaws/Auth';
        if (is_dir($path)) {
            $authtypes = array();
            $dir = scandir($path);
            foreach ($dir as $authtype) {
                if (stristr($authtype, '.php')) {
                    $authtype = str_replace('.php', '', $authtype);
                    $authtypes[$authtype] = $authtype;
                }
            }

            return $authtypes;
        }

        return false;
    }

    /**
     * Prepares the jaws Editor
     *
     * @access  public
     * @param   string  $gadget  Gadget that uses the editor (usable for plugins)
     * @param   string  $name    Name of the editor
     * @param   string  $value   Value of the editor/content (optional)
     * @param   bool    $filter  Convert special characters to HTML entities
     * @return  object  The editor in /gadgets/Settings/editor
     */
    function &LoadEditor($gadget, $name, $value = '', $filter = true)
    {
        if ($filter && !empty($value)) {
            $value = Jaws_XSS::filter($value);
        }

        $editor = $this->_Preferences['editor'];
        $file   = JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
        if (!file_exists($file)) {
            $editor = 'TextArea';
            $file   = JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
        }
        $editorClass = "Jaws_Widgets_$editor";

        require_once $file;
        $editor = new $editorClass($gadget, $name, $value);

        return $editor;
    }

    /**
     * Loads/creates a class object
     *
     * @access  public
     * @param   string  $classname  Class name
     * @param   string  $property   Jaws app property name
     * @param   bool    $singleton  Get instance of singleton pattern class
     * @return  mixed   Object if success otherwise Jaws_Error on failure
     */
    function loadObject($classname, $property = '', $singleton = false)
    {
        // filter non validate character
        $classname = preg_replace('/[^[:alnum:]_]/', '', $classname);
        if (empty($property) || !isset($this->$property)) {
            $objClass = $singleton? $classname::getInstance() : new $classname();
            if (!empty($property)) {
                $this->$property = $objClass;
            }
            return $objClass;
        }

        return $this->$property;
    }

    /**
     * Loads a class from within the Jaws or gadgets directories
     *
     * @access  public
     * @param   string  $classname  Class name
     * @return  void
     */
    static function loadClass($classname)
    {
        // filter non validate character
        $classname = preg_replace('/[^[:alnum:]_]/', '', $classname);
        $classname = str_replace('_', '/', $classname);
        if (0 === strpos($classname, 'Jaws/')) {
            $file = JAWS_PATH. "include/$classname.php";
        } else {
            $file = JAWS_PATH. "gadgets/$classname.php";
        }
        if (!file_exists($file)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'Loaded class file: ' . $file);
        } else {
            require_once $file;
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded class file: ' . $file);
        }
    }

    /**
     * Verify if an image exists, if not returns a default image (unknown.png)
     *
     * @param   string Image path
     * @return  string The original path if it exists or an unknow.png path
     * @access  public
     */
    static function CheckImage($path)
    {
        if (is_file($path)) {
            return $path;
        }

        return 'images/unknown.png';
    }

    /**
     * Returns the URL of the site
     *
     * @access  public
     * @param   string  $suffix     Suffix for adding to end of URL
     * @param   bool    $rel_url    Relative url
     * @return  string  Site's URL
     */
    function getSiteURL($suffix = '', $rel_url = true)
    {
        static $site_url;
        if (!isset($site_url)) {
            $site_url = array();
            // server schema
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $site_url['scheme'] = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
            } else {
                $site_url['scheme'] = empty($_SERVER['HTTPS'])? 'http' : 'https';
            }

            //$site_url['host'] = $_SERVER['SERVER_NAME'];
            $site_url['host'] = current(explode(':', $_SERVER['HTTP_HOST']));
            // server port
            $site_url['port'] = $_SERVER['SERVER_PORT']==80? '' : (':'.$_SERVER['SERVER_PORT']);

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

            $site_url['path'] = substr($path, 0, stripos($path, BASE_SCRIPT)-1);
            $site_url['path'] = explode('/', $site_url['path']);
            $site_url['path'] = implode('/', array_map('rawurlencode', $site_url['path']));
        }

        $url = $site_url['path'];
        if (!$rel_url) {
            $url = $site_url['scheme']. '://'. $site_url['host']. $site_url['port']. $url;
        }

        $url = rtrim($url, '/');
        $suffix = is_bool($suffix)? array() : explode('/', $suffix);
        $suffix = implode('/', array_map('rawurlencode', $suffix));
        return $url . $suffix;
    }

    /**
     * Returns the URL of the data
     *
     * @access  public
     * @param   string  $suffix    suffix part of url
     * @param   bool    $rel_url   relative url
     * @param   bool    $base_data use JAWS_BASE_DATA instead of JAWS_DATA
     * @return  string  Related URL to data directory
     */
    function getDataURL($suffix = '', $rel_url = true, $base_data = false)
    {
        if (!defined('JAWS_DATA_URL') || $base_data) {
            $url = substr($base_data? JAWS_BASE_DATA : JAWS_DATA, strlen(JAWS_PATH));
            $url = str_replace('\\', '/', $url);
            if (!$rel_url) {
                $url = $this->getSiteURL('/' . $url);
            }
        } else {
            $url = JAWS_DATA_URL;
        }

        $suffix = is_bool($suffix)? array() : explode('/', $suffix);
        $suffix = implode('/', array_map('rawurlencode', $suffix));
        return $url . $suffix;
    }

    /**
     * Returns the URL of the themes directory
     *
     * @access  public
     * @param   string  $suffix         suffix part of url
     * @param   bool    $rel_url        relative url
     * @param   bool    $base_themes    use JAWS_BASE_DATA instead of JAWS_DATA
     * @return  string  Related URL to themes directory
     */
    function getThemeURL($suffix = '', $rel_url = true, $base_themes = false)
    {
        if (!defined('JAWS_THEMES_URL') || $base_themes) {
            $url = substr($base_themes? JAWS_BASE_THEMES : JAWS_THEMES, strlen(JAWS_PATH));
            $url = str_replace('\\', '/', $url);
            if (!$rel_url) {
                $url = $this->getSiteURL('/' . $url);
            }
        } else {
            $url = JAWS_THEMES_URL;
        }

        $suffix = is_bool($suffix)? array() : explode('/', $suffix);
        $suffix = implode('/', array_map('rawurlencode', $suffix));
        return $url . $suffix;
    }

    /**
     * Executes the autoload gadgets
     *
     * @access  public
     * @return  void
     */
    function RunAutoload()
    {
        $data    = $GLOBALS['app']->Registry->fetch('gadgets_autoload_items');
        $gadgets = array_filter(explode(',', $data));
        foreach($gadgets as $gadget) {
            if (Jaws_Gadget::IsGadgetEnabled($gadget)) {
                $objGadget = Jaws_Gadget::getInstance($gadget);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }
                $objHook = $objGadget->hook->load('Autoload');
                if (Jaws_Error::IsError($objHook)) {
                    continue;
                }

                $result = $objHook->Execute();
                if (Jaws_Error::IsError($result)) {
                    //do nothing;
                }
            }
        }
    }

    /**
     * Checks if a class exists without triggering __autoload
     *
     * @param   string  $classname  Name of class
     * @return  bool    true success and false on error
     *
     * @access  public
     */
    static function classExists($classname)
    {
        return class_exists($classname, false);
    }

    /**
     * Sets a define
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   string  $key        Define name
     * @param   string  $value      Define value
     * @return  void
     */
    function define($component, $key, $value = '')
    {
        if (empty($key)) {
            if (!array_key_exists($component, $this->defines)) {
                $this->defines[$component] = array();
            }
        } else {
            $this->defines[$component][$key] = $value;
        }
    }

    /**
     * Get all defines of the gadget
     *
     * @access  public
     * @param   string  $component  (Optional) Component name
     * @return  array   Defines of the gadget
     */
    function defines($component = null)
    {
        if (is_null($component)) {
            return $this->defines;
        } else {
            return $this->defines[$component];
        }
    }

    /**
     * Get Browser accept encoding
     *
     * @access  public
     * @return  string The type of browser
     */
    function GetBrowserEncoding()
    {
        return $this->_BrowserEncoding;
    }

    /**
     * use native gzip compression?
     *
     * @access  private
     * @return  bool    True or False
     */
    function GZipEnabled()
    {
        static $_GZipEnabled;
        if (!isset($_GZipEnabled)) {
            $this->_BrowserEncoding = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '');
            $this->_BrowserEncoding = strtolower($this->_BrowserEncoding);
            $_GZipEnabled = true;
            if (($this->Registry->fetch('gzip_compression', 'Settings') != 'true') ||
                !extension_loaded('zlib') ||
                ini_get('zlib.output_compression') ||
                (ini_get('zlib.output_compression_level') > 0) ||
                (ini_get('output_handler') == 'ob_gzhandler') ||
                (ini_get('output_handler') == 'mb_output_handler') ||
                (strpos($this->_BrowserEncoding, 'gzip') === false))
            {
                $_GZipEnabled = false;
            }
        }

        return $_GZipEnabled;
    }

    /**
     * Is actual agent a robot?
     *
     * @access  private
     * @return  bool    True or False
     */
    function IsAgentRobot()
    {
        static $_IsRobot;
        if (!isset($_IsRobot)) {
            $_IsRobot = false;
            $robots = explode(',', $this->Registry->fetch('robots', 'Settings'));
            $robots = array_map('strtolower', $robots);
            $uagent = strtolower(Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT']));
            $ipaddr = $_SERVER['REMOTE_ADDR'];
            foreach($robots as $robot) {
                if (!empty($robot) && (($ipaddr == $robot) || (strpos($uagent, $robot) !== false))) {
                    $_IsRobot = true;
                    break;
                }
            }
        }

        return $_IsRobot;
    }

    /**
     * Get user time
     *
     * @access  private
     * @param   mixed   $time   timestamp
     * @param   string  $format date format
     * @param   bool    $default_timezone   use default timezone instead of user timezone
     * @return  bool    True or False
     */
    function UTC2UserTime($time = '', $format = '', $default_timezone = false)
    {
        $time = empty($time)? time() : $time;
        if (is_array($time)) {
            $time = mktime(isset($time[5])? $time[5] : 0,
                           isset($time[4])? $time[4] : 0,
                           isset($time[3])? $time[3] : 0,
                           isset($time[1])? $time[1] : 0,
                           isset($time[2])? $time[2] : 0,
                           $time[0]);
        }
        $time = is_numeric($time)? $time : strtotime($time);

        // GMT offset
        $timezone = $default_timezone? $this->_Preferences['timezone'] : $this->_Preferences['site_timezone'];
        if (is_numeric($timezone)) {
            $gmt_offset = $timezone * 3600;
        } else {
            @date_default_timezone_set($timezone);
            $gmt_offset = date('Z', $time);
            date_default_timezone_set('UTC');
        }
        $time = $time + $gmt_offset;
        return empty($format)? $time : date($format, $time);
    }

    /**
     * Get UTC time
     *
     * @access  private
     * @param   mixed   $time   timestamp
     * @param   string  $format date format
     * @param   bool    $default_timezone   use default timezone instead of user timezone
     * @return  bool    True or False
     */
    function UserTime2UTC($time, $format = '', $default_timezone = false)
    {
        if (is_array($time)) {
            $time = mktime(isset($time[5])? $time[5] : 0,
                           isset($time[4])? $time[4] : 0,
                           isset($time[3])? $time[3] : 0,
                           isset($time[1])? $time[1] : 0,
                           isset($time[2])? $time[2] : 0,
                           $time[0]);
        }
        $time = is_numeric($time)? $time : strtotime($time);

        // GMT offset
        $timezone = $default_timezone? $this->_Preferences['timezone'] : $this->_Timezone;
        if (is_numeric($timezone)) {
            $gmt_offset = $timezone * 3600;
        } else {
            @date_default_timezone_set($timezone);
            $gmt_offset = date('Z', $time);
            date_default_timezone_set('UTC');
        }
        $time = $time - $gmt_offset;
        return empty($format)? $time : date($format, $time);
    }


    /**
     * Manage http response code
     *
     * @access  public
     * @param   int     $code   Response code
     * @return  int     http response code
     */
    public function http_response_code($code = null)
    {
        static $response = 200;
        if (!empty($code)) {
            $response = (int)$code;
            if (http_response_code() === 200) {
                http_response_code($response);
            }
        }

        return $response;
    }

    /**
     * Overloading magic method
     *
     * @access  private
     * @param   string  $property   Property name
     * @return  mixed   Property value otherwise Null
     */
    public function __get($property)
    {
        return isset($this->$property)? $this->$property : null;
    }

}

/**
 * Convenience function to application object
 *
 * @access  public
 * @return  object  Jaws object
 */
function jaws() {
    return Jaws::getInstance();
}
