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
require JAWS_PATH . 'include/Jaws/Model.php';

class Jaws_Gadget
{
    /**
     * Gadget's name (FS name)
     *
     * @var     string
     * @access  protected
     * @see    GetName
     */
    var $_Name;

    /**
     * Gadget's translated name
     *
     * @var     string
     * @access  protected
     * @see    GetTranslatedName
     */
    var $_TranslatedName;

    /**
     * Gadget's version
     *
     * @var     string
     * @access  protected
     * @see    GetVersion
     */
    var $_Version;

    /**
     * Gadget's description
     *
     * @var     string
     * @access  protected
     * @see    GetDescription
     */
    var $_Description;

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
     * @see GetActions()
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
     * Constructor
     *
     * @access  protected
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    function Jaws_Gadget($gadget)
    {
        if (substr($gadget, -5, 5) == 'Model') {
            $gadget = substr($gadget, 0, strlen($gadget) - 5);
        }
        $GLOBALS['app']->Registry->LoadFile($gadget);

        if (isset($GLOBALS['app']->ACL)) {
            $GLOBALS['app']->ACL->LoadFile($gadget);
        }

        require_once JAWS_PATH . 'include/Jaws/GadgetInfo.php';
        $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
        // check for error
        if (Jaws_Error::IsError($info)) {
            Jaws_Error::Fatal("Gadget $gadget needs Info File!");
        }

        $this->_Name           = $gadget;
        $this->_TranslatedName = $info->GetName();
        $this->_Description    = $info->GetDescription();
        $this->_Version        = $info->GetVersion();
        $this->LoadActions($gadget);
    }

    /**
     * Load the gadget's action stuff files
     *
     * @access  public
     */
    function LoadActions($gadget = '')
    {
        if (!$this->_LoadedActions) {
            $this->_ValidAction = $GLOBALS['app']->GetGadgetActions($gadget);
            if (!isset($this->_ValidAction['NormalAction']['DefaultAction'])) {
                $this->_ValidAction['NormalAction']['DefaultAction'] = array('name' => 'DefaultAction',
                                                                             'mode' => 'NormalAction',
                                                                             'desc' => '',
                                                                             'file' => null);
            }

            if (!isset($this->_ValidAction['AdminAction']['Admin'])) {
                $this->_ValidAction['AdminAction']['Admin'] = array('name' => 'Admin',
                                                                    'mode' => 'AdminAction',
                                                                    'desc' => '',
                                                                    'file' => null);
            }
            $this->_LoadedActions = true;
        }
    }

    /**
     * Get the gadget's name
     *
     * @access  public
     * @return  string    Returns the gadget's name
     */
    function GetName()
    {
        return $this->_Name;
    }

    /**
     * Get the gadget's translated name
     *
     * @access  public
     * @return  string    Returns the gadget's translated name
     */
    function GetTranslatedName()
    {
        return $this->_TranslatedName;
    }

    /**
     * Get the gadget's version
     *
     * @access  public
     * @return  string    Returns the gadget's version
     */
    function GetVersion()
    {
        return $this->_Version;
    }

