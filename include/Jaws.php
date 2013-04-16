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
     * Is index page
     *
     * @var     bool
     * @access  private
     */
    var $_IsIndex = false;

    /**
     * The main request's gadget
     * @var     string
     * @access  protected
     */
    var $_RequestGadget = '';

    /**
     * The main request's action
     * @var     string
     * @access  protected
     */
    var $_RequestAction = '';

    /**
     * Default preferences
     * @var     array
     * @access  private
     */
    var $_Preferences = array(
        'theme'             => 'jaws',
        'language'          => 'en',
        'editor'            => null,
        'timezone'          => null,
        'calendar_type'     => 'Gregorian',
        'calendar_language' => 'en',
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
     * The calendar type.
     * @var     string
     * @access  protected
     */
    var $_CalendarType = 'Gregorian';

    /**
     * The calendar language the application is running in.
     * @var     string
     * @access  protected
     */
    var $_CalendarLanguage = 'en';

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
     */
    function Jaws()
    {
        spl_autoload_register(array($this, 'loadClass'));
        $this->loadObject('Jaws_UTF8', 'UTF8');
    }

    /**
     * Does everything needed to get the application to a usable state.
     *
     * @return  void
     * @access  public
     */
    function create()
    {
        $this->loadObject('Jaws_Translate', 'Translate');
        $this->loadObject('Jaws_Registry', 'Registry');
        $this->loadObject('Jaws_ACL', 'ACL');
        $this->loadObject('Jaws_Listener', 'Listener');

        $this->loadPreferences();
        $this->Registry->Init();
        $this->InstanceSession();
  
        $this->loadDefaults();
        $this->Translate->Init($this->_Language);

        $this->loadObject('Jaws_URLMapping', 'Map');
        $this->Map->Load();
    }

    /**
     * Load the default application preferences(language, theme, ...)
     *
     * @return  void
     * @access  public
     */
    function loadPreferences()
    {
        $this->_Preferences = array(
            'theme'             => $this->Registry->Get('theme', 'Settings'),
            'language'          => $this->Registry->Get(JAWS_SCRIPT == 'index'? 'site_language': 'admin_language',
                                                        'Settings'),
            'editor'            => $this->Registry->Get('editor', 'Settings'),
            'timezone'          => $this->Registry->Get('timezone', 'Settings'),
            'calendar_type'     => $this->Registry->Get('calendar_type', 'Settings'),
            'calendar_language' => $this->Registry->Get('calendar_language', 'Settings'),
        );
    }

    /**
     * Set the language and theme, first based on session data, then on application defaults.
     *
     * @return  void
     * @access  public
     */
    function loadDefaults()
    {
        if (APP_TYPE == 'web') {
            $cookies = array();
            $cookie_precedence = ($this->Registry->Get('cookie_precedence', 'Settings') == 'true');
            if ($cookie_precedence) {
                // load cookies preferences
                $cookies = $GLOBALS['app']->Session->GetCookie('preferences');
                if (!is_array($cookies)) {
                    $cookies = array();
                }
            }

            // load from session
            $this->_Theme            = $this->Session->GetAttribute('theme');
            $this->_Language         = $this->Session->GetAttribute('language');
            $this->_Editor           = $this->Session->GetAttribute('editor');
            $this->_Timezone         = $this->Session->GetAttribute('timezone');
            $this->_CalendarType     = $this->Session->GetAttribute('calendartype');
            $this->_CalendarLanguage = $this->Session->GetAttribute('calendarlanguage');

            // theme
            if (empty($this->_Theme)) {
                if (array_key_exists('theme', $cookies)) {
                    $this->_Theme = $cookies['theme'];
                } else {
                    $this->_Theme = $this->_Preferences['theme'];
                }
            }

            // language
            if (JAWS_SCRIPT == 'admin') {
                $this->_Language = empty($this->_Language)? $this->_Preferences['language'] : $this->_Language;
            } else {
                if (array_key_exists('language', $cookies)) {
                    $this->_Language = $cookies['language'];
                } else {
                    $this->_Language = $this->_Preferences['language'];
                }
            }

            // editor
            if (JAWS_SCRIPT == 'admin') {
                if (empty($this->_Editor)) {
                    if (array_key_exists('editor', $cookies)) {
                        $this->_Editor = $cookies['editor'];
                    } else {
                        $this->_Editor = $this->_Preferences['editor'];
                    }
                }
            } else {
                $this->_Editor = 'TextArea';
            }

            // timezone
            if (is_null($this->_Timezone)) {
                if (array_key_exists('timezone', $cookies)) {
                    $this->_Timezone = $cookies['timezone'];
                } else {
                    $this->_Timezone = $this->_Preferences['timezone'];
                }
            }

            // calendar type
            if (empty($this->_CalendarType)) {
                if (array_key_exists('calendar_type', $cookies)) {
                    $this->_CalendarType = $cookies['calendar_type'];
                } else {
                    $this->_CalendarType = $this->_Preferences['calendar_type'];
                }
            }

            // calendar language
            if (empty($this->_CalendarLanguage)) {
                if (array_key_exists('calendar_language', $cookies)) {
                    $this->_CalendarLanguage = $cookies['calendar_language'];
                } else {
                    $this->_CalendarLanguage = $this->_Preferences['calendar_language'];
                }
            }
        } else {
            $this->_Theme            = $this->_Preferences['theme'];
            $this->_Language         = $this->_Preferences['language'];
            $this->_Editor           = $this->_Preferences['editor'];
            $this->_Timezone         = $this->_Preferences['timezone'];
            $this->_CalendarType     = $this->_Preferences['calendar_type'];
            $this->_CalendarLanguage = $this->_Preferences['calendar_language'];
        }

        // filter non validate character
        $this->_Theme            = preg_replace('/[^[:alnum:]_-]/', '', $this->_Theme);
        $this->_Language         = preg_replace('/[^[:alnum:]_-]/', '', $this->_Language);
        $this->_Editor           = preg_replace('/[^[:alnum:]_-]/', '', $this->_Editor);
        $this->_Timezone         = $this->_Preferences['timezone'];
        $this->_CalendarType     = preg_replace('/[^[:alnum:]_]/',  '', $this->_CalendarType);
        $this->_CalendarLanguage = preg_replace('/[^[:alnum:]_]/',  '', $this->_CalendarLanguage);

        require_once PEAR_PATH. 'Net/Detect.php';
        $bFlags = explode(',', $this->Registry->Get('browsers_flag', 'Settings'));
        $this->_BrowserFlag = Net_UserAgent_Detect::getBrowser($bFlags);
    }

    /**
     * Setup the applications session.
     *
     * @return  void
     * @access  public
     */
    function InstanceSession()
    {
        require_once JAWS_PATH . 'include/Jaws/Session.php';
        $this->Session =& Jaws_Session::factory();
        $this->Session->Init();
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
        return $this->_BrowserFlag;
    }

    /**
     * Overwrites the default values the Application use
     *
     * It overwrites the default values with the input values
     * (which should come in an array)
     *
     *  - Theme:            Array key should be named theme
     *  - Language:         Array key should be named language
     *  - CalendarType:     Array key should be named calendartype
     *  - CalendarLanguage: Array key should be named calendarlanguage
     *  - Editor:           Array key should be named editor
     *
     * In the case of Language and CalendarLanguage, if the new values are
     * different from the default ones (or the values that were already loaded)
     * we load the translation stuff again
     *
     * @access  public
     * @param   array   $defaults  New default values
     */
    function OverwriteDefaults($defaults) 
    {
        if (!is_array($defaults)) {
            return;
        }

        $loadLanguageAgain = false;
        foreach($defaults as $key => $value) {
            $key = strtolower($key);
            if (empty($value)) {
                continue;
            }

            switch($key) {
                case 'theme':
                    $this->_Theme = $value;
                    break;

                case 'language':
                    if ($this->_Language != $value) {
                        $loadLanguageAgain = true;
                        $this->_Language = $value;
                    }
                    break;

                case 'calendartype':
                    $this->_CalendarType = $value;
                    break;

                case 'calendarlanguage':
                    if ($this->_CalendarLanguage != $value) {
                        $loadLanguageAgain = true;
                        $this->_CalendarLanguage = $value;
                    }
                    break;

                case 'editor':
                    $this->_Editor = $value;
                    break;

                case 'timezone':
                    $this->_Timezone = $value;
                    break;
            }
        }

        if ($loadLanguageAgain) {
            $this->Translate->Init($this->_Language);
        }
    }
    
    /**
     * Get the default language
     *
     * @access  public
     * @return  string The default language
     */
    function GetCalendarType()
    {
        return $this->_CalendarType;
    }

    /**
     * Get the default language
     *
     * @access  public
     * @return  string The default language
     */
    function GetCalendarLanguage()
    {
        return $this->_CalendarLanguage;
    }

    /**
     * Get the available authentication methods
     *
     * @access  public
     * @return  array  Array with available authentication methods
     */
    function GetAuthMethods()
    {
        $path = JAWS_PATH . 'include/Jaws/Auth';
        if (is_dir($path)) {
            $methods = array();
            $dir = scandir($path);
            foreach ($dir as $method) {
                if (stristr($method, '.php')) {
                    $method = str_replace('.php', '', $method);
                    $methods[$method] = $method;
                }
            }

            return $methods;
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
    function LoadGadget($gadget, $type = 'HTML', $filename = '')
    {
        // filter non validate character
        $type = preg_replace('/[^[:alnum:]_]/', '', $type);
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        $type_class_name = $gadget. '_'. $type;
        if (!isset($this->_Gadgets[$gadget][$type])) {
            if (!is_dir(JAWS_PATH . 'gadgets/' . $gadget)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                                        'Gadget directory check');
                return $error;
            }

            // is gadget available?
            if (defined('JAWS_AVAILABLE_GADGETS')) {
                static $available_gadgets;
                if (!isset($available_gadgets)) {
                    $available_gadgets = array_filter(array_map('trim', explode(',', JAWS_AVAILABLE_GADGETS)));
                }

                if (!in_array($gadget, $available_gadgets)) {
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_GADGET_NOT_AVAILABLE', $gadget),
                                            'Gadget availability check',
                                            JAWS_ERROR_INFO);
                    return $error;
                }
            }

            $file = JAWS_PATH . 'gadgets/' . $gadget . '/' . $type . '.php';
            if (file_exists($file)) {
                include_once $file;
            }

            if (!Jaws::classExists($type_class_name)) {
                // return a error
                $error = new Jaws_Error(_t('GLOBAL_ERROR_CLASS_DOES_NOT_EXIST', $type_class_name),
                                        'Gadget class check');
                return $error;
            }

            // temporary
            if ($type == 'Info') {
                $obj = new $type_class_name($gadget);
            } else {
                if (!isset($this->_Gadgets[$gadget]['Info']['base'])) {
                    $info_class_name = $gadget . '_Info';
                    $ifile = JAWS_PATH . 'gadgets/' . $gadget . '/Info.php';
                    @include_once $ifile;
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
                    case 'HTML':
                        $file_class_name = $gadget. '_Actions_'. $filename;
                        $file = JAWS_PATH. "gadgets/$gadget/Actions/$filename.php";
                        break;

                    case 'AdminHTML':
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

            $file = JAWS_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php';
            if (file_exists($file)) {
                include_once $file;
            }

            if (!Jaws::classExists($plugin)) {
                // return a error
                $error = new Jaws_Error(_t('GLOBAL_ERROR_CLASS_DOES_NOT_EXIST', $plugin),
                                        'Plugin class check');
                return $error;
            }

            // load plugin's language file
            $this->Translate->LoadTranslation($plugin, JAWS_COMPONENT_PLUGIN);

            $obj = new $plugin();
            if (Jaws_Error::IsError($obj)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $plugin),
                                        'Plugin file loading');
                return $error;
            }

            $this->_Plugins[$plugin] = $obj;
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
        $this->_IsIndex       = $index;
        $this->_RequestGadget = $gadget;
        $this->_RequestAction = $action;
    }

    /**
     * Get main request properties like gadget and action
     *
     * @access  public
     * @return  array   Main request data array
     */
    function GetMainRequest()
    {
        return array('index'  => $this->_IsIndex,
                     'gadget' => $this->_RequestGadget,
                     'action' => $this->_RequestAction);
    }

    /**
     * Gets the actions of a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget's name
     * @param   string  $type   Action's type(LayoutAction, NormalAction, AdminAction, ... or empty for all type)
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
            if (@include_once($file)) {
                array_walk($actions, $func_merge, $gadget);
                $this->_Gadgets[$gadget]['actions']['index'] = $actions;
            } else {
                $this->_Gadgets[$gadget]['actions']['index'] = array();
            }

            $file = JAWS_PATH . 'gadgets/' . $gadget . '/AdminActions.php';
            if (@include_once($file)) {
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
            $calendar = $this->GetCalendarType();
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
    function loadObject($classname, $property = '')
    {
        // filter non validate character
        $classname = preg_replace('/[^[:alnum:]_]/', '', $classname);

        if (!empty($property)) {
            if (isset($this->{$property})) {
                $objClass = $this->{$property};
            } else {
                $objClass = new $classname();
                $this->{$property} = $objClass;
            }
        } else {
            $objClass = new $classname();
        }

        return $objClass;
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
        $file = JAWS_PATH. 'include'. DIRECTORY_SEPARATOR. str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';
        require_once $file;
        $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded class: ' . $classname . ', File: ' . $file);
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
     * Returns the current location (without BASE_SCRIPT)
     *
     * @access  public
     * @return  string   Current location
     */
    function GetURILocation()
    {
        static $location;

        if (isset($location)) {
            return $location;
        }

        //TODO: Need to check which SERVER var is allways sent to the server
        if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
            $location = Jaws_XSS::filter($_SERVER['SCRIPT_NAME']);
        } else {
            $location = Jaws_XSS::filter($_SERVER['REQUEST_URI']);
        }
        $location = substr($location, 0, stripos($location, BASE_SCRIPT));
        return $location;
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
            $cfg_url = isset($GLOBALS['app']->Registry)? $GLOBALS['app']->Registry->Get('site_url', 'Settings') : '';
            if (!empty($cfg_url)) {
                $cfg_url = parse_url($cfg_url);
                if (isset($cfg_url['scheme']) && isset($cfg_url['host'])) {
                    $cfg_url['path'] = isset($cfg_url['path'])? $cfg_url['path'] : '';
                    $site_url = $cfg_url;
                }
            }

            if (!isset($site_url)) {
                $site_url = array();
                $site_url['scheme'] = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')? 'https' : 'http';
                //$site_url['host'] = $_SERVER['SERVER_NAME'];
                $site_url['host'] = reset(explode(':', $_SERVER['HTTP_HOST']));
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
            }

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
        $data    = $GLOBALS['app']->Registry->Get('gadgets_autoload_items');
        $gadgets = array_filter(explode(',', $data));
        foreach($gadgets as $gadgetName) {
            $gadget = $this->loadGadget($gadgetName, 'Autoload');
            if (!Jaws_Error::isError($gadget)) {
                if (method_exists($gadget, 'Execute')) {
                    $gadget->Execute();
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
    function classExists($classname)
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
            if (($this->Registry->Get('gzip_compression', 'Settings') != 'true') ||
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
            $robots = explode(',', $this->Registry->Get('robots', 'Settings'));
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

}