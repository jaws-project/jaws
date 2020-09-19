<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Action extends Jaws_Gadget_Class
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        parent::__construct($gadget);

        // fetch gadget actions
        $this->fetchAll();
    }


    /**
     * Loads the gadget action file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $filename   Action class file name
     * @return  mixed   Action class object on successful, Jaws_Error otherwise
     */
    public function &load($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);
        if (empty($filename)) {
            return $this;
        }

        if (!isset($this->gadget->objects['Actions'][$filename])) {
            $classname = $this->gadget->name. "_Actions_$filename";
            $file = ROOT_JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Actions/$filename.php";
            if (!file_exists($file)) {
                $classname = "Jaws_Gadget_Actions_$filename";
                $file = ROOT_JAWS_PATH. "include/Jaws/Gadget/Actions/$filename.php";
                if (!file_exists($file)) {
                    return Jaws_Error::raiseError(
                        "Actions filename [$filename] not exists!",
                        __FUNCTION__,
                        JAWS_ERROR_ERROR,
                        1
                    );
                }
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError(
                    "Class [$classname] not exists!",
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    1
                );
            }

            $this->gadget->objects['Actions'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget action class: [$classname]");
        }

        return $this->gadget->objects['Actions'][$filename];
    }


    /**
     * Loads the gadget admin action file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $filename   Action class file name
     * @return  mixed   Action class object on successful, Jaws_Error otherwise
     */
    public function &loadAdmin($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);
        if (empty($filename)) {
            return $this;
        }

        if (!isset($this->gadget->objects['AdminActions'][$filename])) {
            $classname = $this->gadget->name. "_Actions_Admin_$filename";
            $file = ROOT_JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Actions/Admin/$filename.php";

            if (!file_exists($file)) {
                return Jaws_Error::raiseError(
                    "File [$file] not exists!",
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    1
                );
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError(
                    "Class [$classname] not exists!",
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    1
                );
            }

            $this->gadget->objects['AdminActions'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget action class: [$classname]");
        }

        return $this->gadget->objects['AdminActions'][$filename];
    }


    /**
     * fetches all actions of gadget
     *
     * @access  public
     * @param   string  $script Action belongs to index or admin
     * @return  array   Actions of gadget
     */
    public function fetchAll($script = '')
    {
        if (empty($this->gadget->actions)) {
            $file = ROOT_JAWS_PATH . 'gadgets/'. $this->gadget->name. '/Actions.php';
            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }
            include_once($file);
            if (isset($actions) && !empty($actions)) {
                $this->gadget->actions['index'] = $actions;
            } else {
                $this->gadget->actions['index'] = array();
            }

            if (isset($admin_actions) && !empty($admin_actions)) {
                $this->gadget->actions['admin'] = $admin_actions;
            } else {
                $this->gadget->actions['admin'] = array();
            }
        }

        return empty($script)? $this->gadget->actions : $this->gadget->actions[$script];
    }


    /**
     * Ajax the gadget adding the basic script links to build the interface
     *
     * @access  protected
     * @param   string  $file   Optional The gadget can require a special JS file,
     *                          it should be located under gadgets/$gadget/Resources/$file
     * @return  void
     */
    public function AjaxMe($file = '')
    {
        if (!empty($file)) {
            $this->app->define($this->gadget->name, false);
            $this->app->layout->addScript(
                'gadgets/'.$this->gadget->name.'/Resources/'. $file.'?'.$this->gadget->version
            );
        }

        $config = array(
            'DATAGRID_PAGER_FIRSTACTION' => 'javascript: firstValues(); return false;',
            'DATAGRID_PAGER_PREVACTION'  => 'javascript: previousValues(); return false;',
            'DATAGRID_PAGER_NEXTACTION'  => 'javascript: nextValues(); return false;',
            'DATAGRID_PAGER_LASTACTION'  => 'javascript: lastValues(); return false;',
        );
        Piwi::addExtraConf($config);
    }


    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     * @return  void
     */
    public function SetTitle($title)
    {
        //Set title in case we are no running on standalone..
        if (isset($this->app->layout)) {
            $this->app->layout->SetTitle($title);
        }
    }


    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     * @return  void
     */
    public function SetDescription($desc)
    {
        //Set description in case we are no running on standalone..
        if (isset($this->app->layout)) {
            $this->app->layout->SetDescription($desc);
        }
    }


    /**
     * Add keywords to meta keywords tag
     *
     * @access  public
     * @param   string  $keywords
     * @return  void
     */
    public function AddToMetaKeywords($keywords)
    {
        //Add keywords in case we are no running on standalone..
        if (isset($this->app->layout)) {
            $this->app->layout->AddToMetaKeywords($keywords);
        }
    }


    /**
     * Add a language to meta language tag
     *
     * @access  public
     * @param   string  $language  Language
     * @return  void
     */
    public function AddToMetaLanguages($language)
    {
        //Add language in case we are no running on standalone..
        if (isset($this->app->layout)) {
            $this->app->layout->AddToMetaLanguages($language);
        }
    }


    /**
     * Execute the action
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   array   $params     Action parameters array
     * @param   string  $action     Layout section name
     * @param   string  $action     Action execute mode(normal, layout, standalone)
     * @return  mixed   Actions output on success otherwise Jaws_Error on failure
     */
    public function Execute($action, $params = null, $section = '', $mode = ACTION_MODE_NORMAL)
    {
        if (false === $action) {
            return Jaws_Error::raiseError(Jaws::t('ACTION_NO_DEFAULT'), __FUNCTION__);
        }

        if (!$this->app->session->GetPermission(
                $this->gadget->name,
                JAWS_SCRIPT == 'index'? 'default' : 'default_admin'
            )
        ) {
            return Jaws_HTTPError::Get($this->app->session->user->logged? 403 : 401);
        }

        if (!$this->IsValidAction($action)) {
            return Jaws_Error::raiseError(
                'Invalid action '.$this->gadget->name.'::'.$action,
                __FUNCTION__,
                JAWS_ERROR_ERROR,
                1
            );
        }

        // check predefine permissions
        if (isset($this->gadget->actions[JAWS_SCRIPT][$action]['acls'])) {
            if (!call_user_func_array(
                    array($this->gadget, 'GetPermission'),
                    $this->gadget->actions[JAWS_SCRIPT][$action]['acls']
                )
            ) {
                return Jaws_HTTPError::Get($this->app->session->user->logged? 403 : 401);
            }
        }

        if (isset($this->app->layout)) {
            $title = strtoupper($this->gadget->name.'_ACTIONS_'.$action.'_TITLE');
            $description = strtoupper($this->gadget->name.'_ACTIONS_'.$action.'_DESC');
            $title = (_t($title) == $title)? '' : _t($title);
            $description = (_t($description) == $description)? '' : _t($description);
            $this->app->layout->SetTitle($title);
            $this->app->layout->SetDescription($description);
        }

        $file = $this->gadget->actions[JAWS_SCRIPT][$action]['file'];
        if (JAWS_SCRIPT == 'index') {
            $objAction = $this->gadget->action->load($file);
        } else {
            $objAction = $this->gadget->action->loadAdmin($file);
        }
        if (Jaws_Error::isError($objAction)) {
            return $objAction;
        }

        if (method_exists($objAction, $action)) {
            $this->app->define($this->gadget->name, false);
            $this->gadget->loaded_actions[$action] = true;
            $this->app->requestedGadget  = $this->gadget->name;
            $this->app->requestedAction  = $action;
            $this->app->requestedSection = $section;
            $this->app->requestedActionMode = $mode;
            if (is_null($params)) {
                return $objAction->$action();
            } else {
                return call_user_func_array(array($objAction, $action), $params);
            }
        }

        return Jaws_Error::raiseError(
            'Action '.$this->gadget->name.'::'.$action. ' does not exist.',
            __FUNCTION__
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
     * @param   string  $file       Action's filename
     * @return  void
     */
    public function SetActionMode($action, $new_mode, $old_mode, $desc = null, $file = null)
    {
        $this->gadget->actions[JAWS_SCRIPT][$action] = array(
            'name' => $action,
            $new_mode => true,
            $old_mode => false,
            'desc' => $desc,
            'file' => $file
        );
    }


    /**
     * Verifies if action is a standalone
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is standalone, if not, returns false
     */
    public function IsStandAlone($action)
    {
        if ($this->IsValidAction($action, JAWS_SCRIPT)) {
            return (isset($this->gadget->actions[JAWS_SCRIPT][$action]['standalone']) &&
                    $this->gadget->actions[JAWS_SCRIPT][$action]['standalone']);
        }

        return false;
    }

    /**
     * Get action attributes
     *
     * @access  public
     * @param   string  $action Action name
     * @param   string  $script     Action belongs to index or admin
     * @return  mixed   Action attribute value otherwise NULL
     */
    public function getAttributes($action, $script = JAWS_SCRIPT)
    {
        return (array)@$this->gadget->actions[$script][$action];
    }

    /**
     * Get action attribute
     *
     * @access  public
     * @param   string  $action Action name
     * @param   string  $attr   Attribute name
     * @return  mixed   Action attribute value otherwise NULL
     */
    public function getAttribute($action, $attr)
    {
        return @$this->gadget->actions[JAWS_SCRIPT][$action][$attr];
    }


    /**
     * Set action attribute value
     *
     * @access  public
     * @param   string  $action Action name
     * @param   string  $attr   Attribute name
     * @param   mixed   $value  Attribute value
     * @return  void
     */
    public function setAttribute($action, $attr, $value)
    {
        $this->gadget->actions[JAWS_SCRIPT][$action][$attr] = $value;
    }


    /**
     * Validates if an action is valid
     *
     * @access  public
     * @param   string  $action     Action to validate
     * @param   string  $script     Action belongs to index or admin
     * @return  mixed   Action mode if action is valid, otherwise false
     */
    public function IsValidAction($action, $script = JAWS_SCRIPT)
    {
        return isset($this->gadget->actions[$script][$action]);
    }


    /**
     * Filter non validate character
     *
     * @access  public
     * @param   string  $action     Action name
     * @return  string  Filtered action name
     */
    public static function filter($action)
    {
        return preg_replace('/[^[:alnum:]_]/', '', @(string)$action);
    }

}