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
     * Is this gadget has layout action?
     *
     * @var     bool
     * @access  private
     */
    var $_has_layout = true;

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
     * Action that gadget will execute
     *
     * @var     string
     * @access  protected
     * @see SetAction()
     * @see GetAction()
     */
    var $_Action;

    /**
     * Store component objects for later use so we aren't running
     * around with multiple copies
     * @var     array
     * @access  protected
     */
    var $components = array();

    /**
     * Store models objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  protected
     */
    var $models = array();

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
     * Loads the gadget extension file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $extension  Extension name
     * @return  mixed   Extension class object on successful, Jaws_Error otherwise
     */
    function &load($extension)
    {
        // filter non validate character
        $extension = preg_replace('/[^[:alnum:]_]/', '', $extension);
        $model_class_name = "Jaws_Gadget_$extension";
        if (!isset($this->components[$extension])) {
            $file = JAWS_PATH. "include/Jaws/Gadget/$extension.php";
            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            include_once($file);
            if (!Jaws::classExists($model_class_name)) {
                return Jaws_Error::raiseError("Class [$model_class_name] not exists!", __FUNCTION__);
            }

            $this->components[$extension] = new $model_class_name($this);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded extension: [$extension]");
        }

        return $this->components[$extension];
    }

    /**
     * Loads a template from a file
     *
     * @access  public
     * @param   string  $fname      File name
     * @param   string  $fpath      File path
     * @param   string  $gadget     Gadget name(owner of template file)
     * @param   string  $options    Load template options(e.g. loadFromTheme, loadRTLDirection)
     * @return  object  Jaws_Template object
     */
    function &loadTemplate($fname, $fpath = '', $gadget = '', $options = array())
    {
        $tpl = new Jaws_Template();
        if (empty($fpath) || in_array($fpath, array('admin', 'index'))) {
            $subdir = empty($fpath)? JAWS_SCRIPT : $fpath;
            $subdir = ($subdir == 'admin')? 'Admin' : '';
            $gadget = empty($gadget)? $this->name : $gadget;
            $fpath  = "gadgets/$gadget/templates/$subdir";
        }

        // set options
        foreach ($options as $option => $value) {
            $tpl->$option = $value;
        }

        $tpl->Load($fname, $fpath, $gadget);
        return $tpl;
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
                $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
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
        $image = Jaws::CheckImage('gadgets/'.$name.'/images/logo.png');
        return $image;
    }

}