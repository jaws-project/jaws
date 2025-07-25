<?php
/**
 * Main application, the core
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
#[\AllowDynamicProperties]
class Jaws
{
    /**
     * The installation instance
     * @var     string
     * @access  public
     */
    var $instance = '';

    /**
     * The processed main request is in index page
     *
     * @var     bool
     * @access  public
     */
    var $mainIndex = false;

    /**
     * The main request attributes
     * @var     string
     * @access  public
     */
    public $mainRequest = array(
        'gadget' => '',
        'action' => '',
    );

    /**
     * The main action object
     * @var     Jaws_Gadget_Action
     * @access  public
     */
    public $mainAction = null;

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
     * Shared data with clients(browsers, apps, ...)
     *
     * @var     array
     * @access  private
     */
    private $exports = array();

    /**
     * Hierarchical structure navigation parts
     * @var     array
     * @access  public
     */
    public $breadcrumb = array(
    );

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
    private function __construct()
    {
    }

    /**
     * Creates the Jaws instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance($create_instance = true)
    {
        static $objJaws = false;
        if ($create_instance && empty($objJaws)) {
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
        $this->export('', JAWS_EXPORT_UNTYPE, 'script', JAWS_SCRIPT);
        $this->export('', JAWS_EXPORT_UNTYPE, 'base', Jaws_Utils::getBaseURL('/'));
        $this->export('', JAWS_EXPORT_UNTYPE, 'relBase', Jaws_Utils::getBaseURL('/'));
        $this->export('', JAWS_EXPORT_UNTYPE, 'absBase', Jaws_Utils::getBaseURL('/', false));
        $this->export('', JAWS_EXPORT_UNTYPE, 'relDataURL', $this->getDataURL('', true));
        $this->export('', JAWS_EXPORT_UNTYPE, 'absDataURL', $this->getDataURL('', false));
        $this->export('', JAWS_EXPORT_UNTYPE, 'requestedURL', Jaws_Utils::getRequestURL());
        $this->export('', JAWS_EXPORT_UNTYPE, 'gzip', $this->GZipEnabled());

        // FileSystem management
        $this->fileManagement = Jaws_FileManagement::getInstance(
            $this->registry->fetch('fm_driver', 'Settings')
        );
        $this->map->init();
        $this->session->init();
        $this->acl->init($this->session->user->id, array_keys($this->session->user->groups));
        $this->loadPreferences();

        $JCrypt = Jaws_Crypt::getInstance();
        if (!Jaws_Error::IsError($JCrypt)) {
            $this->export('', JAWS_EXPORT_UNTYPE, 'pubkey', base64_encode($JCrypt->getPublic()));
        }
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
            // fetch installation instance
            $this->instance = $this->registry->fetch('instance', 'Settings');

            $user   = $this->session->user->id;
            //FIXME:: there is no layout attribute!
            $layout = $this->session->user->layout;
            $this->_Preferences = array(
                'theme'         => (array)$this->registry->fetch('theme', 'Settings'),
                'editor'        => $this->registry->fetchByUser($user,   'editor',   'Settings'),
                'timezone'      => $this->registry->fetchByUser($user,   'timezone', 'Settings'),
                'site_timezone' => $this->registry->fetch('timezone', 'Settings'),
                'calendar'      => $this->registry->fetchByUser($layout, 'calendar', 'Settings'),
            );

            if (JAWS_SCRIPT == 'index') {
                $this->_Preferences['language'] = $this->registry->fetchByUser($layout, 'site_language', 'Settings');
            } else {
                $this->_Preferences['language'] = $this->registry->fetchByUser($user, 'admin_language', 'Settings');
            }
        }

        // merge default with passed preferences
        $this->_Preferences = array_merge($this->_Preferences, $preferences);
        // filter non validate character
        $this->_Preferences['theme']['name'] = preg_replace(
            '/[^[:alnum:]_\-]/', '', $this->_Preferences['theme']['name']
        );
        $this->_Preferences['theme']['locality'] = (int)$this->_Preferences['theme']['locality'];
        $this->_Preferences['language'] = preg_replace('/[^[:alnum:]_\-]/', '', (string)$this->_Preferences['language']);
        $this->_Preferences['editor'] = preg_replace('/[^[:alnum:]_\-]/', '', (string)$this->_Preferences['editor']);
        $this->_Preferences['calendar'] = preg_replace('/[^[:alnum:]_]/',  '', (string)$this->_Preferences['calendar']);

        // load the language translates
        Jaws_Translate::getInstance()->init($this->_Preferences['language']);

        // pass preferences to client
        $this->export('', JAWS_EXPORT_UNTYPE, $this->_Preferences);

        // pass user session data to client
        $this->export(
            '',
            JAWS_EXPORT_SESSION,
            array(
                'user' => array(
                    'id'        => $this->session->user->id,
                    'username'  => $this->session->user->username,
                    'superadmin'=> $this->session->user->superadmin,
                    'nickname'  => $this->session->user->nickname,
                    'logged'    => $this->session->user->logged,
                    'avatar'    => $this->session->user->avatar,
                ),
            )
        );
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
                return Jaws_Error::raiseError(Jaws::t('ERROR_INVALID_NAME', 'Theme'));
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
    function getLanguage()
    {
        $language = $this->_Preferences['language'];
        // Check if valid language name
        if (strpos($language, '..') !== false ||
            strpos($language, '%') !== false ||
            strpos($language, '\\') !== false ||
            strpos($language, '/') !== false) {
                return new Jaws_Error(Jaws::t('ERROR_INVALID_NAME', 'getLanguage'),
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
    function getEditor()
    {
        return $this->_Preferences['editor'];
    }

    /**
     * Get Browser flag
     *
     * @access  public
     * @return  string The type of browser
     */
    function getBrowserFlag()
    {
        if (empty($this->_BrowserFlag)) {
            require_once PEAR_PATH. 'Net/Detect.php';
            $bFlags = explode(',', $this->registry->fetch('browsers_flag', 'Settings'));
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
    function getCalendar()
    {
        return $this->_Preferences['calendar'];
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
    function &loadEditor($gadget, $name, $value = '', $filter = true)
    {
        if ($filter && !empty($value)) {
            $value = Jaws_XSS::filter($value);
        }

        $editor = $this->_Preferences['editor'];
        $file   = ROOT_JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
        if (!file_exists($file)) {
            $editor = 'TextArea';
            $file   = ROOT_JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
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
            $file = ROOT_JAWS_PATH. "include/$classname.php";
        } else {
            $file = ROOT_JAWS_PATH. "gadgets/$classname.php";
        }
        if (!file_exists($file)) {
            if (false !== strpos($classname, 'Jaws/XTemplate/Tags/')) {
                // no log if template tag not found
                return;
            }
            $GLOBALS['log']->Log(JAWS_ERROR, 'Loaded class file: ' . $file);
        } else {
            require_once $file;
            $GLOBALS['log']->Log(JAWS_DEBUG, 'Loaded class file: ' . $file);
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
        return Jaws_Utils::getBaseURL($suffix, $rel_url);
    }

    /**
     * Returns the URL of the data
     *
     * @access  public
     * @param   string  $suffix    suffix part of url
     * @param   bool    $rel_url   relative url
     * @param   bool    $base_data use JAWS_BASE_DATA instead of ROOT_DATA_PATH
     * @return  string  Related URL to data directory
     */
    function getDataURL($suffix = '', $rel_url = true, $base_data = false)
    {
        if (!defined('ROOT_DATA_PATH_URL') || $base_data) {
            $url = substr($base_data? JAWS_BASE_DATA : ROOT_DATA_PATH, strlen(ROOT_JAWS_PATH));
            $url = str_replace('\\', '/', $url);
            if (!$rel_url) {
                $url = Jaws_Utils::getBaseURL('/' . $url);
            }
        } else {
            $url = ROOT_DATA_PATH_URL;
        }

        //$suffix = is_bool($suffix)? array() : explode('/', $suffix);
        //$suffix = implode('/', array_map('rawurlencode', $suffix));
        return $url . $suffix;
    }

    /**
     * Returns the URL of the themes directory
     *
     * @access  public
     * @param   string  $suffix         suffix part of url
     * @param   bool    $rel_url        relative url
     * @param   bool    $base_themes    use JAWS_BASE_DATA instead of ROOT_DATA_PATH
     * @return  string  Related URL to themes directory
     */
    function getThemeURL($suffix = '', $rel_url = true, $base_themes = false)
    {
        if (!defined('JAWS_THEMES_URL') || $base_themes) {
            $url = substr($base_themes? JAWS_BASE_THEMES : JAWS_THEMES, strlen(ROOT_JAWS_PATH));
            $url = str_replace('\\', '/', $url);
            if (!$rel_url) {
                $url = $this->getSiteURL('/' . $url);
            }
        } else {
            $url = JAWS_THEMES_URL;
        }

        //$suffix = is_bool($suffix)? array() : explode('/', $suffix);
        //$suffix = implode('/', array_map('rawurlencode', $suffix));
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
        $data    = $this->registry->fetch('gadgets_autoload_items');
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
    static function classExists($classname, $autoload = false)
    {
        return class_exists($classname, $autoload);
    }

    /**
     * Sets a export data
     *
     * @access  public
     * @param   string  $component  Component name
     * @param   int     $type       Export data type(1: registry key, 2: ACL key, 3: session key, 5: un-type)
     * @param   string  $key        Key name
     * @param   mixed   $value      Key value
     * @return  void
     */
    function export($component, $type = JAWS_EXPORT_UNTYPE, $key = null, $value = '')
    {
        if (!array_key_exists($component, $this->exports)) {
            $this->exports[$component] = array();
        }

        if (!array_key_exists($type, $this->exports[$component])) {
            $this->exports[$component][$type] = array();
        }

        if (is_array($key)) {
            $this->exports[$component][$type] = array_merge($this->exports[$component][$type], $key);
        } elseif(isset($key)) {
            $this->exports[$component][$type][$key] = $value;
        }
    }

    /**
     * Get all exports data of the given component/gadget
     *
     * @access  public
     * @param   string  $component  (Optional) Component name
     * @return  array   export data
     */
    function exports($component = null)
    {
        if (is_null($component)) {
            return $this->exports;
        } else {
            return $this->exports[$component];
        }
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
            $_GZipEnabled = true;
            if (($this->registry->fetch('gzip_compression', 'Settings') != 'true') ||
                !extension_loaded('zlib') ||
                ini_get('zlib.output_compression') ||
                (ini_get('zlib.output_compression_level') > 0) ||
                (ini_get('output_handler') == 'ob_gzhandler') ||
                (ini_get('output_handler') == 'mb_output_handler') ||
                (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false))
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
            $robots = explode(',', $this->registry->fetch('robots', 'Settings'));
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
        $time = is_numeric($time)? (int)$time : strtotime($time);

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
        $timezone = $default_timezone? $this->_Preferences['timezone'] : $this->_Preferences['site_timezone'];
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
     * Convenience function to translate strings
     *
     * @param   string  $params Method parameters
     *
     * @return string
     */
    public static function t($input, ...$params)
    {
        @list($input, $lang) = explode('|', $input);
        if ($type = strstr($input, '.', true)) {
            $string = substr($input, strlen($type) + 1);
            if ($component = strstr($string, '.', true)) {
                $string = substr($string, strlen($component) + 1);
            } else {
                // format not supported
                return $input;
            }

            switch ($type) {
                case 'global':
                case 'GLOBAL':
                    $type = Jaws_Translate::TRANSLATE_GLOBAL;
                    break;

                case 'gadgets':
                case 'GADGETS':
                    $type = Jaws_Translate::TRANSLATE_GADGET;
                    break;

                case 'plugins':
                case 'PLUGINS':
                    $type = Jaws_Translate::TRANSLATE_PLUGIN;
                    break;

                case 'install':
                case 'INSTALL':
                    $type = Jaws_Translate::TRANSLATE_INSTALL;
                    break;

                case 'upgrade':
                case 'UPGRADE':
                    $type = Jaws_Translate::TRANSLATE_UPGRADE;
                    break;

                default:
                    // format not supported
                    return $input;
            }
        } else {
            $string = $input;
            $component = '';
            $type = Jaws_Translate::TRANSLATE_GLOBAL;
        }

        return Jaws_Translate::getInstance()->XTranslate(
            $lang,
            $type,
            $component,
            $string,
            $params
        );
    }

    /**
     * Overloading __get magic method
     *
     * @access  private
     * @param   string  $property   Property name
     * @return  mixed   Requested property otherwise null
     */
    function __get($property)
    {
        switch ($property) {
            case 'request':
                return $this->loadObject('Jaws_Request', 'request', true);
                break;

            case 'registry':
                return $this->loadObject('Jaws_Registry', 'registry');
                break;

            case 'acl':
                return $this->loadObject('Jaws_ACL', 'acl');
                break;

            case 'listener':
                return $this->loadObject('Jaws_Listener', 'listener');
                break;

            case 'map':
                return $this->loadObject('Jaws_URLMapping', 'map');
                break;

            case 'layout':
                return $this->loadObject('Jaws_Layout', 'layout');
                break;

            case 'session':
                $this->session = Jaws_Session::factory();
                return $this->session;
                break;

            case 'cache':
                $this->cache = Jaws_Cache::factory();
                return $this->cache;
                break;

            case 'template':
                return new Jaws_XTemplate();
                break;

            default:
                return Jaws_Error::raiseError("Property '$property' not exists!", __FUNCTION__);
        }

    }

}