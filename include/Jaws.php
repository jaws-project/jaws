<?php
/**
 * Main application, the core
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
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
     * Store hook object for later use so we aren't running
     * @var array
     * @access  protected
     */
    var $_Classes = array();

    /**
     * Constructor
     *
     * @access  public
     */
    function Jaws()
    {
        require_once JAWS_PATH . 'include/Jaws/Gadget.php';
        require_once JAWS_PATH . 'include/Jaws/Template.php';
        require_once JAWS_PATH . 'include/Jaws/Header.php';
    }

    /**
     * Does everything needed to get the application to a usable state.
     *
     * @return  void
     * @access  public
     */
    function create()
    {
        $this->loadClass('UTF8', 'Jaws_UTF8');
        $this->loadClass('Translate', 'Jaws_Translate');
        $this->loadClass('Registry', 'Jaws_Registry');

        $this->loadPreferences();
        $this->Registry->Init();
        $this->InstanceSession();
  
        $this->loadDefaults();
        $this->Translate->Init($this->_Language);

        $this->loadClass('Map', 'Jaws_URLMapping');
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
            'theme'             => $this->Registry->Get('/config/theme'),
            'language'          => $this->Registry->Get(JAWS_SCRIPT == 'index'?
                                                        '/config/site_language':
                                                        '/config/admin_language'),
            'editor'            => $this->Registry->Get('/config/editor'),
            'timezone'          => $this->Registry->Get('/config/timezone'),
            'calendar_type'     => $this->Registry->Get('/config/calendar_type'),
            'calendar_language' => $this->Registry->Get('/config/calendar_language'),
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
            $cookie_precedence = ($this->Registry->Get('/config/cookie_precedence') == 'true');

            // load from session
            $this->_Theme            = $this->Session->GetAttribute('theme');
            $this->_Language         = $this->Session->GetAttribute('language');
            $this->_Editor           = $this->Session->GetAttribute('editor');
            $this->_Timezone         = $this->Session->GetAttribute('timezone');
            $this->_CalendarType     = $this->Session->GetAttribute('calendartype');
            $this->_CalendarLanguage = $this->Session->GetAttribute('calendarlanguage');

            // load cookies preferences
            $cookies = array(
                'theme'             => $this->Session->GetCookie('theme'),
                'language'          => $this->Session->GetCookie('language'),
                'editor'            => $this->Session->GetCookie('editor'),
                'timezone'          => $this->Session->GetCookie('timezone'),
                'calendar_type'     => $this->Session->GetCookie('calendar_type'),
                'calendar_language' => $this->Session->GetCookie('calendar_language'),
            );

            // theme
            if (empty($this->_Theme)) {
                if ($cookie_precedence && !empty($cookies['theme'])) {
                    $this->_Theme = $cookies['theme'];
                } else {
                    $this->_Theme = $this->_Preferences['theme'];
                }
            }

            // language
            if (JAWS_SCRIPT == 'admin') {
                $this->_Language = empty($this->_Language)? $this->_Preferences['language'] : $this->_Language;
            } else {
                if ($cookie_precedence && !empty($cookies['language'])) {
                    $this->_Language = $cookies['language'];
                } else {
                    $this->_Language = $this->_Preferences['language'];
                }
            }

            // editor
            if (empty($this->_Editor)) {
                if ($cookie_precedence && !is_null($cookies['editor'])) {
                    $this->_Editor = $cookies['editor'];
                } else {
                    $this->_Editor = $this->_Preferences['editor'];
                }
            }

            // timezone
            if (is_null($this->_Timezone)) {
                if ($cookie_precedence && !is_null($cookies['timezone'])) {
                    $this->_Timezone = $cookies['timezone'];
                } else {
                    $this->_Timezone = $this->_Preferences['timezone'];
                }
            }

            // calendar type
            if (empty($this->_CalendarType)) {
                if ($cookie_precedence && !is_null($cookies['calendar_type'])) {
                    $this->_CalendarType = $cookies['calendar_type'];
                } else {
                    $this->_CalendarType = $this->_Preferences['calendar_type'];
                }
            }

            // calendar language
            if (empty($this->_CalendarLanguage)) {
                if ($cookie_precedence && !is_null($cookies['calendar_language'])) {
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

        require_once 'Net/Detect.php';
        $bFlags = explode(',', $this->Registry->Get('/config/browsers_flag'));
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
        $this->loadClass('Layout', 'Jaws_Layout');
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
     * @param   bool    rel_url relative url
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
            $theme['path'] = JAWS_DATA . 'themes/' . $this->_Theme . '/';
            if (!is_dir($theme['path'])) {
                $theme['url']    = $this->getDataURL('themes/' . $this->_Theme . '/', $rel_url, true);
                $theme['path']   = JAWS_BASE_DATA .  'themes/' . $this->_Theme . '/';
                $theme['exists'] = is_dir($theme['path']);
            } else {
                $theme['url']    = $this->getDataURL('themes/' . $this->_Theme . '/', $rel_url);
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
     * @param   string Name of the gadget
     * @param   string The type being loaded
     * @param   string Try to find gadget class in this file
     * @return  mixed  Gadget class object on successful, Jaws_Error otherwise
     */
    function LoadGadget($gadget, $type = 'HTML', $filename = '')
    {
        $type   = trim($type);
        $gadget = urlencode(trim(strip_tags($gadget)));
        $type_class_name = $gadget . ucfirst($type);
        $load_registry = true;
        if (!isset($this->_Gadgets[$gadget][$type])) {
            if (!is_dir(JAWS_PATH . 'gadgets/' . $gadget)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                                        'Gadget directory check');
                return $error;
            }

            // is gadget published?
            if (defined('JAWS_PUBLISHED_GADGETS')) {
                static $published_gadgets;
                if (!isset($published_gadgets)) {
                    $published_gadgets = array_filter(array_map('trim', explode(',', JAWS_PUBLISHED_GADGETS)));
                }

                if (!in_array($gadget, $published_gadgets)) {
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_GADGET_NOT_PUBLISHED', $gadget),
                                            'Gadget publish check',
                                            JAWS_ERROR_INFO);
                    return $error;
                }
            }

            // Load gadget's language file
            $this->Translate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET);

            switch ($type) {
                case 'Info':
                    $load_registry = false;
                    if (!Jaws::classExists('Jaws_GadgetInfo')) {
                        require_once JAWS_PATH . 'include/Jaws/GadgetInfo.php';
                    }
                    break;
                case 'HTML':
                case 'AdminHTML':
                    if (!Jaws::classExists('Jaws_GadgetHTML')) {
                        require_once JAWS_PATH . 'include/Jaws/GadgetHTML.php';
                    }
                    break;
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

            if ($load_registry &&
               (!isset($this->_Gadgets[$gadget]) || !isset($this->_Gadgets[$gadget]['Registry'])))
            {
                $this->_Gadgets[$gadget]['Registry'] = true;
                if (isset($this->ACL)) {
                    $this->ACL->LoadFile($gadget);
                }
                $this->Registry->LoadFile($gadget);
            }

            $obj = new $type_class_name($gadget);
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

                    case 'LayoutHTML':
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

                $objFile = new $file_class_name($gadget);
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
        $plugin = urlencode(trim(strip_tags($plugin)));
        if (!isset($this->_Plugins[$plugin])) {
            if (!is_dir(JAWS_PATH . 'plugins/' . $plugin)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_PLUGIN_DOES_NOT_EXIST', $plugin),
                                        'Plugin directory check');
                return $error;
            }

            // is plugin published?
            if (defined('JAWS_PUBLISHED_PLUGINS')) {
                static $published_plugins;
                if (!isset($published_plugins)) {
                    $published_plugins = array_filter(array_map('trim', explode(',', JAWS_PUBLISHED_PLUGINS)));
                }

                if (!in_array($plugin, $published_plugins)) {
                    $error = new Jaws_Error(_t('GLOBAL_ERROR_PLUGIN_NOT_PUBLISHED', $plugin),
                                            'Plugin publish check');
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

            $obj = new $plugin();
            if (Jaws_Error::IsError($obj)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $plugin),
                                        'Plugin file loading');
                return $error;
            }

            // load registry file
            $this->Registry->LoadFile($plugin, 'plugins');
            // load plugin's language file
            $this->Translate->LoadTranslation($plugin, JAWS_COMPONENT_PLUGIN);

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
     * @return  arraye
     */
    function GetMainRequest()
    {
        return array('index'  => $this->_IsIndex,
                     'gadget' => $this->_RequestGadget,
                     'action' => $this->_RequestAction);
    }

    /**
     * Set true|false if a gadget has been updated so we don't check it again and again
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @param   string  $status  True if gadget is updated (installed and latest version)
     * @return  void
     */
    function SetGadgetAsUpdated($gadget, $status = true)
    {
        if (!empty($gadget) && !isset($this->_Gadgets[$gadget]['is_updated'])) {
            $this->_Gadgets[$gadget]['is_updated'] = $status;
        }
    }

    /**
     * Returns true or false is gadget has been marked as updated. If the gadget hasn't been marked
     * it returns null.
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  void
     */
    function IsGadgetMarkedAsUpdated($gadget)
    {
        if (!empty($gadget) && isset($this->_Gadgets[$gadget]['is_updated'])) {
            return $this->_Gadgets[$gadget]['is_updated'];
        }

        return null;
    }

    /**
     * Gets a list of installed gadgets (using Singleton), it uses
     * the /gadget/enabled_items
     *
     * @access  public
     * @return   array   Array of enabled_items (and updated)
     */
    function GetInstalledGadgets()
    {
        static $installedGadgets;

        if (isset($installedGadgets)) {
            return $installedGadgets;
        }
        $installedGadgets = array();

        $gs = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/enabled_items'));
        $ci = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/core_items'));
        $ci = str_replace(' ', '', $ci);
        $gs = array_merge($gs, $ci);

        if (count($gs) > 0) {
            foreach ($gs as $gadget) {
                if (file_exists(JAWS_PATH . 'gadgets/' . $gadget . '/Info.php')) {
                    if (Jaws_Gadget::IsGadgetUpdated($gadget)) {
                        $installedGadgets[$gadget] = $gadget;
                    }
                }
            }
        }

        return $installedGadgets;
    }

    /**
     * Gets the actions of a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget's name
     * @param   string  $type   Action's type(LayoutAction, NormalAction, AdminAction, ... or empty for all type)
     * @return  array   Gadget actions
     */
    function GetGadgetActions($gadget, $type = '')
    {
        if (!isset($this->_Gadgets[$gadget]['actions'])) {
            $file = JAWS_PATH . 'gadgets/' . $gadget . '/Actions.php';

            if (file_exists($file)) {
                require_once $file;
                if (isset($actions)) {
                    $tmp = array();

                    // key: Action Name  value: Action Properties
                    foreach ($actions as $action => $properties) {
                        $name   = isset($properties[1])? $properties[1] : $action;
                        $desc   = isset($properties[2])? $properties[2] : '';
                        $params = isset($properties[3])? $properties[3] : false;
                        $modes  = array_filter(array_map('trim', explode(',', $properties[0])));
                        foreach ($modes as $mode) {
                            @list($mode, $file) = array_filter(explode(':', $mode));
                            $tmp[$mode][$action] = array('name'   => $name,
                                                         'mode'   => $mode,
                                                         'desc'   => $desc,
                                                         'params' => $params,
                                                         'file'   => $file);
                        }
                    }
                    $this->_Gadgets[$gadget]['actions'] = $tmp;
                } else {
                    $this->_Gadgets[$gadget]['actions'] = array();
                }
            } else {
                $this->_Gadgets[$gadget]['actions'] = array();
            }
        }

        if (empty($type)) {
            return $this->_Gadgets[$gadget]['actions'];
        } else {
            return $this->_Gadgets[$gadget]['actions'][$type];
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
     * @return  object  The editor in /config/editor
     */
    function &LoadEditor($gadget, $name, $value = '', $filter = true, $label = '')
    {
        if ($filter && !empty($value)) {
            $xss   = $this->loadClass('XSS', 'Jaws_XSS');
            $value = $xss->filter($value);
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
     * Loads a class from within the Jaws dir
     *
     * @access  public
     * @param   string  $property Jaws app property name
     * @param   string  $class    Class name
     * @return  object  Date calender object
     */
    function loadClass($property, $class)
    {
        if (!isset($this->{$property})) {
            $file = JAWS_PATH . 'include'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
            if (!file_exists($file)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FILE_DOES_NOT_EXIST', $file),
                                        'File exists check');
                return $error;
            }

            include_once $file;

            if (!$this->classExists($class)) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_CLASS_DOES_NOT_EXIST', $class),
                                        'Class exists check');
                return $error;
            }

            $this->{$property} = new $class();
            if (Jaws_Error::IsError($this->{$property})) {
                $error = new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_INSTANCE', $file, $class),
                                        'Class file loading');
                return $error;
            }

            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loaded class: ' . $class . ', File: ' . $file);
        }

        return $this->{$property};
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

        $xss = $this->loadClass('XSS', 'Jaws_XSS');
        //TODO: Need to check which SERVER var is allways sent to the server
        if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
            $location = $xss->filter($_SERVER['SCRIPT_NAME']);
        } else {
            $location = $xss->filter($_SERVER['REQUEST_URI']);
        }
        $location = substr($location, 0, stripos($location, BASE_SCRIPT));
        return $location;
    }

    /**
     * Returns the URL of the site
     *
     * @param   string  suffix for add to site url
     * @param   string  rel_url relative url
     * @access  public
     * @return  string  Site's URL
     */
    function getSiteURL($suffix = '', $rel_url = false)
    {
        static $site_url;
        if (!isset($site_url)) {
            $cfg_url = isset($GLOBALS['app']->Registry)? $GLOBALS['app']->Registry->Get('/config/site_url') : '';
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
     * @param   string  suffix    suffix part of url
     * @param   bool    rel_url   relative url
     * @param   bool    base_data use JAWS_BASE_DATA instead of JAWS_DATA
     * @access  public
     * @return  string  Related URL to data directory
     */
    function getDataURL($suffix = '', $rel_url = true, $base_data = false)
    {
        if (!defined('JAWS_DATA_URL') || $base_data) {
            $url = substr($base_data? JAWS_BASE_DATA : JAWS_DATA, strlen(JAWS_PATH));
            if (DIRECTORY_SEPARATOR !='/') {
                $url = str_replace('\\', '/', $url);
            }
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
     * Executes the autoload gadgets
     *
     * @return  void
     */
    function RunAutoload()
    {
        $data    = $GLOBALS['app']->Registry->Get('/gadgets/autoload_items');
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
     * Returns a gadget hook of a specific gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget we want to load (where the hook is)
     * @param   string  $hook    Gadget hook (the hook name)
     * @return  object  Gadget's hook if it exists or false
     */
    function loadHook($gadget, $hook)
    {
        $hookName = $gadget.$hook.'Hook';
        if (!isset($this->_Classes[$hookName])) {
            $hookFile = JAWS_PATH . 'gadgets/' . $gadget . '/hooks/' . $hook . '.php';
            if (file_exists($hookFile)) {
                include_once $hookFile;
            }

            if (!Jaws::classExists($hookName)) {
                return false;
            }

            $obj = new $hookName();
            $this->_Classes[$hookName] = $obj;
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG,
                                 'Loaded hook: ' . $hook . ' of gadget '. $gadget. ', File: ' . $hookFile);
        }
        return $this->_Classes[$hookName];
    }

    /**
     * Checks if a class exists without triggering __autoload
     *
     * @param   string  classname
     * @return  bool    true success and false on error
     *
     * @access  public
     */
    function classExists($classname)
    {
        if (version_compare(PHP_VERSION, '5.0', '>=')) {
            return class_exists($classname, false);
        }
        return class_exists($classname);
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
            if (($this->Registry->Get('/config/gzip_compression') != 'true') ||
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
            $robots = explode(',', $this->Registry->Get('/config/robots'));
            $robots = array_map('strtolower', $robots);
            $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $uagent = strtolower($GLOBALS['app']->XSS->parse($_SERVER['HTTP_USER_AGENT']));
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