    /**
     * Get the gadget's description
     *
     * @access  public
     * @return  string    Returns the gadget's description
     */
    function GetDescription()
    {
        return $this->_Description;
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
            $this->_Action = $GLOBALS['app']->Registry->Get('/gadgets/' . $this->_Name . '/default_action');
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

        if (!$mode = $this->IsValidAction($action)) {
            Jaws_Error::Fatal('Invalid action: '. $action);
        }

        $file = $this->_ValidAction[$mode][$action]['file'];
        if (!empty($file)) {
            $objAction = $GLOBALS['app']->loadGadget($this->_Name,
                                                     JAWS_SCRIPT == 'index'? 'HTML' : 'AdminHTML',
                                                     $file);
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
     * @param   string  $name Action's name
     * @param   string  $mode Action's mode
     * @param   string  $description Action's description
     */
    function AddAction($action, $mode, $description, $file = null)
    {
        $this->_ValidAction[$mode][$action] = array('name' => $action,
                                                    'mode' => $mode,
                                                    'desc' => $description,
                                                    'file' => $file);
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
        $this->_ValidAction[$new_mode][$action] = array('name' => $action,
                                                        'mode' => $new_mode,
                                                        'desc' => $desc,
                                                        'file' => $file);
        unset($this->_ValidAction[$old_mode][$action]);
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
        $this->AddAction($action, 'NormalAction', $description);
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
        $this->AddAction($action, 'AdminAction', $description);
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
        if ($this->IsValidAction($action)) {
            return (isset($this->_ValidAction['AdminAction'][$action]) || 
                    isset($this->_ValidAction['StandaloneAdminAction'][$action]));
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

        if ($this->IsValidAction($action)) {
            return (isset($this->_ValidAction['NormalAction'][$action]) || 
                    isset($this->_ValidAction['StandaloneAction'][$action]));
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
    function IsValidAction($action)
    {
        if (JAWS_SCRIPT == 'index') {
            $modes = array('NormalAction', 'LayoutAction', 'StandaloneAction');
        } else {
            $modes = array('AdminAction', 'StandaloneAdminAction');
        }

        foreach($modes as $mode) {
            if (isset($this->_ValidAction[$mode][$action])) {
                return $mode;
            }
        }

        return false;
    }

    /**
     * Get a list of the available actions
     *
     * @access  public
     * @return  array   Returns an array of the available actions
     */
    function GetActions($formatted = true)
    {
        if (!$formatted) {
            return $this->_ValidAction;
        }

        $result = (isset($this->_ValidAction['LayoutAction'])) ? $this->_ValidAction['LayoutAction'] : array();
        return $result;
    }

    /**
     * Parses the input text
     *
     * @access  public
     * @param   string  $text   The Text to parse
     * @param   string  $gadget The Gadget's name
     * @param   bool    $auto_paragraph If parse text should move new lines to paragraphs
     * @return  string  Returns the parsed text
     */
    function ParseText($text, $gadget = null, $auto_paragraph = true, $clean = false)
    {
        $res = $text;

        // Lets clean this text up!
        if ($clean) {
            $res = htmlentities($res, ENT_QUOTES, 'UTF-8');
        }

        if (!empty($gadget)) {
            $plugins = $GLOBALS['app']->Registry->Get('/plugins/parse_text/enabled_items');
            if (!Jaws_Error::isError($plugins) && !empty($plugins)) {
                $plugins = array_filter(explode(',', $plugins));
                foreach ($plugins as $plugin) {
                    $objPlugin = $GLOBALS['app']->LoadPlugin($plugin);
                    if (!Jaws_Error::IsError($objPlugin)) {
                        $use_in = $GLOBALS['app']->Registry->Get('/plugins/parse_text/' . $plugin . '/use_in');
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
        if ($GLOBALS['app']->Registry->Get('/gadgets/' . $gadget . '/enabled') != 'true') {
            // Gadget is not found or disabled
            return false;
        }

        return true;
    }

    /**
     * Disables a gadget, just removing main entries from the registry
     *
     * @param   string $name Name of the gadget to disable.
     * @access  public
     */
    function DisableGadget($gadget)
    {
        // run prechecks
        if (!Jaws_Gadget::_commonPreDisableGadget($gadget)) {
            return false;
        }

        // Before anything starts
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onBeforeDisablingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        if (!Jaws_Gadget::_commonDisableGadget($gadget)) {
            return false;
        }

        if (
            $GLOBALS['app']->Registry->Get('/gadgets/' . $gadget . '/enabled') == 'true' &&
            $GLOBALS['app']->Registry->Get('/config/main_gadget') != $gadget
        ) {
            $GLOBALS['app']->Registry->Set('/gadgets/' . $gadget . '/enabled', 'false');
        }
        $GLOBALS['app']->Registry->Commit('core'); //Commit all changes to core

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('onAfterDisablingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Does a complete uninstall to the gadget, removing acl keys, registry keys,
     * tables, data, etc..
     *
     * @param   string  $gadget  Gadget's name
     * @return  bool    True true success or false on error
     * @access  public
     */
    function UninstallGadget($gadget)
    {
        // run prechecks
        if (!Jaws_Gadget::_commonPreDisableGadget($gadget)) {
            return false;
        }

        // Before anything starts
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onBeforeUninstallingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        $model = $GLOBALS['app']->loadGadget($gadget, 'AdminModel');
        if (method_exists($model, 'UninstallGadget')) {
            $res = $model->UninstallGadget();
            if (Jaws_Error::IsError($res) || !$res) {
                return $res;
            }
        }

        if (!Jaws_Gadget::_commonDisableGadget($gadget)) {
            return false;
        }

        $model->UninstallACLs();

        $GLOBALS['app']->Registry->DeleteKey('/gadgets/' . $gadget . '/enabled');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/' . $gadget . '/version');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/' . $gadget . '/requires');
        $GLOBALS['app']->Registry->Commit($gadget); //Commit all changes
        $GLOBALS['app']->Registry->Commit('core'); //Commit all changes to core

        $GLOBALS['app']->Registry->deleteCacheFile($gadget);
        $GLOBALS['app']->ACL->deleteCacheFile($gadget);

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('onAfterUninstallingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    function _commonPreDisableGadget($gadget)
    {
        if (!file_exists(JAWS_PATH . 'gadgets/' . $gadget . '/Info.php')) {
            return false;
        }

        if (
            Jaws_Gadget::IsGadgetInstalled($gadget) &&
            $GLOBALS['app']->Registry->Get('/config/main_gadget') == $gadget
        ) {
            return false;
        }

        // Check if it's a core gadget, thus can't be removed.
        ///FIXME check for errors
        $core = $GLOBALS['app']->Registry->Get('/gadgets/core_items');
        if (stristr($core, $gadget)) {
            return false;
        }

        $sql = '
            SELECT [key_name] FROM [[registry]]
            WHERE [key_name] LIKE {name} AND [key_value] LIKE {search}';
        $params = array(
            'name' => '/gadgets/%/requires',
            'search' => '%' . $gadget . '%'
        );

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if ($result > 0) {
            return false;
        }

        return true;
    }

    /**
     * Operations that both UninstallGadget
     * and DisableGadget use
     *
     * @param   string name of the gadget being uninstalled/disabled
     * @return  void
     *
     * @see UninstallGadget
     * @see DisableGadget
     *
     * @access  public
     */
    function _commonDisableGadget($gadget)
    {
        $pull = $GLOBALS['app']->Registry->Get('/gadgets/enabled_items');
        if (stristr($pull, $gadget)) {
            $pull = str_replace(',' . $gadget, '', $pull);
        }
        $GLOBALS['app']->Registry->Set('/gadgets/enabled_items', $pull);

        //Autoload stuff
        $gadgets = $GLOBALS['app']->Registry->Get('/gadgets/autoload_items');
        if (stristr($gadgets, $gadget)) {
            $gadgets = str_replace(','.$gadget, '', $gadgets);
        }
        $GLOBALS['app']->Registry->Set('/gadgets/autoload_items', $gadgets);

        //Delete the layout items
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
        $model->DeleteGadgetElements($gadget);

        $GLOBALS['app']->loadClass('Listener', 'Jaws_EventListener');
        $GLOBALS['app']->Listener->DeleteListener($gadget);

        return true;
    }

    /**
     * Does an update to the gadget, if the update of the gadget is ok then the version
     * key (in registry) will be updated
     *
     * @param   string  $gadget     Gadget's name
     * @return  bool    True if success or false on error
     * @access  public
     */
    function UpdateGadget($gadget)
    {
        $info  = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($info)) {
            return $info;
        }

        $currentVersion = $GLOBALS['app']->Registry->Get('/gadgets/'.$gadget.'/version');
        $newVersion     = $info->GetVersion();
        if (version_compare($currentVersion, $newVersion, ">=")) {
            return true;
        }

        $model = $GLOBALS['app']->loadGadget($gadget, 'AdminModel');
        if (Jaws_Error::IsError($model)) {
            return $model;
        }

        // Before anything starts
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onBeforeUpdatingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        $instance = $model->UpdateGadget($currentVersion, $newVersion);
        if (Jaws_Error::IsError($instance)) {
            return $instance;
        }

        if (!$instance) {
            return false;
        }

        if (is_string($instance)) {
            //Instance has the new version number
            $GLOBALS['app']->Registry->Set('/gadgets/'.$gadget.'/version', $instance);
        } else {
            //Use the latest (current) version
            $GLOBALS['app']->Registry->Set('/gadgets/'.$gadget.'/version', $newVersion);
        }

        // commit acl and registry keys
        if (isset($GLOBALS['app']->ACL)) {
            $GLOBALS['app']->ACL->Commit($gadget);
        }
        $GLOBALS['app']->Registry->Commit($gadget);

        //Autoload feature
        $autoloadFeature = file_exists(JAWS_PATH . 'gadgets/' . $gadget . '/Autoload.php');
        $data    = $GLOBALS['app']->Registry->Get('/gadgets/autoload_items');
        $gadgets = explode(',', $data);
        if ($autoloadFeature) {
            if (!in_array($gadget, $gadgets)) {
                $data .= ',' . $gadget;
                $GLOBALS['app']->Registry->Set('/gadgets/autoload_items', $data);
            }
        } elseif (in_array($gadget, $gadgets)) {
            if (stristr($data, $gadget)) {
                $data = str_replace(','.$gadget, '', $data);
            }
            $GLOBALS['app']->Registry->Set('/gadgets/autoload_items', $data);
        }

        // Commit all the recent core changes
        $GLOBALS['app']->Registry->Commit('core');

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('onAfterUpdatingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Enables a gadget
     *
     * @param   string  $name  Gadget's name
     * @access  public
     */
    function EnableGadget($gadget)
    {
        if (strtolower($gadget) === 'core') {
            return new Jaws_Error(_t('GLOBAL_GADGETS_GADGET_CANT_HAVE_NAME_CORE', $gadget),
                                     __FUNCTION__);
        }

        $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
        if (Jaws_Error::IsError($info)) {
            return false;
        }

        $req = $info->GetRequirements();
        if (is_array($req)) {
            foreach ($req as $r) {
                if (!Jaws_Gadget::IsGadgetInstalled($r)) {
                    return new Jaws_Error(_t('GLOBAL_GI_GADGET_REQUIRES', $r, $gadget),
                                          __FUNCTION__);
                }
            }
        }

        // Before anything starts
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onBeforeEnablingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        $core = $info->GetAttribute('core_gadget');

        $enabled = '/gadgets/' . $gadget . '/enabled';
        $status = $GLOBALS['app']->Registry->Get($enabled);
        if ($status !== null) {
            $GLOBALS['app']->Registry->Set($enabled, 'true');
        } else {
            //This can fail if user doesn't name the gadget model as: $gadgetAdminModel
            $model = $GLOBALS['app']->loadGadget($gadget, 'AdminModel');
            if (Jaws_Error::IsError($model)) {
                return false;
            }
            $instance = $model->InstallGadget();
            if (Jaws_Error::IsError($instance)) {
                return $instance;
            }

            if (!$instance) {
                return false;
            }

            // Applying the keys that every gadget gets
            $requires = implode($req, ', ');
            $GLOBALS['app']->Registry->NewKeyEx(array($enabled, 'true'),
                                                array('/gadgets/' . $gadget . '/version', $info->GetVersion()),
                                                array('/gadgets/' . $gadget . '/requires', $requires)
                                                );
            // ACL keys
            $model->InstallACLs();

            // Commit registry this late since a gadget can have no install function
            $GLOBALS['app']->Registry->Commit($gadget);
        }

        $type = ($core && !is_null($core)) ? 'core_items' : 'enabled_items';
        $items = $GLOBALS['app']->Registry->Get('/gadgets/' . $type);
        $gadgets = explode(',', $items);
        if (!in_array($gadget, $gadgets)) {
            $items .= ',' . $gadget;
            $GLOBALS['app']->Registry->Set('/gadgets/' . $type, $items);
        }

        $autoloadFeature = file_exists(JAWS_PATH . 'gadgets/' . $gadget . '/Autoload.php');
        if ($autoloadFeature) {
            $data    = $GLOBALS['app']->Registry->Get('/gadgets/autoload_items');
            $gadgets = explode(',', $data);
            if (!in_array($gadget, $gadgets)) {
                $data .= ',' . $gadget;
                $GLOBALS['app']->Registry->Set('/gadgets/autoload_items', $data);
            }
        }

        // Commit all the recent core changes with Set if any,
        // don't pass $gadget, it was commited above
        $GLOBALS['app']->Registry->Commit('core');

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('onAfterEnablingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Return true or false if the gadget is correctly installed
     *
     * @access  public
     * @return  bool    True or false, depends of the gadget status
     */
    function IsGadgetInstalled($gadget = null)
    {
        if (is_null($gadget)) {
            $gadget = $this->_Name;
        }

        ///FIXME registry get has to be checked for errors
        $items = trim($GLOBALS['app']->Registry->Get('/gadgets/enabled_items'));
        if (!empty($items) && substr($items,-1) != ',') {
            $items .= ',';
        }

        $items.= $GLOBALS['app']->Registry->Get('/gadgets/core_items');
        if (is_dir(JAWS_PATH . 'gadgets/' . $gadget) &&
            $GLOBALS['app']->Registry->Get('/gadgets/'.$gadget.'/enabled') == 'true' &&
            in_array($gadget, explode(',', $items)))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true or false if the gadget is running the version the $gadgetInfo.php says
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  bool    True or false, depends of the jaws version
     */
    function IsGadgetUpdated($gadget)
    {
        if ($GLOBALS['app']->IsGadgetMarkedAsUpdated($gadget) === null) {
            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($info)) {
                    //Jaws_Error::Fatal("Gadget $gadget needs Info File!");
                    return false;
                }

                $current_version = $GLOBALS['app']->Registry->Get('/gadgets/'.$gadget.'/version');
                $gadget_version  = $info->GetVersion();;

                //If the new gadget version is > than the current version (installed)
                $status = version_compare($gadget_version, $current_version, '>') ? false : true;
            } else {
                $status = false;
            }

            $GLOBALS['app']->SetGadgetAsUpdated($gadget, $status);
        }

        return $GLOBALS['app']->IsGadgetMarkedAsUpdated($gadget);
    }

    /**
     * Returns true or false if the gadget if the gadget is running in the required Jaws.
     *
     * If gadget doesn't have any required Jaws version to run it will return true
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @return  bool    True or false, depends of the jaws version
     */
    function CanRunInCoreVersion($gadget)
    {
        if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
            $info = $GLOBALS['app']->loadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($info)) {
                Jaws_Error::Fatal("Gadget $gadget needs Info File!");
            }

            $coreVersion     = $GLOBALS['app']->Registry->Get('/config/version');
            $requiredVersion = $Info->GetRequiredJawsVersion();

            if ($requiredVersion == $coreVersion) {
                return true;
            }

            if (version_compare($coreVersion, $requiredVersion, '>')) {
                return false;
            }

            return true;
        }

        return false;
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
        return $GLOBALS['app']->Session->GetPermission(empty($gadget)? $this->_Name : $gadget, $task);
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
        return $GLOBALS['app']->Session->CheckPermission(empty($gadget)? $this->_Name : $gadget,
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
            $name = $this->_Name;
        }
        $image = Jaws::CheckImage('gadgets/'.$name.'/images/logo.png');
        return $image;
    }
}
