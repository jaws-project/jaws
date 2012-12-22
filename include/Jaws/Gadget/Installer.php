<?php
/**
 * Jaws gadget installer class
 *
 * @category   Gadget
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
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

        // Before anything starts
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $res = $GLOBALS['app']->Shouter->Shout('onBeforeDisablingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        if (!$this->_commonDisableGadget()) {
            return false;
        }

        if (
            $this->gadget->GetRegistry('enabled') == 'true' &&
            $this->gadget->GetRegistry('main_gadget', 'Settings') != $gadget
        ) {
            $$this->gadget->SetRegistry('enabled', 'false');
        }
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
    function UninstallGadget()
    {
        // run prechecks
        $gadget = $this->gadget->name;
        if (!$this->_commonPreDisableGadget()) {
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

        if (!$this->_commonDisableGadget()) {
            return false;
        }

        $model->UninstallACLs();

        $this->gadget->DelRegistry('enabled');
        $this->gadget->DelRegistry('version');
        $this->gadget->DelRegistry('requires');

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('onAfterUninstallingGadget', $gadget);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
        }

        return true;
    }

    function _commonPreDisableGadget()
    {
        if (self::IsGadgetInstalled($this->gadget->name) &&
            $this->gadget->GetRegistry('main_gadget', 'Settings') == $this->gadget->name
        ) {
            return false;
        }

        // Check if it's a core gadget, thus can't be removed.
        ///FIXME check for errors
        $core = $GLOBALS['app']->Registry->Get('gadgets_core_items');
        if (stristr($core, $this->gadget->name)) {
            return false;
        }

        $params = array();
        $params['name']  = 'requires';
        $params['value'] = '%'. $this->gadget->name. '%';

        $sql = '
            SELECT
                [key_name]
            FROM [[registry]]
            WHERE
                [key_name] = {name}
              AND
                [key_value] LIKE {value}';

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return empty($result);
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
    function _commonDisableGadget()
    {
        $gadget = $this->gadget->name;
        $pull = $GLOBALS['app']->Registry->Get('gadgets_enabled_items');
        if (stristr($pull, $gadget)) {
            $pull = str_replace(',' . $gadget, '', $pull);
        }
        $GLOBALS['app']->Registry->Set('gadgets_enabled_items', $pull);

        //Autoload stuff
        $gadgets = $GLOBALS['app']->Registry->Get('gadgets_autoload_items');
        if (stristr($gadgets, $gadget)) {
            $gadgets = str_replace(','.$gadget, '', $gadgets);
        }
        $GLOBALS['app']->Registry->Set('gadgets_autoload_items', $gadgets);

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
     * @return  bool    True if success or false on error
     * @access  public
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

        // begin upgrade gadget event
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $result = $GLOBALS['app']->Shouter->Shout('Begin_UpgradeGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
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
        $result = $GLOBALS['app']->Shouter->Shout('End_UpgradeGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $this->gadget;
    }

    /**
     * Installs a gadget
     *
     * @access  public
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

        // begin install gadget event
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        $result = $GLOBALS['app']->Shouter->Shout('Begin_InstallGadget', $this->gadget->name);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $installer->Install();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Applying the keys that every gadget gets
        $requires = implode($this->gadget->_Requires, ',');
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

        $type = $this->gadget->_IsCore ? 'core_items' : 'enabled_items';
        $items = $GLOBALS['app']->Registry->Get('gadgets_' . $type);
        $gadgets = explode(',', $items);
        if (!in_array($this->gadget->name, $gadgets)) {
            $items .= ',' . $this->gadget->name;
            $GLOBALS['app']->Registry->Set('gadgets_' . $type, $items);
        }

        $autoloadFeature = file_exists(JAWS_PATH . 'gadgets/' . $this->gadget->name . '/Autoload.php');
        if ($autoloadFeature) {
            $data    = $GLOBALS['app']->Registry->Get('gadgets_autoload_items');
            $gadgets = explode(',', $data);
            if (!in_array($this->gadget->name, $gadgets)) {
                $data .= ',' . $this->gadget->name;
                $GLOBALS['app']->Registry->Set('gadgets_autoload_items', $data);
            }
        }

        // After anything finished
        $res = $GLOBALS['app']->Shouter->Shout('End_InstallGadget', $this->gadget->name);
        if (Jaws_Error::IsError($res) || !$res) {
            return $res;
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
     * Performs any actions required to finish installing a gadget.
     * Gadgets should override this method only if they need to perform actions to install.
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
     * Updates the gadget
     * Gadgets should override this method only if they need to perform actions to update
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function Upgrade()
    {
        return true;
    }

}