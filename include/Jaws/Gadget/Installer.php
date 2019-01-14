<?php
/**
 * Jaws gadget installer class
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Installer
{
    /**
     * Default ACL value of front-end gadget access
     *
     * @var     bool
     * @access  public
     */
    public $default_acl = true;

    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  public
     */
    public $_RegKeys = array();

    /**
     * Gadget ACL keys
     *
     * @var     array
     * @access  public
     */
    public $_ACLKeys = array();

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the Installer class object
     *
     * @access  public
     * @return  object  Installer class object
     */
    public function &load()
    {
        return $this;
    }

    /**
     * Loads the gadget model file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    public function &loadInstaller()
    {
        $classname = $this->gadget->name. '_Installer';
        $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/Installer.php';
        if (!file_exists($file)) {
            return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
        }

        include_once($file);
        if (!Jaws::classExists($classname)) {
            return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
        }

        $objInstaller = new $classname($this->gadget);
        return $objInstaller;
    }

    /**
     * Get all ACLs for the gadget
     *
     * @access  public
     * @return  array   ACLs of the gadget
     */
    public function GetACLs()
    {
        $result = array();
        foreach ($this->_ACLKeys as $acl) {
            if (is_array($acl)) {
                $result[] = array($acl[0], $acl[1], (int)$acl[2]);
            } else {
                $result[] = array($acl, '', 0);
            }
        }

        // Adding common ACL keys
        $result[] = array('default', '', (int)$this->default_acl);
        $result[] = array('default_admin', '', 0);
        $result[] = array('default_registry', '', 0);
        return $result;
    }

    /**
     * Install a gadget
     *
     * @access  public
     * @param   string  $input_schema       Schema file path
     * @param   array   $input_variables    Schema variables
     * @return  mixed   True if success or Jaws_Error on error
     */
    public function InstallGadget($input_schema = '', $input_variables = array())
    {
        if (Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return true;
        }

        $installer = $this->loadInstaller();
        if (Jaws_Error::IsError($installer)) {
            return $installer;
        }

        // all required gadgets, must be installed
        foreach ($this->gadget->requirement as $req) {
            if (!Jaws_Gadget::IsGadgetInstalled($req)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_GI_GADGET_REQUIRES', $req, $this->gadget->name),
                    __FUNCTION__
                );
            }
        }

        // Registry keys
        $requirement = ','. implode($this->gadget->requirement, ','). ',';
        $recommended = ','. implode($this->gadget->recommended, ','). ',';
        $installer->_RegKeys = array_merge(
            array(
                array('version', $this->gadget->version),
                array('requirement', $requirement),
                array('recommended', $recommended),
            ),
            $installer->_RegKeys
        );
        $this->gadget->registry->insertAll($installer->_RegKeys, $this->gadget->name);

        // load gadget install method
        if (method_exists($installer, 'Install')) {
            $result = $installer->Install($input_schema, $input_variables);
            if (Jaws_Error::IsError($result)) {
                // removing gadget registry keys
                $GLOBALS['app']->Registry->delete($this->gadget->name);
                return $result;
            }
        }

        // ACL keys
        $this->gadget->acl->insert($installer->GetACLs(), $this->gadget->name);

        // adding gadget to installed gadgets list
        $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
        $installed_gadgets.= $this->gadget->name. ',';
        $GLOBALS['app']->Registry->update('gadgets_installed_items', $installed_gadgets);

        // adding gadget to auto-load gadgets list
        if (file_exists(JAWS_PATH . "gadgets/{$this->gadget->name}/Hooks/Autoload.php")) {
            $autoload_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_autoload_items');
            $autoload_gadgets.= $this->gadget->name. ',';
            $GLOBALS['app']->Registry->update('gadgets_autoload_items', $autoload_gadgets);
        }

        // end install gadget event
        $res = $this->gadget->event->shout('InstallGadget', $this->gadget->name);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Uninstall a gadget
     * Does a complete uninstall to the gadget, removing ACL keys, registry keys, tables, data, etc..
     *
     * @access  public
     * @return  mixed    True if success or Jaws_Error on error
     */
    public function UninstallGadget()
    {
        if (!Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return Jaws_Error::raiseError(
                "gadget [{$this->gadget->name}] not installed",
                __FUNCTION__
            );
        }

        if ($this->gadget->_IsCore) {
            return Jaws_Error::raiseError(
                "you can't uninstall core gadgets",
                __FUNCTION__
            );
        }

        $gModel = $this->gadget->model->load();
        $dependent_gadgets = $gModel->requirementfor();
        if (!empty($dependent_gadgets)) {
            if (Jaws_Error::IsError($dependent_gadgets)) {
                return $dependent_gadgets;
            }

            $dependent_gadgets = implode(', ', $dependent_gadgets);
            return Jaws_Error::raiseError(
                "you can't uninstall this gadget, because $dependent_gadgets gadget(s) is dependent on it",
                __FUNCTION__
            );
        }

        $installer = $this->loadInstaller();
        if (Jaws_Error::IsError($installer)) {
            return $installer;
        }

        if (method_exists($installer, 'Uninstall')) {
            $result = $installer->Uninstall();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // removing gadget from installed gadgets list
        $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
        $installed_gadgets = str_replace(",{$this->gadget->name},", ',', $installed_gadgets);
        $GLOBALS['app']->Registry->update('gadgets_installed_items', $installed_gadgets);

        // removing gadget from auto-load gadgets list
        $autoload_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_autoload_items');
        $autoload_gadgets = str_replace(",{$this->gadget->name},", ',', $autoload_gadgets);
        $GLOBALS['app']->Registry->update('gadgets_autoload_items', $autoload_gadgets);

        // removing gadget listeners
        $this->gadget->event->delete();
        // removing gadget ACL keys
        $GLOBALS['app']->ACL->delete($this->gadget->name);
        // removing gadget registry keys
        $GLOBALS['app']->Registry->delete($this->gadget->name);

        // end uninstall gadget event
        $result = $this->gadget->event->shout('UninstallGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Upgrade a gadget
     * Does an update to the gadget, if the update of the gadget is ok then the version
     * key (in registry) will be updated
     *
     * @access  public
     * @return  mixed    True if success or Jaws_Error on error
     */
    public function UpgradeGadget()
    {
        $oldVersion = $this->gadget->registry->fetch('version', $this->gadget->name);
        $newVersion = $this->gadget->version;
        if (version_compare($oldVersion, $newVersion, ">=")) {
            return true;
        }

        $installer = $this->loadInstaller();
        if (Jaws_Error::IsError($installer)) {
            return $installer;
        }

        if (method_exists($installer, 'Upgrade')) {
            $result = $installer->Upgrade($oldVersion, $newVersion);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (is_string($result)) {
            // set return the new version number
            $this->gadget->registry->update('version', $result);
        } else {
            // set the latest version number
            $this->gadget->registry->update('version', $newVersion);
        }

        // auto-load feature
        $autoload_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_autoload_items');
        if (file_exists(JAWS_PATH . "gadgets/{$this->gadget->name}/Hooks/Autoload.php")) {
            if (false === strpos($autoload_gadgets, ",{$this->gadget->name},")) {
                $autoload_gadgets.= $this->gadget->name. ',';
                $GLOBALS['app']->Registry->update('gadgets_autoload_items', $autoload_gadgets);
            }
        } else {
            $autoload_gadgets = str_replace(",{$this->gadget->name},", ',', $autoload_gadgets);
            $GLOBALS['app']->Registry->update('gadgets_autoload_items', $autoload_gadgets);
        }

        // end upgrade gadget event
        $result = $this->gadget->event->shout('UpgradeGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Enable a gadget
     *
     * @access  public
     * @return  mixed    True if success or Jaws_Error on error
     */
    public function EnableGadget()
    {
        if (!Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return Jaws_Error::raiseError(
                "gadget [{$this->gadget->name}] not installed",
                __FUNCTION__
            );
        }

        // all required gadgets, must be enabled
        foreach ($this->gadget->requirement as $req) {
            if (!Jaws_Gadget::IsGadgetEnabled($req)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_GI_GADGET_REQUIRES', $req, $this->gadget->name),
                    __FUNCTION__
                );
            }
        }

        // removing gadget from disabled gadgets list
        $disabled_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_disabled_items');
        $disabled_gadgets = str_replace(",{$this->gadget->name},", ',', $disabled_gadgets);
        $GLOBALS['app']->Registry->update('gadgets_disabled_items', $disabled_gadgets);

        // end disable gadget event
        $res = $this->gadget->event->shout('EnableGadget', $this->gadget->name);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Disable a gadget
     *
     * @access  public
     * @return  mixed    True if success or Jaws_Error on error
     */
    public function DisableGadget()
    {
        if (!Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return Jaws_Error::raiseError(
                "gadget [{$this->gadget->name}] not installed",
                __FUNCTION__
            );
        }

        if ($this->gadget->_IsCore) {
            return Jaws_Error::raiseError(
                "you can't disable core gadgets",
                __FUNCTION__
            );
        }

        // check depend on gadgets status
        $gModel = $this->gadget->model->load();
        $dependent_gadgets = $gModel->requirementfor();
        if (Jaws_Error::IsError($dependent_gadgets)) {
            return $dependent_gadgets;
        }
        foreach ($dependent_gadgets as $idx => $gadget) {
            if (!Jaws_Gadget::IsGadgetEnabled($gadget)) {
                $dependent_gadgets[$idx] = null;
            }
        }
        $dependent_gadgets = implode(', ', array_filter($dependent_gadgets));
        if (!empty($dependent_gadgets)) {
            return Jaws_Error::raiseError(
                "you can't disable this gadget, because $dependent_gadgets gadget(s) is dependent on it",
                __FUNCTION__
            );
        }

        // adding gadget to disabled gadgets list
        $disabled_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_disabled_items');
        $disabled_gadgets.= $this->gadget->name. ',';
        $GLOBALS['app']->Registry->update('gadgets_disabled_items', $disabled_gadgets);

        // end disable gadget event
        $res = $this->gadget->event->shout('DisableGadget', $this->gadget->name);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Returns true or false if the gadget if the gadget is running in the required Jaws.
     *
     * If gadget doesn't have any required Jaws version to run it will return true
     *
     * @access  public
     * @return  bool    True or false, depends of the jaws version
     */
    public function CanRunInCoreVersion()
    {
        if (self::IsGadgetInstalled($this->gadget->name)) {
            $coreVersion     = $GLOBALS['app']->Registry->fetch('version');
            $requiredVersion = $this->gadget->GetRequiredJawsVersion();

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
     * Installs table(s)/data schema into database
     *
     * @access  public
     * @param   string  $new_schema     New schema file path/name
     * @param   array   $variables      Schema variables
     * @param   string  $old_schema     Old schema file path/name
     * @param   string  $init_data      Schema is include initialization data
     * @return  mixed   True on success and Jaws_Error on failure
     */
    public function InstallSchema($new_schema, $variables = array(), $old_schema = false, $init_data = false)
    {
        $main_file = $new_schema;
        if (!preg_match('@\\\\|/@', $new_schema)) {
            $main_file = JAWS_PATH. "gadgets/{$this->gadget->name}/Resources/schema/$new_schema";
        }
        if (!file_exists($main_file)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $new_schema),
                __FUNCTION__,
                JAWS_ERROR_ERROR,
                1
            );
        }

        $base_file = false;
        if (!empty($old_schema)) {
                $base_file = $old_schema;
            if (!preg_match('@\\\\|/@', $old_schema)) {
                $base_file = JAWS_PATH. "gadgets/{$this->gadget->name}/Resources/schema/$old_schema";
            }
            if (!file_exists($base_file)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $old_schema),
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    1
                );
            }
        }

        $result = Jaws_DB::getInstance()->installSchema($main_file, $variables, $base_file, $init_data);
        if (Jaws_Error::IsError($result)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_FAILED_QUERY_FILE',$new_schema . (empty($old_schema)? '': "/$old_schema")),
                __FUNCTION__,
                JAWS_ERROR_ERROR,
                1
            );
        }

        return true;
    }

}