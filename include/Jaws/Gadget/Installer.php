<?php
/**
 * Jaws gadget installer class
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Installer
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Installer($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the gadget model file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $type   Model type
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    function &loadInstaller()
    {
        if (!isset($this->gadget->installer)) {
            $installer_class_name = $this->gadget->name. '_Installer';
            $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/Installer.php';

            if (!@include_once($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            if (!Jaws::classExists($installer_class_name)) {
                return Jaws_Error::raiseError("Class [$installer_class_name] not exists!", __FUNCTION__);
            }

            $this->gadget->installer = new $installer_class_name($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget installer: [$installer_class_name]");
        }

        return $this->gadget->installer;
    }

    /**
     * Disables a gadget, just removing main entries from the registry
     *
     * @param   string $name Name of the gadget to disable.
     * @access  public
     */
    function DisableGadget()
    {
        // run prechecks
        $gadget = $this->gadget->name;
        if (!$this->_commonPreDisableGadget()) {
            return false;
        }

        // if (!$this->_commonDisableGadget()) {
            // return false;
        // }

        if (
            $this->gadget->GetRegistry('enabled') == 'true' &&
            $this->gadget->GetRegistry('main_gadget', 'Settings') != $gadget
        ) {
            $$this->gadget->SetRegistry('enabled', 'false');
        }
        // After anything finished
        $res = $GLOBALS['app']->Listener->Shout('DisableGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    /**
     * Gets gadgets that depend on a given gadget
     *
     * @access  public
     * @return  mixed   Array of gadgets otherwise Jaws_Error
     */
    function dependOnGadgets()
    {
        $params = array();
        $params['name']  = 'requires';
        $params['value'] = '%,'. $this->gadget->name. ',%';

        $sql = '
            SELECT
                [component_name]
            FROM [[registry]]
            WHERE
                [key_name] = {name}
              AND
                [key_value] LIKE {value}';

        $requires = $GLOBALS['db']->queryCol($sql, $params);
        return $requires;
    }

    /**
     * Install a gadget
     *
     * @access  public
     * @return  mixed    Gadget name if success or Jaws_Error on error
     */
    function InstallGadget()
    {
        if (Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return $this->gadget;
        }

        $installer = $this->loadInstaller();
        if (Jaws_Error::IsError($installer)) {
            return $installer;
        }

        // all required gadgets, must be installed
        foreach ($this->gadget->_Requires as $req) {
            if (!Jaws_Gadget::IsGadgetInstalled($req)) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_GI_GADGET_REQUIRES', $req, $this->gadget->name),
                    __FUNCTION__
                );
            }
        }

        $result = $installer->Install();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Applying the keys that every gadget gets
        $requires = ','. implode($this->gadget->_Requires, ','). ',';
        $this->gadget->AddRegistry(
            array(
                'enabled'  => 'true',
                'version'  => $this->gadget->_Version,
                'requires' => $requires,
            )
        );

        // ACL keys
        $gModel = $GLOBALS['app']->LoadGadget($this->gadget->name, 'AdminModel');
        $gModel->InstallACLs();

        // adding gadget to installed gadgets list
        $installed_gadgets = $GLOBALS['app']->Registry->Get('gadgets_installed_items');
        $installed_gadgets.= $this->gadget->name. ',';
        $GLOBALS['app']->Registry->Set('gadgets_installed_items', $installed_gadgets);

        // adding gadget to autoload gadgets list
        if (file_exists(JAWS_PATH . "gadgets/{$this->gadget->name}/Autoload.php")) {
            $autoload_gadgets = $GLOBALS['app']->Registry->Get('gadgets_autoload_items');
            $autoload_gadgets.= $this->gadget->name. ',';
            $GLOBALS['app']->Registry->Set('gadgets_autoload_items', $autoload_gadgets);
        }

        // end install gadget event
        $res = $GLOBALS['app']->Listener->Shout('InstallGadget', $this->gadget->name);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return $this->gadget;
    }

    /**
     * Uninstall a gadget
     * Does a complete uninstall to the gadget, removing acl keys, registry keys, tables, data, etc..
     *
     * @access  public
     * @return  mixed    True if success or Jaws_Error on error
     */
    function UninstallGadget()
    {
        if (!Jaws_Gadget::IsGadgetInstalled($this->gadget->name)) {
            return Jaws_Error::raiseError(
                "gadget [{$this->gadget->name}] not installed",
                __FUNCTION__
            );
        }

        if ($this->gadget->GetRegistry('main_gadget', 'Settings') == $this->gadget->name) {
            return Jaws_Error::raiseError(
                "you can't uninstall main gadget",
                __FUNCTION__
            );
        }

        if ($this->gadget->_IsCore) {
            return Jaws_Error::raiseError(
                "you can't uninstall core gadgets",
                __FUNCTION__
            );
        }

        $dependent_gadgets = $this->dependOnGadgets();
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

        $result = $installer->Uninstall();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // removeing gadget from installed gadgets list
        $installed_gadgets = $GLOBALS['app']->Registry->Get('gadgets_installed_items');
        $installed_gadgets = str_replace(",{$this->gadget->name},", ',', $installed_gadgets);
        $GLOBALS['app']->Registry->Set('gadgets_installed_items', $installed_gadgets);

        // removeing gadget from autoload gadgets list
        $autoload_gadgets = $GLOBALS['app']->Registry->Get('gadgets_autoload_items');
        $autoload_gadgets = str_replace(",{$this->gadget->name},", ',', $autoload_gadgets);
        $GLOBALS['app']->Registry->Set('gadgets_autoload_items', $autoload_gadgets);

        // removeing gadget listeners
        $GLOBALS['app']->Listener->DeleteListener($this->gadget->name);

        // removeing gadget registry keys
        $GLOBALS['app']->Registry->Delete($this->gadget->name, '', JAWS_COMPONENT_GADGET);

        // end uninstall gadget event
        $result = $GLOBALS['app']->Listener->Shout('UninstallGadget', $this->gadget->name);
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
     * @return  mixed    Gadget name if success or Jaws_Error on error
     */
    function UpgradeGadget()
    {
        $oldVersion = $this->gadget->GetRegistry('version', $this->gadget->name);
        $newVersion = $this->gadget->_Version;
        if (version_compare($oldVersion, $newVersion, ">=")) {
            return $this->gadget;
        }

        $installer = $this->loadInstaller();
        if (Jaws_Error::IsError($installer)) {
            return $installer;
        }

        $result = $installer->Upgrade($oldVersion, $newVersion);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (is_string($result)) {
            // set return the new version number
            $this->gadget->SetRegistry('version', $result);
        } else {
            // set the latest version number
            $this->gadget->SetRegistry('version', $newVersion);
        }

        // autoload feature
        $autoload_gadgets = explode(',', $GLOBALS['app']->Registry->Get('gadgets_autoload_items'));
        $autoload_gadgets = array_filter(array_map('trim', $autoload_gadgets));
        if (file_exists(JAWS_PATH. 'gadgets/'. $this->gadget->name. '/Autoload.php')) {
            if (!in_array($this->gadget->name, $autoload_gadgets)) {
                array_push($autoload_gadgets, $this->gadget->name);
                $GLOBALS['app']->Registry->Set('gadgets_autoload_items', implode(',', $autoload_gadgets));
            }
        } else {
            if (false !== $indx = array_search($this->gadget->name, $autoload_gadgets)) {
                unset($autoload_gadgets[$indx]);
                $GLOBALS['app']->Registry->Set('gadgets_autoload_items', implode(',', $autoload_gadgets));
            }
        }

        // end upgrade gadget event
        $result = $GLOBALS['app']->Listener->Shout('UpgradeGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $this->gadget;
    }

    /**
     * Returns true or false if the gadget if the gadget is running in the required Jaws.
     *
     * If gadget doesn't have any required Jaws version to run it will return true
     *
     * @access  public
     * @return  bool    True or false, depends of the jaws version
     */
    function CanRunInCoreVersion()
    {
        if (self::IsGadgetInstalled($this->gadget->name)) {
            $coreVersion     = $GLOBALS['app']->Registry->Get('version');
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
     * @access  public
     */
    function InstallSchema($main_schema, $variables = array(), $base_schema = false, $data = false, $create = true, $debug = false)
    {
        $main_file = JAWS_PATH . 'gadgets/'. $this->gadget->name . '/schema/' . $main_schema;
        if (!file_exists($main_file)) {
            return new Jaws_Error (_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $main_schema),
                                   $this->gadget->name,
                                   JAWS_ERROR_ERROR,
                                   1);
        }

        $base_file = false;
        if (!empty($base_schema)) {
            $base_file = JAWS_PATH . 'gadgets/'. $this->gadget->name . '/schema/' . $base_schema;
            if (!file_exists($base_file)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $base_schema),
                                      $this->gadget->name,
                                      JAWS_ERROR_ERROR,
                                      1);
            }
        }

        $result = $GLOBALS['db']->installSchema($main_file, $variables, $base_file, $data, $create, $debug);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_QUERY_FILE',
                                     $main_schema . (empty($base_schema)? '': "/$base_schema")),
                                  $this->gadget->GetAttribute('Name'),
                                  JAWS_ERROR_ERROR,
                                  1);
        }

        return true;
    }

    /**
     * Install the gadget
     * Gadgets should override this method only if they need to perform actions to install
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function Install()
    {
        return true;
    }

    /**
     * Uninstall the gadget
     * Gadgets should override this method only if they need to perform actions to uninstall
     *
     * @access  public
     * @return  mixed    True on a successful install and Jaws_Error otherwise
     */
    function Uninstall()
    {
        return true;
    }

    /**
     * Upgrade the gadget
     * Gadgets should override this method only if they need to perform actions to upgrade
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function Upgrade()
    {
        return true;
    }

}