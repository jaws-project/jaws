<?php
/**
 * Main application, the core
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws
{
    /**
     * The processed request is in index page
     *
     * @var     bool
     * @access  protected
     */
    var $requestedInIndex = false;

    /**
     * The requested gadget
     * @var     string
     * @access  protected
     */
    var $requestedGadget = '';

    /**
     * The requested action
     * @var     string
     * @access  protected
     */
    var $requestedAction = '';

    /**
     * Default preferences
     * @var     array
     * @access  private
     */
    var $_Preferences = array(
        'theme'    => 'jaws',
        'language' => 'en',
        'editor'   => null,
        'timezone' => null,
        'calendar' => 'Gregorian',
    );

    /**
     * The application's theme.
     * @var     string
     * @access  protected
     */
    var $_Theme = 'jaws';

    /**
     * The language the application is running in.
     * @var     string
     * @access  protected
     */
    var $_Language = 'en';

    /**
     * The calendar
     * @var     string
     * @access  protected
     */
    var $_Calendar = 'Gregorian';

    /**
     * The editor application is using
     * @var     string
     * @access  protected
     */
    var $_Editor = null;

    /**
     * The timezone
     * @var     string
     * @access  protected
     */
    var $_Timezone = null;

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
     * Store gadget object for later use so we aren't running
     * around with multiple copies
     * @var array
     * @access  protected
     */
    var $_Gadgets = array();

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
        spl_autoload_register(array($this, 'loadClass'));
        $this->loadObject('Jaws_Request', 'request', true);
        $this->loadObject('Jaws_UTF8', 'UTF8');
        $this->loadObject('Jaws_Translate', 'Translate');
        $this->loadObject('Jaws_Registry', 'Registry');
        $this->loadObject('Jaws_ACL', 'ACL');
        $this->loadObject('Jaws_Listener', 'Listener');
        $this->loadObject('Jaws_URLMapping', 'Map');
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
        $this->loadPreferences();
    }

    /**
     * Load the default application preferences(language, theme, ...)
     *
     * @access  public
     * @param   array   $preferences    default preferences
     * @return  void
     */
    function loadPreferences($preferences = array(), $loadFromDatabase = true)
    {
        if ($loadFromDatabase) {
            $user   = $this->Session->GetAttribute('user');
            $layout = $this->Session->GetAttribute('layout');
            $this->_Preferences = array(
                'theme'    => $this->Registry->fetchByUser($layout, 'theme',    'Settings'),
                'editor'   => $this->Registry->fetchByUser($user,   'editor',   'Settings'),
                'timezone' => $this->Registry->fetchByUser($user,   'timezone', 'Settings'),
                'calendar' => $this->Registry->fetchByUser($layout, 'calendar', 'Settings'),
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
        $this->_Theme    = preg_replace('/[^[:alnum:]_-]/', '', $this->_Preferences['theme']);
        $this->_Language = preg_replace('/[^[:alnum:]_-]/', '', $this->_Preferences['language']);
        $this->_Editor   = preg_replace('/[^[:alnum:]_-]/', '', $this->_Preferences['editor']);
        $this->_Timezone = $this->_Preferences['timezone'];
        $this->_Calendar = preg_replace('/[^[:alnum:]_]/',  '', $this->_Preferences['calendar']);

        // load the language translates
        $this->Translate->Init($this->_Language);
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
        if (!isset($theme)) {
            // Check if valid theme name
            if (strpos($this->_Theme, '..') !== false ||
                strpos($this->_Theme, '%') !== false ||
                strpos($this->_Theme, '\\') !== false ||
                strpos($this->_Theme, '/') !== false) {
                    return new Jaws_Error(_t('GLOBAL_ERROR_INVALID_NAME', 'GetTheme'),
                                          'Getting theme name');
            }

            $theme = array();
            $theme['name'] = $this->_Theme;
            $theme['path'] = JAWS_THEMES. $this->_Theme . '/';
            if (!is_dir($theme['path'])) {
                $theme['url']    = $this->getThemeURL($this->_Theme . '/', $rel_url, true);
                $theme['path']   = JAWS_BASE_THEMES. $this->_Theme . '/';
                $theme['exists'] = is_dir($theme['path']);
            } else {
                $theme['url']    = $this->getThemeURL($this->_Theme . '/', $rel_url);
                $theme['exists'] = true;
            }
        }

        return $theme;
    }

    /**
     * Get the default language
     *
     * @access  public
     * @return  string The default language
     */
    function GetLanguage()
    {
        // Check if valid language name
        if (strpos($this->_Language, '..') !== false ||
            strpos($this->_Language, '%') !== false ||
            strpos($this->_Language, '\\') !== false ||
            strpos($this->_Language, '/') !== false) {
                return new Jaws_Error(_t('GLOBAL_ERROR_INVALID_NAME', 'GetLanguage'),
                                      'Getting language name');
        }
        return $this->_Language;
    }

    /**
     * Get the default editor
     *
     * @access  public
     * @return  string The default language
     */
    function GetEditor()
    {
        return $this->_Editor;
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
        return $this->_Calendar;
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
     * Loads the gadget file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string $gadget      Name of the gadget
     * @param   string $type        The type being loaded
     * @param   string $filename    Try to find gadget class in this file
     * @return  mixed  Gadget class object on successful, Jaws_Error otherwise
     */
    function LoadGadget($gadget, $type = 'Action', $filename = '')
    {
        // filter non validate character
        $type = preg_replace('/[^[:alnum:]_]/', '', $type);
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        $type_class_name = $gadget. '_'. $type;
        if (@!array_key_exists($type, $this->_Gadgets[$gadget])) {
            if (!is_dir(JAWS_PATH . 'gadgets/' . $gadget)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                    __FUNCTION__
                );
            }

            // is gadget available?
            if (defined('JAWS_AVAILABLE_GADGETS')) {
                static $available_gadgets;
                if (!isset($available_gadgets)) {
                    $available_gadgets = array_filter(array_map('trim', explode(',', JAWS_AVAILABLE_GADGETS)));
                }

                if (!in_array($gadget, $available_gadgets)) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_GADGET_NOT_AVAILABLE', $gadget),
                        __FUNCTION__,
                        JAWS_ERROR_INFO
                    );
                }
            }

            $file = JAWS_PATH . 'gadgets/' . $gadget . '/' . $type . '.php';
            if (file_exists($file)) {
                include_once $file;
            }

            $obj = null;
            if (!Jaws::classExists($type_class_name)) {
                switch ($type) {
                    case 'Info':
                        return Jaws_Error::raiseError(
                            _t('GLOBAL_ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                            __FUNCTION__
                        );
                        break;

                    case 'AdminAction':
                        $type_class_name = 'Jaws_Gadget_Action';
                        break;

                    case 'AdminModel':
                        $type_class_name = 'Jaws_Gadget_Model';
                        break;

                    default:
                        $type_class_name = "Jaws_Gadget_$type";
                }
            }

            // temporary
            if ($type == 'Info') {
                $obj = new $type_class_name($gadget);
            } else {
                if (!isset($this->_Gadgets[$gadget]['Info']['base'])) {
                    $info_class_name = $gadget . '_Info';
                    $ifile = JAWS_PATH . 'gadgets/' . $gadget . '/Info.php';
                    include_once $ifile;
                    $this->_Gadgets[$gadget]['Info']['base'] = new $info_class_name($gadget);
                }
                $obj = new $type_class_name($this->_Gadgets[$gadget]['Info']['base']);
            }
            if (Jaws_Error::IsError($obj)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $type_class_name),
                                        'Gadget file loading');
                return $error;
            }

            $this->_Gadgets[$gadget][$type]['base']  = $obj;
            $this->_Gadgets[$gadget][$type]['files'] = array();
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded gadget: ' . $gadget . ', File: ' . $type);
        }

        $filename = trim($filename);
        if (!empty($filename)) {
            if (!isset($this->_Gadgets[$gadget][$type]['files'][$filename])) {
                switch ($type) {
                    case 'Action':
                        $file_class_name = $gadget. '_Actions_'. $filename;
                        $file = JAWS_PATH. "gadgets/$gadget/Actions/$filename.php";
                        break;

                    case 'AdminAction':
                        $file_class_name = $gadget. '_Actions_Admin_'. $filename;
                        $file = JAWS_PATH. "gadgets/$gadget/Actions/Admin/$filename.php";
                        break;

                    case 'Model':
                        $file_class_name = $gadget. '_Model_'. $filename;
                        $file = JAWS_PATH. "gadgets/$gadget/Model/$filename.php";
                        break;

                    case 'AdminModel':
                        $file_class_name = $gadget. '_Model_Admin_'. $filename;
                        $file = JAWS_PATH. "gadgets/$gadget/Model/Admin/$filename.php";
                        break;
                }

                if (file_exists($file)) {
                    include_once $file;
                }

                if (!Jaws::classExists($file_class_name)) {
                    // return a error
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_CLASS_DOES_NOT_EXIST', $file_class_name),
                                            'Gadget class check');
                    return $error;
                }

                // temporary
                $objFile = new $file_class_name($this->_Gadgets[$gadget]['Info']['base']);
                if (Jaws_Error::IsError($objFile)) {
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $file_class_name),
                                            'Gadget file loading');
                    return $error;
                }

                $this->_Gadgets[$gadget][$type]['files'][$filename] = $objFile;
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget file: $gadget, Type: $type, File: $filename");
            }

            return $this->_Gadgets[$gadget][$type]['files'][$filename];
        }

        return $this->_Gadgets[$gadget][$type]['base'];
    }

    /**
     * Loads the plugin file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string $plugin Name of the plugin
     * @return  mixed Plugin class object on successful, Jaws_Error otherwise
     */
    function LoadPlugin($plugin)
    {
        // filter non validate character
        $plugin = preg_replace('/[^[:alnum:]_]/', '', $plugin);

        if (!isset($this->_Plugins[$plugin])) {
            if (!is_dir(JAWS_PATH . 'plugins/' . $plugin)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                                        'Plugin directory check');
                return $error;
            }

            // is plugin available?
            if (defined('JAWS_AVAILABLE_PLUGINS')) {
                static $available_plugins;
                if (!isset($available_plugins)) {
                    $available_plugins = array_filter(array_map('trim', explode(',', JAWS_AVAILABLE_PLUGINS)));
                }

                if (!in_array($plugin, $available_plugins)) {
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_PLUGIN_NOT_AVAILABLE', $plugin),
                                            'Plugin availability check');
                    return $error;
                }
            }

            $file = JAWS_PATH. 'plugins/'. $plugin. '/Plugin.php';
            if (file_exists($file)) {
                include_once $file;
            }

            $plugin_class = $plugin. '_Plugin';
            if (!Jaws::classExists($plugin_class)) {
                // return a error
                $error = new Jaws_Error(_t('GLOBAL_ERROR_CLASS_DOES_NOT_EXIST', $plugin_class),
                                        'Plugin class check');
                return $error;
            }

            // load plugin's language file
            $this->Translate->LoadTranslation($plugin, JAWS_COMPONENT_PLUGIN);

            $objPlugin = new $plugin_class($plugin);
            if (Jaws_Error::IsError($objPlugin)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $plugin_class),
                                        'Plugin file loading');
                return $error;
            }

            $this->_Plugins[$plugin] = $objPlugin;
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded plugin: ' . $plugin);
        }

        return $this->_Plugins[$plugin];
    }

    /**
     * Set main request properties like gadget and action
     *
     * @access  public
     * @param   bool    $index  Index page?
     * @param   string  $gadget Gadget's name
     * @param   string  $action Gadget's action
     * @return  void
     */
    function SetMainRequest($index, $gadget, $action)
    {
        $this->requestedInIndex = $index;
        $this->requestedGadget  = $gadget;
        $this->requestedAction  = $action;
    }

    /**
     * Gets the actions of a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget's name
     * @param   string  $type   Action's type(NormalAction, AdminAction, ... or empty for all type)
     * @param   string  $script Action belongs to index or admin
     * @return  array   Gadget actions
     */
    function GetGadgetActions($gadget, $type = '', $script = JAWS_SCRIPT)
    {
        // filter non validate character
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);

        if (!isset($this->_Gadgets[$gadget]['actions'])) {
            // Load gadget's language file
            $this->Translate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET);
            global $base_properties;
            $base_properties= array(
                'normal'     => false,
                'layout'     => false,
                'parametric' => false,
                'standalone' => false,
                'file'       => false,
            );
            $func_merge = create_function(
                '&$properties, $action, $gadget',
                'global $base_properties;
                $base_properties["name"] = _t(strtoupper($gadget."_ACTIONS_".$action));
                $base_properties["desc"] = _t(strtoupper($gadget."_ACTIONS_".$action."_DESC"));
                $properties = array_merge($base_properties, $properties);'
            );
            $file = JAWS_PATH . 'gadgets/' . $gadget . '/Actions.php';
            if (file_exists($file)) {
                include_once($file);
                array_walk($actions, $func_merge, $gadget);
                $this->_Gadgets[$gadget]['actions']['index'] = $actions;
            } else {
                $this->_Gadgets[$gadget]['actions']['index'] = array();
            }

            $file = JAWS_PATH . 'gadgets/' . $gadget . '/AdminActions.php';
            if (file_exists($file)) {
                include_once($file);
                array_walk($actions, $func_merge, $gadget);
                $this->_Gadgets[$gadget]['actions']['admin'] = $actions;
            } else {
                $this->_Gadgets[$gadget]['actions']['admin'] = array();
            }
        }

        if (empty($type)) {
            return $this->_Gadgets[$gadget]['actions'];
        } else {
            return array_filter(
                $this->_Gadgets[$gadget]['actions'][$script],
                create_function('$item', 'return $item[\''.$type.'\'];')
            );
        }
    }

    /**
     * Prepares the jaws Editor
     *
     * @access  public
     * @param   string  $gadget  Gadget that uses the editor (usable for plugins)
     * @param   string  $name    Name of the editor
     * @param   string  $value   Value of the editor/content (optional)
     * @param   bool    $filter  Convert special characters to HTML entities
     * @param   string  $label   Label that the editor will have (optional)
     * @return  object  The editor in /gadgets/Settings/editor
     */
    function &LoadEditor($gadget, $name, $value = '', $filter = true, $label = '')
    {
        if ($filter && !empty($value)) {
            $value = Jaws_XSS::filter($value);
        }

        $editor = $this->_Editor;
        $file   = JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
        if (!file_exists($file)) {
            $editor = 'TextArea';
            $file   = JAWS_PATH . 'include/Jaws/Widgets/' . $editor . '.php';
        }
        $editorClass = "Jaws_Widgets_$editor";

        require_once $file;
        $editor = new $editorClass($gadget, $name, $value, $label);

        return $editor;
    }

    /**
     * Loads the Jaws Date class.
     * Singleton approach.
     *
     * @access  public
     * @return  object  Date calender object
     */
    function loadDate()
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        $signature = serialize(array('date'));
        if (!isset($instances[$signature])) {
            include_once JAWS_PATH . 'include/Jaws/Date.php';
            $calendar = $this->GetCalendar();
            $instances[$signature] =& Jaws_Date::factory($calendar);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Date class is loaded');
        }

        return $instances[$signature];
    }

    /**
     * Loads a class object
     *
     * @access  public
     * @param   string  $classname  Class name
     * @param   string  $property   Jaws app property name
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
     * Loads a class from within the Jaws dir
     *
     * @access  public
     * @param   string  $classname  Class name
     * @return  mixed   Object if success otherwise Jaws_Error on failure
     */
    function loadClass($classname, $property = '')
    {
        // filter non validate character
        $classname = preg_replace('/[^[:alnum:]_]/', '', $classname);
        $classname = str_replace('_', '/', $classname);
        if (0 === strpos($classname, 'Jaws/')) {
            $file = JAWS_PATH. "include/$classname.php";
        } else {
            $file = JAWS_PATH. "gadgets/$classname.php";
        }
        require_once $file;
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded class file: ' . $file);
    }

    /**
     * Verify if an image exists, if not returns a default image (unknown.png)
     *
     * @param   string Image path
     * @return  string The original path if it exists or an unknow.png path
     * @access  public
     */
    function CheckImage($path)
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
    function getSiteURL($suffix = '', $rel_url = false)
    {
        static $site_url;
        if (!isset($site_url)) {
            $site_url = array();
            $site_url['scheme'] = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')? 'https' : 'http';
            //$site_url['host'] = $_SERVER['SERVER_NAME'];
            $site_url['host'] = current(explode(':', $_SERVER['HTTP_HOST']));
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
                $objHook = $objGadget->loadHook('Autoload');
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
        $timezone = $default_timezone? $this->_Preferences['timezone'] : $this->_Timezone;
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
