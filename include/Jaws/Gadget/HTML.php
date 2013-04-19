<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category   Gadget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_HTML
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * Are we running Ajax?
     *
     * @access  private
     * @var     bool
     */
    var $_usingAjax = false;

    /**
     * A list of actions that the gadget has
     *
     * @var     array
     * @access  protected
     * @see AddAction()
     */
    var $_ValidAction = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_HTML($gadget)
    {
        $this->gadget = $gadget;
        $this->LoadActions();
        if (APP_TYPE == 'web') {
            // Add ShowGadgetInfo action
            $this->StandaloneAction('ShowGadgetInfo','');

            // Add Ajax actions.
            $this->StandaloneAction('Ajax', '');
            $this->StandaloneAdminAction('Ajax', '');

            // Add _404 as normal action
            $this->NormalAction('_404');
        }
    }

    /**
     * Load the gadget's action stuff files
     *
     * @access  public
     */
    function LoadActions()
    {
        if (empty($this->_ValidAction)) {
            $this->_ValidAction = $GLOBALS['app']->GetGadgetActions($this->gadget->name);
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
        }
    }

    /**
     * Ajax Admin stuff
     *
     * @access  public
     * @return  string  JSON encoded string
     */
    function Ajax()
    {
        if (JAWS_SCRIPT == 'admin') {
            $objAjax = $GLOBALS['app']->LoadGadget($this->gadget->name, 'AdminAjax');
        } else {
            $objAjax = $GLOBALS['app']->LoadGadget($this->gadget->name, 'Ajax');
        }

        $request =& Jaws_Request::getInstance();
        $method = $request->get('method', 'get');
        $params = $request->getAll('post');
        $output = call_user_func_array(array($objAjax, $method), $params);

        // Set Headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        return Jaws_UTF8::json_encode($output);
    }

    /**
     * Overloads Jaws_Gadget::IsValid. Difference: Checks that the gadget (HTML) file exists
     *
     * @access  public
     * @param   string  $gadget Gadget's Name
     * @return  bool    Returns true if the gadget is valid, otherwise will finish the execution
     */
    function IsValid($gadget)
    {
        // Check if file exists
        // Hack until we decide if $gadget.php will be a proxy file
        if (!file_exists(JAWS_PATH . 'gadgets/'.$gadget.'/HTML.php')) {
            Jaws_Error::Fatal('Gadget file doesn\'t exists');
        }

        parent::IsValid($gadget);
    }

    /**
     * Ajax the gadget adding the basic script links to build the interface
     *
     * @access  protected
     * @param   string  $file       Optional The gadget can require a special JS file,
     *                              it should be located under gadgets/$gadget/resources/$file
     * @param   string  $version    Optional File version
     */
    function AjaxMe($file = '', $version = '')
    {
        $this->_usingAjax = true;
        $GLOBALS['app']->Layout->AddScriptLink('libraries/mootools/core.js');
        $GLOBALS['app']->Layout->AddScriptLink('include/Jaws/Resources/Ajax.js');
        if (!empty($file)) {
            $GLOBALS['app']->Layout->AddScriptLink(
                'gadgets/'.
                $this->gadget->name.
                '/resources/'.
                $file.
                (empty($version)? '' : "?$version")
            );
        }

        $config = array(
            'DATAGRID_PAGER_FIRSTACTION' => 'javascript: firstValues(); return false;',
            'DATAGRID_PAGER_PREVACTION'  => 'javascript: previousValues(); return false;',
            'DATAGRID_PAGER_NEXTACTION'  => 'javascript: nextValues(); return false;',
            'DATAGRID_PAGER_LASTACTION'  => 'javascript: lastValues(); return false;',
            'DATAGRID_DATA_ONLOADING'    => 'showWorkingNotification;',
            'DATAGRID_DATA_ONLOADED'     => 'hideWorkingNotification;',
        );
        Piwi::addExtraConf($config);
    }

    /**
     * Return the 404 message (page not found)
     *
     * @access  protected
     */
    function _404()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        return Jaws_HTTPError::Get(404);
    }

    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     */
    function SetTitle($title)
    {
        //Set title in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->SetTitle($title);
        }
    }

    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     */
    function SetDescription($desc)
    {
        //Set description in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->SetDescription($desc);
        }
    }

    /**
     * Add keywords to meta keywords tag
     *
     * @access  public
     * @param   string  $keywords
     */
    function AddToMetaKeywords($keywords)
    {
        //Add keywords in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->AddToMetaKeywords($keywords);
        }
    }

    /**
     * Add a language to meta language tag
     *
     * @access  public
     * @param   string  $language  Language
     */
    function AddToMetaLanguages($language)
    {
        //Add language in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->AddToMetaLanguages($language);
        }
    }

    /**
     * Returns the state of usingAjax
     *
     * @access  public
     * @return  bool
     */
    function usingAjax()
    {
        return $this->_usingAjax;
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
            $this->_Action = $this->gadget->registry->get('default_action');
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
                $this->gadget->name,
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

}