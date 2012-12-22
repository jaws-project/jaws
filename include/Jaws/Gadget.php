<?php
/**
 * Jaws Gadgets class
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget
{
    /**
     * Language translate name of the gadget
     *
     * @var     string
     * @access  private
     */
    var $_Title = '';

    /**
     * Language translate description of the gadget
     *
     * @var     string
     * @access  private
     */
    var $_Description = '';

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '';

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
     * Default ACL value of frontend gadget access
     *
     * @var     bool
     * @access  private
     */
    var $_DefaultACL = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array();

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
     * A list of actions that the gadget has
     *
     * @var     array
     * @access  protected
     * @see AddAction()
     */
    var $_ValidAction = array();

    /**
     * Flag to know if the actions have been loaded
     *
     * @var     bool
     * @access  private
     */
    var $_LoadedActions = false;

    /**
     * Store component objects for later use so we aren't running
     * around with multiple copies
     * @var     array
     * @access  protected
     */
    var $components = array();

    /**
     * Store model objects for later use so we aren't running
     * around with multiple copies
     * @var     array
     * @access  protected
     */
    var $models = array();

    /**
     * Store installer objects for later use so we aren't running
     * around with multiple copies
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

        // Load gadget's language file
        $GLOBALS['app']->Translate->LoadTranslation($this->name, JAWS_COMPONENT_GADGET);
        // load ACLs
        $GLOBALS['app']->ACL->LoadFile($this->name);

        $this->_Title       = _t(strtoupper($gadget).'_NAME');
        $this->_Description = _t(strtoupper($gadget).'_DESCRIPTION');
        $this->LoadActions();
    }

    /**
     * Loads the gadget model file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $model   Model name
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    function &load($extension)
    {
        // filter non validate character
        $extension = preg_replace('/[^[:alnum:]_]/', '', $extension);
        $model_class_name = "Jaws_Gadget_$extension";
        if (!isset($this->components[$extension])) {
            $file = JAWS_PATH. "include/Jaws/Gadget/$extension.php";
            if (!@include_once($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            if (!Jaws::classExists($model_class_name)) {
                return Jaws_Error::raiseError("Class [$model_class_name] not exists!", __FUNCTION__);
            }

            $this->components[$extension] = new $model_class_name($this);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded extension: [$extension]");
        }

        return $this->components[$extension];
    }

    /**
     * Load the gadget's action stuff files
     *
     * @access  public
     */
    function LoadActions()
    {
        if (!$this->_LoadedActions) {
            $this->_ValidAction = $GLOBALS['app']->GetGadgetActions($this->name);
            if (!isset($this->_ValidAction['index']['DefaultAction'])) {
                $this->_ValidAction['index']['DefaultAction'] = array(
                    'name' => 'DefaultAction',
                    'normal' => true,
                    'desc' => '',
                    'file' => null
                );
            }

            if (!isset($this->_ValidAction['admin']['Admin'])) {
                $this->_ValidAction['admin']['Admin'] = array(
                    'name' => 'Admin',
                    'normal' => true,
                    'desc' => '',
                    'file' => null
                );
            }

            $this->_LoadedActions = true;
        }
    }

    /**
     * Gets the gadget name
     *
     * @access  public
     * @return  string   Gadget name
     */
    function GetGadget()
    {
        return $this->name;
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
     * Gets the gadget translated name
     *
     * @access  protected
     * @return  string   Gadget translated name
     */
    function GetTitle()
    {
        return $this->_Title;
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
            $jawsVersion = $GLOBALS['app']->Registry->Get('version');
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
     * Gets the gadget description
     *
     * @access  public
     * @return  string   Gadget description
     */
    function GetDescription()
    {
        return $this->_Description;
    }

    /**
     * Gets the gadget version
     *
     * @access  public
     * @return  string Gadget's version
     */
    function GetVersion()
    {
        return $this->_Version;
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
     * Gets the short description of a given ACL key
     *
     * @access  public
     * @param   string $key  ACL Key
     * @return  string The ACL description
     */
    function GetACLDescription($key)
    {
        $key = substr(strrchr($key, '/'), 1);
        if (in_array($key, array('default', 'default_admin', 'default_registry'))) {
            return _t(strtoupper('GLOBAL_ACL_'. $key));
        } else {
            return _t(strtoupper($this->name. '_ACL_'. $key));
        }
    }

    /**
     * Get all ACLs for the gadet
     *
     * @access  public
     * @return  array   ACLs of the gadget
     */
    function GetACLs()
    {
        $result = array();
        foreach ($this->_ACLs as $key => $value) {
            //ACL comes with a value?
            if ($value === 'true' || $value === 'false') {
                $default = $value;
                $acl     = $key;
            } else {
                //False by default
                $default = 'false';
                $acl     = $value;
            }
            $result['/ACL/gadgets/'. $this->name. '/'. $acl] = $default;
        }

        // Adding common ACL keys
        $result['/ACL/gadgets/'. $this->name. '/default'] = $this->_DefaultACL? 'true' : 'false';
        $result['/ACL/gadgets/'. $this->name. '/default_admin'] = 'false';
        $result['/ACL/gadgets/'. $this->name. '/default_registry'] = 'false';

        return $result;
    }

    /**
     * Set the action to execute
     *
     * @access  public
     * @param   string  $value Gadget's Action
     */
    function SetAction($value)
    {
        $this->_Action = $value;
    }

    /**
     * Get the gadget's action
     *
     * @access  public
     * @return  string  Returns the gadget's action
     */
    function GetAction()
    {
        return $this->_Action;
    }

    /**
     * Execute the action
     *
     * @access  public
     */
    function Execute()
    {
        // is an empty action?
        if (empty($this->_Action) || $this->_Action == 'DefaultAction') {
            $this->_Action = $this->GetRegistry('default_action');
            if (empty($this->_Action)) {
                $this->_Action = 'DefaultAction';
            }
        }

        // Cut of the param part since we only want to validate the action name
        if ($pos = strpos($this->_Action, '(')) {
            $action = substr($this->_Action, 0, $pos);
        } else {
            $action = $this->_Action;
        }

        if (!$this->IsValidAction($action)) {
            Jaws_Error::Fatal('Invalid action: '. $action);
        }

        $file = $this->_ValidAction[JAWS_SCRIPT][$action]['file'];
        if (!empty($file)) {
            $objAction = $GLOBALS['app']->LoadGadget(
                $this->name,
                JAWS_SCRIPT == 'index'? 'HTML' : 'AdminHTML',
                $file
            );
        }

        $action2execute = $this->_Action;
        if (strpos($action2execute, '(')) {
            // FIXME: This is a hack to support actions with params,
            //        currently only supports 1 parameter.
            $regexp = "/([A-Za-z]+)\((.*)\)/sm";
            preg_match ($regexp, $action2execute, $matches, PREG_OFFSET_CAPTURE);
            $method = $matches[1][0];
            $params = $matches[2][0];
            return call_user_func(array($this, $method), $params);
        }

        return empty($file)? $this->$action2execute() : $objAction->$action2execute();
    }

    /**
     * Adds a new Action
     *
     * @access  protected
     * @param   string  $name   Action name
     * @param   string  $script Action script
     * @param   string  $mode   Action mode
     * @param   string  $description Action's description
     */
    function AddAction($action, $script, $mode, $description, $file = null)
    {
        $this->_ValidAction[$script][$action] = array(
            'name' => $action,
            $mode => true,
            'desc' => $description,
            'file' => $file
        );
    }

    /**
     * Set a Action mode
     *
     * @access  protected
     * @param   string  $name       Action's name
     * @param   string  $new_mode   Action's new mode
     * @param   string  $old_mode   Action's old mode
     * @param   string  $desc       Action's description
     */
    function SetActionMode($action, $new_mode, $old_mode, $desc = null, $file = null)
    {
        $this->_ValidAction[JAWS_SCRIPT][$action] = array(
            'name' => $action,
            $new_mode => true,
            $old_mode => false,
            'desc' => $desc,
            'file' => $file
        );
    }

    /**
     * Adds a normal action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function NormalAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'index', 'normal', $description);
    }

    /**
     * Adds an admin action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function AdminAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'admin', 'normal', $description);
    }

    /**
     * Verifies if the action is for admin users(for controlpanel)
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is for admin users, if not, returns false
     */
    function IsAdmin($action)
    {
        if ($this->IsValidAction($action, 'admin')) {
            return (isset($this->_ValidAction['admin'][$action]['normal']) &&
                    $this->_ValidAction['admin'][$action]['normal']) ||
                   (isset($this->_ValidAction['admin'][$action]['standalone']) &&
                    $this->_ValidAction['admin'][$action]['standalone']);
        }

        return false;
    }

    /**
     * Verifies if action is normal
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is normal, if not, returns false
     */
    function IsNormal($action)
    {
        if (empty($action)) {
            $action = 'DefaultAction';
        }

        if ($this->IsValidAction($action, 'index')) {
            return (isset($this->_ValidAction['index'][$action]['normal']) &&
                    $this->_ValidAction['index'][$action]['normal']) ||
                   (isset($this->_ValidAction['index'][$action]['standalone']) &&
                    $this->_ValidAction['index'][$action]['standalone']);
        }

        return false;
    }

    /**
     * Adds a layout action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function LayoutAction($action, $name, $description = null)
    {
        $this->AddAction($action, 'index', 'layout', $name, $description);
    }

    /**
     * Adds a standalone action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function StandaloneAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'index', 'standalone', $name, $description);
    }

    /**
     * Adds a standalone/admin action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function StandaloneAdminAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'admin', 'standalone', $name, $description);
    }

    /**
     * Verifies if action is a standalone
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is standalone, if not, returns false
     */
    function IsStandAlone($action)
    {
        if ($this->IsValidAction($action, 'index')) {
            return (isset($this->_ValidAction['index'][$action]['standalone']) &&
                    $this->_ValidAction['index'][$action]['standalone']);
        }
        return false;
    }

    /**
     * Verifies if action is a standalone of controlpanel
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is standalone of the controlpanel if not, returns false
     */
    function IsStandAloneAdmin($action)
    {
        if ($this->IsValidAction($action, 'admin')) {
            return (isset($this->_ValidAction['admin'][$action]['standalone']) &&
                    $this->_ValidAction['admin'][$action]['standalone']);
        }
        return false;
    }

    /**
     * Uses the admin of the gadget(in controlpanel)
     *
     * @access  public
     * @return  string  The text to show
     */
    function Admin()
    {
        $str = _t('GLOBAL_JG_NOADMIN');
        return $str;
    }

    /**
     * Validates if an action is valid
     *
     * @access  public
     * @param   string  $action Action to validate
     * @return  mixed   Action mode if action is valid, otherwise false
     */
    function IsValidAction($action, $script = JAWS_SCRIPT)
    {
        return isset($this->_ValidAction[$script][$action]);
    }

    /**
     * Get registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  mixed   Returns key value if exists otherwise null
     */
    function GetRegistry($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Get($name, $gadget, JAWS_COMPONENT_GADGET);
    }

    /**
     * Set registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function SetRegistry($name, $value, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Set($name, $value, $gadget, JAWS_COMPONENT_GADGET);
    }

    /**
     * Add registry key value
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $value  Key value
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function AddRegistry($name, $value = '', $gadget = '')
    {
        if (is_array($name)) {
            $gadget = empty($value)? $this->name : $value;
            return $GLOBALS['app']->Registry->NewKeyEx($name, $gadget, JAWS_COMPONENT_GADGET);
        } else {
            $gadget = empty($gadget)? $this->name : $gadget;
            return $GLOBALS['app']->Registry->NewKey($name, $value, $gadget, JAWS_COMPONENT_GADGET);
        }
    }

    /**
     * Delete registry key
     *
     * @access  public
     * @param   string  $name   Key name
     * @param   string  $gadget (Optional) Gadget name
     * @return  bool    Returns True or False
     */
    function DelRegistry($name, $gadget = '')
    {
        $gadget = empty($gadget)? $this->name : $gadget;
        return $GLOBALS['app']->Registry->Delete($name, $gadget, JAWS_COMPONENT_GADGET);
    }

    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text           The Text to parse
     * @param   string  $gadget         The Gadget name
     * @param   string  $plugins_set    Plugins set name(admin or index)
     * @param   bool    $auto_paragraph If parse text should move new lines to paragraphs
     * @param   bool    $clean          htmlentities
     * @return  string  Returns the parsed text
     */
    function ParseText($text, $gadget = null, $plugins_set = 'admin', $auto_paragraph = true, $clean = false)
    {
        $res = $text;

        // Lets clean this text up!
        if ($clean) {
            $res = htmlentities($res, ENT_QUOTES, 'UTF-8');
        }

        if (!empty($gadget)) {
            if ($plugins_set == 'admin') {
                $plugins = $GLOBALS['app']->Registry->Get('plugins_admin_enabled_items');
            } else {
                $plugins = $GLOBALS['app']->Registry->Get('plugins_enabled_items');
            }
            if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
                $plugins = array_filter(explode(',', $plugins));
                foreach ($plugins as $plugin) {
                    $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
                    if (!Jaws_Error::IsError($objPlugin)) {
                        $use_in = '*';
                        if ($plugins_set == 'admin') {
                            $use_in = $GLOBALS['app']->Registry->Get('use_in', $plugin, JAWS_COMPONENT_PLUGIN);
                        }
                        if (!Jaws_Error::isError($use_in) &&
                           ($use_in == '*' || in_array($gadget, explode(',', $use_in))))
                        {
                            $res = $objPlugin->ParseText($res);
                        }
                    }
                }
            }
        }

        if ($auto_paragraph) {
            //So we don't call require_once each time we invoke it
            if (!Jaws::classExists('Jaws_String')) {
                require JAWS_PATH . 'include/Jaws/String.php';
            }
            $res = Jaws_String::AutoParagraph($res);
        } else {
            $res = str_replace("\n\n", '<br />', $res);
        }

        return $res;
    }

    /**
     * Validate if a gadget is valid
     *
     * @access  public
     * @param   string  $gadget Gadget's Name
     * @return  bool    Returns true if the gadget is valid, otherwise will finish the execution
     */
    function IsValid($gadget)
    {
        // Check for valid gadget identificator
        if (preg_match('[^A-Za-z0-9_-]', $gadget)) {
            //Invalid gadget name
            return false;
        }

        // Check if gadget is enabled
        ///FIXME check for errors
        if ($GLOBALS['app']->Registry->Get('enabled', $gadget, JAWS_COMPONENT_GADGET) != 'true') {
            // Gadget is not found or disabled
            return false;
        }

        return true;
    }

    /**
     * Return true or false if the gadget is correctly installed
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @return  bool    True or false, depends of the gadget status
     */
    public static function IsGadgetInstalled($gadget)
    {
        $enabled_gadgets = $GLOBALS['app']->Registry->Get('gadgets_enabled_items');
        $enabled_gadgets.= $GLOBALS['app']->Registry->Get('gadgets_core_items');
        return (false !== strpos($enabled_gadgets, ",{$gadget},"));
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
                $current_version = $objGadget->GetRegistry('version');
                $gadgets_status[$gadget] = version_compare($objGadget->_Version, $current_version, '>')? false : true;
            }
        }

        return $gadgets_status[$gadget];
    }

    /**
     * Get permission on a gadget/task
     *
     * @param   string $task Task name
     * @param   string $gadget Gadget name
     * @return  bool    True if granted, else False
     */
    function GetPermission($task, $gadget = false)
    {
        return $GLOBALS['app']->Session->GetPermission(empty($gadget)? $this->name : $gadget, $task);
    }

    /**
     * Check permission on a gadget/task
     *
     * @param   string  $task           Task(s) name
     * @param   bool    $together       And/Or tasks permission result, default true
     * @param   string  $gadget         Gadget name
     * @param   string  $errorMessage   Error message to return
     * @return  mixed   True if granted, else throws an Exception(Jaws_Error::Fatal)
     */
    function CheckPermission($task, $together = true, $gadget = false, $errorMessage = '')
    {
        return $GLOBALS['app']->Session->CheckPermission(empty($gadget)? $this->name : $gadget,
                                                         $task,
                                                         $together,
                                                         $errorMessage);
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
