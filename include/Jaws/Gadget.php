<?php
/**
 * Jaws Gadgets class
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget
{
    /**
     * Language translate name of the gadget
     *
     * @var     string
     * @access  public
     */
    var $title = '';

    /**
     * Language translate description of the gadget
     *
     * @var     string
     * @access  public
     */
    var $description = '';

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
     * Required gadgets
     *
     * @var     array
     * @access  private
     */
    var $_Requires = array();

    /**
     * Attributes of the gadget
     *
     * @var     array
     * @access  private
     */
    var $_Attributes = array();

    /**
     * Name of the gadget
     *
     * @var     string
     * @access  private
     */
    var $name = '';

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
     * Store extension objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $extensions = array();

    /**
     * Store models objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $models = array();

    /**
     * Store actions objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $actions = array();

    /**
     * Store hooks objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $hooks = array();

    /**
     * Store events objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $events = array();

    /**
     * Store installer object for later use so we aren't running around with multiple copies
     * @var     object
     * @access  protected
     */
    var $installer;

    /**
     * Constructor
     *
     * @access  protected
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    function Jaws_Gadget($gadget)
    {
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        $this->name = $gadget;

        // load gadget Request interface
        $this->request = new Jaws_Gadget_Request($this);

        // load gadget ACL interface
        $this->acl = new Jaws_Gadget_ACL($this);
        // load gadget registry interface
        $this->registry = new Jaws_Gadget_Registry($this);
        // Load gadget's language file
        $GLOBALS['app']->Translate->LoadTranslation($this->name, JAWS_COMPONENT_GADGET);

        $this->title       = _t(strtoupper($gadget).'_NAME');
        $this->description = _t(strtoupper($gadget).'_DESCRIPTION');
    }

    /**
     * Creates the Jaws_Gadget instance
     *
     * @return  object returns the instance
     * @access  public
     */
    static function getInstance($gadget)
    {
        static $instances = array();
        $gadget = preg_replace('/[^[:alnum:]_]/', '', $gadget);
        if (!isset($instances[$gadget])) {
            if (!is_dir(JAWS_PATH . "gadgets/$gadget")) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_GADGET_DOES_NOT_EXIST', $gadget),
                    __FUNCTION__
                );
            }

            $file = JAWS_PATH . "gadgets/$gadget/Info.php";
            if (!file_exists($file)) {
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

            require_once $file;
            $classname = $gadget. '_Info';
            $instances[$gadget] = new $classname($gadget);
            if (!Jaws_Error::IsError($instances[$gadget])) {
                $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget: $gadget");
            }
        }

        return $instances[$gadget];
    }

    /**
     * Sets an attribute
     *
     * @access  protected
     * @param   string $key         Attribute name
     * @param   string $value       Attribute value
     * @param   string $description Attribute description
     * @return  void
     */
    function SetAttribute($key, $value, $description = '')
    {
        $this->_Attributes[$key] = array(
            'value'       => $value,
            'description' => $description
        );
    }

    /**
     * Returns the value of the given attribute key
     *
     * @access  protected
     * @param   string $key Attribute name
     * @return  mixed  value of the given attribute key
     */
    function GetAttribute($key)
    {
        if (array_key_exists($key, $this->_Attributes)) {
            return $this->_Attributes[$key]['value'];
        }

        return null;
    }

    /**
     * Get all attributres for the gadget
     *
     * @access  public
     * @return  array Attributes of the gadget
     */
    function GetAttributes()
    {
        return $this->_Attributes;
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
            $jawsVersion = $GLOBALS['app']->Registry->fetch('version');
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
        $lang = $GLOBALS['app']->GetLanguage();
        return str_replace(array('{url}', '{lang}', '{page}', '{lower-page}',
                                 '{type}', '{lower-type}', '{types}', '{lower-types}'),
                           array($this->_Wiki_URL, $lang, $this->name, strtolower($this->name),
                                 'Gadget', 'gadget', 'Gadgets', 'gadgets'),
                           $this->_Wiki_Format);
    }

    /**
     * Register required gadgets
     *
     * @access  public
     * @param   mixed   $argv Optional variable list of required gadgets
     * @return  void
     */
    function Requires($argv)
    {
        $this->_Requires = func_get_args();
    }

    /**
     * Get the requirements of the gadget
     *
     * @access  public
     * @return  array Gadget's Requirements
     */
    function GetRequirements()
    {
        return $this->_Requires;
    }

    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text           The Text to parse
     * @param   string  $gadget         The Gadget name
     * @param   string  $plugins_set    Plugins set name(admin or index)
     * @return  string  Returns the parsed text
     */
    function ParseText($text, $gadget = '', $plugins_set = 'admin')
    {
        $res = $text;
        $gadget = empty($gadget)? $this->name : $gadget;

        $plugins = $GLOBALS['app']->Registry->fetch('plugins_installed_items');
        if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
            $plugins = array_filter(explode(',', $plugins));
            foreach ($plugins as $plugin) {
                $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
                if (!Jaws_Error::IsError($objPlugin)) {
                    $use_in = '*';
                    if ($plugins_set == 'admin') {
                        $use_in = $GLOBALS['app']->Registry->fetch('backend_gadgets', $plugin);
                    } else {
                        $use_in = $GLOBALS['app']->Registry->fetch('frontend_gadgets', $plugin);
                    }
                    if (!Jaws_Error::isError($use_in) &&
                       ($use_in == '*' || in_array($gadget, explode(',', $use_in))))
                    {
                        $res = $objPlugin->ParseText($res);
                    }
                }
            }
        }

        //So we don't call require_once each time we invoke it
        if (!Jaws::classExists('Jaws_String')) {
            require JAWS_PATH . 'include/Jaws/String.php';
        }
        $res = Jaws_String::AutoParagraph($res);

        return $res;
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
        $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
        return (false !== strpos($installed_gadgets, ",{$gadget},")) && is_dir(JAWS_PATH. "gadgets/{$gadget}");
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
                $gadgets_status[$gadget] = version_compare($objGadget->version, $current_version, '>')? false : true;
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
            $disabled_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_disabled_items');
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
        return $GLOBALS['app']->Session->GetPermission(
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
        return $GLOBALS['app']->Session->CheckPermission(
            empty($gadget)? $this->name : $gadget,
            $key,
            $subkey ,
            $together,
            $errorMessage
        );
    }

    /**
     * Search in map and return its url if found
     *
     * @access  protected
     * @param   string  $action    Action name
     * @param   array   $params    Parameters of action
     * @param   bool    $abs_url   Absolute or relative URL
     * @param   string  $gadget    Gadget name
     * @return  string  The mapped URL
     */
    function urlMap($action='', $params = array(), $abs_url = false, $gadget = '')
    {
        return $GLOBALS['app']->Map->GetURLFor(empty($gadget)? $this->name : $gadget, $action, $params, $abs_url);
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
     * Overloading magic method
     *
     * @access  private
     * @param   string  $method     Method name
     * @param   string  $arguments  Method parameters
     * @return  mixed   Requested object otherwise Jaws_Error
     */
    function __call($method, $arguments)
    {
        switch ($method) {
            case 'loadAdminModel':
            case 'loadAdminAction':
            case 'loadAdminTemplate':
                array_unshift($arguments, true);
                $extension = substr($method, 9);
                $model_class_name = "Jaws_Gadget_$extension";
                if (!isset($this->extensions[$extension])) {
                    $this->extensions[$extension] = new $model_class_name($this);
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded extension: [$extension]");
                }

                return call_user_func_array(array($this->extensions[$extension], 'load'), $arguments);
                break;

            case 'loadModel':
            case 'loadAction':
            case 'loadTemplate':
                array_unshift($arguments, false);

            case 'loadHook':
            case 'loadEvent':
            case 'loadInstaller':
                $extension = substr($method, 4);
                $model_class_name = "Jaws_Gadget_$extension";
                if (!isset($this->extensions[$extension])) {
                    $this->extensions[$extension] = new $model_class_name($this);
                    $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded extension: [$extension]");
                }

                return call_user_func_array(array($this->extensions[$extension], 'load'), $arguments);
                break;
        }

        return Jaws_Error::raiseError("Method '$method' not exists!", __FUNCTION__);
    }

}