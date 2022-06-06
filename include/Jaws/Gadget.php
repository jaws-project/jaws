<?php
/**
 * Jaws Gadgets class
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Gadget version
     *
     * @var     string
     * @access  public
     */
    var $version = '';

    /**
     * Required Jaws version required
     *
     * @var     string
     * @access  private
     */
    var $_Req_JawsVersion = '';

    /**
     * Minimum PHP version required
     *
     * @var     string
     * @access  private
     */
    var $_Min_PHPVersion = '';

    /**
     * Is this gadget core gadget?
     *
     * @var     bool
     * @access  private
     */
    var $_IsCore = false;

    /**
     * Section of the gadget(Gadget, Customers, etc..)
     *
     * @var     string
     * @access  private
     */
    var $_Section = '';

    /**
     * Base URL of gadget's documents
     *
     * @var     string
     * @access  private
     */
    var $_Wiki_URL = JAWS_WIKI;

    /**
     * Format of gadget's documents url
     *
     * @var     string
     * @access  private
     */
    var $_Wiki_Format = JAWS_WIKI_FORMAT;

    /**
     * Requirement gadgets
     *
     * @var     array
     * @access  public
     */
    public $requirement = array();

    /**
     * Recommended/Optional gadgets
     *
     * @var     array
     * @access  public
     */
    public $recommended = array();

    /**
     * Attributes of the gadget
     *
     * @var     array
     * @access  private
     */
    private $attributes = array();

    /**
     * Name of the gadget
     *
     * @var     string
     * @access  private
     */
    var $name = '';

    /**
     * Path of the gadget
     *
     * @var     string
     * @access  private
     */
    var $path = '';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = false;

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = false;

    /**
     * Store actions/models/events objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  public
     */
    public $objects = array();

    /**
     * Actions attributes array
     * @var     array
     * @access  public
     */
    public $actions = array();

    /**
     * Loaded actions
     * @var     array
     * @access  public
     */
    public $loaded_actions = array();


    /**
     * Constructor
     *
     * @access  protected
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    protected function __construct($gadget)
    {
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        $this->name = $gadget;
        $this->path = ROOT_JAWS_PATH. 'gadgets/'. $gadget . '/';
        $this->app  = Jaws::getInstance();
    }

    /**
     * Creates the Jaws_Gadget instance
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  object returns the instance
     */
    static function getInstance($gadget)
    {
        static $instances = array();
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        if (!isset($instances[$gadget])) {
            if (!is_dir(ROOT_JAWS_PATH . "gadgets/$gadget")) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                    __FUNCTION__
                );
            }

            $file = ROOT_JAWS_PATH . "gadgets/$gadget/Info.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_GADGET_DOES_NOT_EXIST', $gadget),
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
                        Jaws::t('ERROR_GADGET_NOT_AVAILABLE', $gadget),
                        __FUNCTION__,
                        JAWS_ERROR_INFO
                    );
                }
            }

            require_once $file;
            $classname = $gadget. '_Info';
            $instances[$gadget] = new $classname($gadget);
            if (!Jaws_Error::IsError($instances[$gadget])) {
                $GLOBALS['log']->Log(JAWS_DEBUG, "Loaded gadget: $gadget");
            }
        }

        return $instances[$gadget];
    }

    /**
     * Execute main requested gadget/action
     *
     * @access  public
     * @return  array   Returns result of execution of requested gadget/action
     */
    static function ExecuteMainRequest()
    {
        $IsIndex = false;
        $objAction = null;
        $reqGadgetVersion = '';
        $isReqActionStandAlone = false;
        $jawsApp = Jaws::getInstance();

        // Only registered user can access not global website
        $privateAccess = $jawsApp->registry->fetch('global_website', 'Settings') == 'false';
        $privateAccess = $privateAccess || (JAWS_SCRIPT == 'admin');

        // Get forwarded error from web-server
        $reqError = $jawsApp->request->fetch('http_error', 'get');

        if (empty($reqError) && $jawsApp->map->Parse()) {
            $reqGadget = Jaws_Gadget::filter($jawsApp->request->fetch('reqGadget'));
            $reqAction = Jaws_Gadget::filter($jawsApp->request->fetch('reqAction'));

            if (empty($reqGadget)) {
                $IsIndex = true;
                if (JAWS_SCRIPT == 'index') {
                    // FIXME:: there is no layout attribute!
                    $reqGadget = $jawsApp->registry->fetchByUser(
                        $jawsApp->session->user->layout,
                        'main_gadget',
                        'Settings'
                    );
                } else {
                    $reqGadget = 'ControlPanel';
                }
            }

            if (!empty($reqGadget) && $reqGadget != '-') {
                if (Jaws_Gadget::IsGadgetEnabled($reqGadget)) {
                    // set main requested attributes
                    if (JAWS_SCRIPT == 'index') {
                        $objAction = Jaws_Gadget::getInstance($reqGadget)->action->load();
                        if (Jaws_Error::IsError($objAction)) {
                            Jaws_Error::Fatal("Error loading gadget: $reqGadget");
                        }
                        $reqAction = empty($reqAction)? $objAction->gadget->default_action : $reqAction;
                    } else {
                        $objAction = Jaws_Gadget::getInstance($reqGadget)->action->loadAdmin();
                        if (Jaws_Error::IsError($objAction)) {
                            Jaws_Error::Fatal("Error loading gadget: $reqGadget");
                        }
                        $reqAction = empty($reqAction)? $objAction->gadget->default_admin_action : $reqAction;
                    }

                    $reqGadgetVersion = $objAction->gadget->version;
                    $jawsApp->mainRequest = $objAction->getAttributes($reqAction);
                    $jawsApp->mainRequest['gadget'] = $reqGadget;
                    $jawsApp->mainRequest['action'] = $reqAction;

                    // check referrer host for internal action
                    if (@$jawsApp->mainRequest['internal'] &&
                        (!$jawsApp->session->extraCheck() || Jaws_Utils::getReferrerHost() != $_SERVER['HTTP_HOST'])
                    ) {
                        $reqError = '403';
                    }

                    $jawsApp->define('', 'mainGadget', $reqGadget);
                    $jawsApp->define('', 'mainAction', $reqAction);
                } else {
                    $reqError = '404';
                    $reqGadget = null;
                    $reqAction = null;
                }
            }

            if ($privateAccess &&
                !$jawsApp->session->user->logged &&
                (empty($objAction) || !$objAction->getAttribute($reqAction, 'global'))
            ) {
                // FIXME: RunAutoload !!!
                Jaws_Header::Location(
                    Jaws_Gadget::getInstance('Users')->gadget->url(
                        'Login',
                        array('referrer' => bin2hex(Jaws_Utils::getRequestURL()))
                    )
                );
            }

        } else {
            $reqError = empty($reqError)? '404' : $reqError;
            $reqGadget = null;
            $reqAction = null;
        }

        $jawsApp->mainIndex = $IsIndex;
        // Run auto-load methods before standalone actions too
        $jawsApp->RunAutoload();
        // Init layout if action mode not standalone
        if (!$isReqActionStandAlone) {
            $jawsApp->layout->Load();
        }

        if (empty($reqError)) {
            $reqResult = '';
            if (!empty($objAction)) {
                // set in main request
                $jawsApp->inMainRequest = true;
                $reqResult = $objAction->Execute($reqAction, null, '', $privateAccess);
                if (Jaws_Error::isError($reqResult)) {
                    $reqResult = $reqResult->GetMessage();
                }
                $jawsApp->inMainRequest = false;

                // we must check type of action after execute, because gadget can change it at runtime
                if (!$objAction->IsStandAlone($reqAction) && $jawsApp->request->fetch('mode') !== 'standalone') {
                    // if mode was standalone before action execute, we need init layout
                    if ($isReqActionStandAlone) {
                        $jawsApp->layout->Load();
                    }
                    $isReqActionStandAlone = false;
                } else {
                    $isReqActionStandAlone = true;
                }
            }
        } else {
            $reqResult = Jaws_HTTPError::Get($reqError);
        }

        return array(
            'gadget'  => $reqGadget,
            'action'  => $reqAction,
            'return'  => $reqResult,
            'error'   => $reqError,
            'version' => $reqGadgetVersion,
            'standalone' => $isReqActionStandAlone,
        );
    }

    /**
     * Sets an attribute
     *
     * @access  public
     * @param   string $key     Attribute name
     * @param   string $value   Attribute value
     * @return  void
     */
    function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Returns the value of the given attribute key
     *
     * @access  public
     * @param   string $key Attribute name
     * @return  mixed  value of the given attribute key
     */
    function getAttribute($key)
    {
        return @$this->attributes[$key];
    }

    /**
     * Get all attributes for the gadget
     *
     * @access  public
     * @return  array Attributes of the gadget
     */
    function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets a define
     *
     * @access  public
     * @param   string $key     Define name
     * @param   string $value   Define value
     * @return  void
     */
    function define($key, $value = '')
    {
        $this->app->define($this->name, $key, $value);
    }

    /**
     * Get all defines of the gadget
     *
     * @access  public
     * @return  array   Defines of the gadget
     */
    function defines()
    {
        return $this->app->defines($this->name);
    }

    /**
     * Gets the gadget's section
     *
     * @access  public
     * @return  string Gadget's section
     */
    function GetSection()
    {
        if ($this->_IsCore) {
            $this->_Section = 'General';
        } elseif (empty($this->_Section)) {
            $this->_Section = 'Gadgets';
        }

        return $this->_Section;
    }

    /**
     * Gets the jaws version that the gadget requires
     *
     * @access  public
     * @return  string   jaws version
     */
    function GetRequiredJawsVersion()
    {
        $jawsVersion = $this->_Req_JawsVersion;
        if (empty($jawsVersion)) {
            $jawsVersion = $this->app->registry->fetch('version');
        }

        return $jawsVersion;
    }

    /**
     * Gets the minimum php version that the gadget requires
     *
     * @access  public
     * @return  string   jaws version
     */
    function GetMinimumPHPVersion()
    {
        $phpVersion = $this->_Min_PHPVersion;
        if (empty($phpVersion)) {
            $phpVersion = PHP_VERSION;
        }

        return $phpVersion;
    }

    /**
     * Gets the gadget doc/manual URL
     *
     * @access  public
     * @return  string Gadget's manual/doc url
     */
    function GetDoc()
    {
        $lang = $this->app->GetLanguage();
        return str_replace(array('{url}', '{lang}', '{page}', '{lower-page}',
                                 '{type}', '{lower-type}', '{types}', '{lower-types}'),
                           array($this->_Wiki_URL, $lang, $this->name, strtolower($this->name),
                                 'Gadget', 'gadget', 'Gadgets', 'gadgets'),
                           $this->_Wiki_Format);
    }

    /**
     * Returns is gadget installed
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  bool    True or false, depends of the gadget status
     */
    public static function IsGadgetInstalled($gadget)
    {
        $installed_gadgets = Jaws::getInstance()->registry->fetch('gadgets_installed_items');
        return (false !== strpos($installed_gadgets, ",{$gadget},")) && is_dir(ROOT_JAWS_PATH. "gadgets/{$gadget}");
    }

    /**
     * Returns true or false if the gadget is running the version the Info.php says
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  bool    True or false, depends of the jaws version
     */
    public static function IsGadgetUpdated($gadget)
    {
        static $gadgets_status;
        if (!isset($gadgets_status)) {
            $gadgets_status = array();
        }

        if (!array_key_exists($gadget, $gadgets_status)) {
            $gadgets_status[$gadget] = false;
            if (self::IsGadgetInstalled($gadget)) {
                $objGadget = Jaws_Gadget::getInstance($gadget);
                $current_version = $objGadget->registry->fetch('version');
                if (false === $gadgets_status[$gadget] = $current_version == $objGadget->version) {
                    // build version not equal
                    if (strrstr($current_version, '.', true) == strrstr($objGadget->version, '.', true)) {
                        $gadgets_status[$gadget] = true;
                        // update build version
                        $objGadget->registry->update('version', $objGadget->version);
                    }
                }
            }
        }

        return $gadgets_status[$gadget];
    }

    /**
     * Returns is gadget enabled
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  bool    True or false, depends of the gadget status
     */
    public static function IsGadgetEnabled($gadget)
    {
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        if (!self::IsGadgetUpdated($gadget)) {
            return false;
        }

        static $disabled_gadgets;
        if (!isset($disabled_gadgets)) {
            $disabled_gadgets = Jaws::getInstance()->registry->fetch('gadgets_disabled_items');
        }

        return (false === strpos($disabled_gadgets, ",{$gadget},"));
    }

    /**
     * Filter non validate character
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  string  Filtered gadget name
     */
    public static function filter($gadget)
    {
        return preg_replace('/[^[:alnum:]_]/', '', @(string)$gadget);
    }

    /**
     * Get permission on a gadget/task
     *
     * @param   string  $key    ACL key(s) name
     * @param   string  $subkey ACL subkey name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $gadget Gadget name
     * @return  bool    True if granted, else False
     */
    function GetPermission($key, $subkey = '', $together = true, $gadget = false)
    {
        return $this->app->session->GetPermission(
            empty($gadget)? $this->name : $gadget,
            $key,
            $subkey,
            $together
        );
    }

    /**
     * Check permission on a gadget/task
     *
     * @param   string  $key            ACL key(s) name
     * @param   string  $subkey         ACL subkey name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $gadget         Gadget name
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function CheckPermission($key, $subkey = '', $together = true, $gadget = false, $errorMessage = '')
    {
        return $this->app->session->CheckPermission(
            empty($gadget)? $this->name : $gadget,
            $key,
            $subkey ,
            $together,
            $errorMessage
        );
    }

    /**
     * Gets raw url
     *
     * @access  public
     * @param   string  $action    Action name
     * @param   array   $params    Parameters of action
     * @param   array   $options    URL options(restype, mode, ...)
     * @param   string  $gadget    Gadget name
     * @return  string  The mapped URL
     */
    function url($action='', $params = array(), $options = array(), $gadget = '')
    {
        if (!is_array($options)) {
            $GLOBALS['log']->Log(JAWS_ERROR, 'use options["absolute"] = true|false for set absolute url', 1);
            $absolute = (bool)$options;
            $options = array();
            $options['absolute'] = $absolute;
        }

        return $this->app->map->GetRawURL(
            empty($gadget)? $this->name : $gadget,
            $action,
            $params,
            $options
        );
    }

    /**
     * Search in map and return its url if found
     *
     * @access  public
     * @param   string  $action    Action name
     * @param   array   $params    Parameters of action
     * @param   array   $options    URL options(restype, mode, ...)
     * @param   string  $gadget    Gadget name
     * @return  string  The mapped URL
     */
    function urlMap($action='', $params = array(), $options = array(), $gadget = '')
    {
        if (!is_array($options)) {
            $GLOBALS['log']->Log(JAWS_ERROR, 'use options["absolute"] = true|false for set absolute url', 1);
            $absolute = (bool)$options;
            $options = array();
            $options['absolute'] = $absolute;
        }

        return $this->app->map->GetMappedURL(
            empty($gadget)? $this->name : $gadget,
            $action,
            $params,
            $options
        );
    }

    /**
     * Returns an URL to the gadget icon
     *
     * @access  public
     * @return  string Icon URL
     * @param   string $name Name of the gadget, if no name is provided use instanced gadget
     */
    function GetIconURL($name = null)
    {
        if (empty($name)) {
            $name = $this->name;
        }
        $image = Jaws::CheckImage('gadgets/'.$name.'/Resources/images/logo.png');
        return $image;
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
        @list($string, $lang) = explode('|', $input);
        if ($gadget = strstr($string, '.', true)) {
            $string = substr($string, strlen($gadget) + 1);
        } else {
            $gadget = strstr(get_called_class(), '_', true);
        }

        return Jaws_Translate::getInstance()->XTranslate(
            $lang,
            Jaws_Translate::TRANSLATE_GADGET,
            $gadget,
            $string,
            $params
        );
    }

    /**
     * Overloading __call magic method
     *
     * @access  private
     * @param   string  $method     Method name
     * @param   string  $arguments  Method parameters
     * @return  mixed   Requested object otherwise Jaws_Error
     */
    function __call($method, $arguments)
    {
        return Jaws_Error::raiseError("Method '$method' not exists!", __FUNCTION__);
    }

    /**
     * Overloading __get magic method
     *
     * @access  private
     * @param   string  $property   Property name
     * @return  mixed   Requested property otherwise Jaws_Error
     */
    function __get($property)
    {
        switch ($property) {
            case 'gadget':
                return $this;
                break;

            case 'title':
            case 'description':
                return $this::t($property);
                break;

            case 'acl':
                $classname = 'Jaws_Gadget_ACL';
                break;

            case 'hook':
            case 'users':
            case 'groups':
            case 'event':
            case 'model':
            case 'plugin':
            case 'action':
            case 'layout':
            case 'request':
            case 'session':
            case 'template':
            case 'registry':
            case 'installer':
            case 'translate':
                $classname = 'Jaws_Gadget_'. ucfirst($property);
                break;

            default:
                return Jaws_Error::raiseError(
                    "Property '$property' not exists!",
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    -1
                );
        }

        $this->$property = new $classname($this);
        return $this->$property;
    }

}