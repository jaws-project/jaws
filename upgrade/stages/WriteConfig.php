<?php
/**
 * Write configuration stuff Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_WriteConfig extends JawsUpgraderStage
{
    /**
     * Sets up a JawsConfig
     *
     * @access public
     * @return string
     */
    function BuildConfig()
    {
        include_once JAWS_PATH . 'include/Jaws/Template.php';
        $tpl = new Jaws_Template(false);
        $tpl->Load('JawsConfig.php', 'stages/WriteConfig/templates');

        $tpl->SetBlock('JawsConfig');
        $tpl->SetVariable('db_driver',  $_SESSION['upgrade']['Database']['driver']);
        $tpl->SetVariable('db_host',    $_SESSION['upgrade']['Database']['host']);
        $tpl->setVariable('db_port',    $_SESSION['upgrade']['Database']['port']);
        $tpl->SetVariable('db_user',    $_SESSION['upgrade']['Database']['user']);
        $tpl->SetVariable('db_pass',    $_SESSION['upgrade']['Database']['password']);
        $tpl->SetVariable('db_isdba',   $_SESSION['upgrade']['Database']['isdba']);
        $tpl->SetVariable('db_name',    $_SESSION['upgrade']['Database']['name']);
        $tpl->SetVariable('db_path',    addslashes($_SESSION['upgrade']['Database']['path']));
        $tpl->SetVariable('db_prefix',  $_SESSION['upgrade']['Database']['prefix']);
        $tpl->SetVariable('log_level',  defined('LOG_ACTIVATED')? LOG_ACTIVATED : (int)DEBUG_ACTIVATED);
        $tpl->ParseBlock('JawsConfig');

        return $tpl->Get();
    }

    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->loadPreferences(array('language' => $_SESSION['upgrade']['language']), false);

        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/WriteConfig/templates');

        _log(JAWS_LOG_DEBUG,"Preparing configuaration file");
        $tpl->SetBlock('WriteConfig');

        $config_path = JAWS_PATH .'config'.DIRECTORY_SEPARATOR;
        $tpl->setVariable('lbl_info',                _t('UPGRADE_CONFIG_INFO'));
        $tpl->setVariable('lbl_solution',            _t('UPGRADE_CONFIG_SOLUTION'));
        $tpl->setVariable('lbl_solution_permission', _t('UPGRADE_CONFIG_SOLUTION_PERMISSION', $config_path));
        $tpl->setVariable('lbl_solution_upload',     _t('UPGRADE_CONFIG_SOLUTION_UPLOAD', $config_path. 'JawsConfig.php'));
        $tpl->SetVariable('next',                    _t('GLOBAL_NEXT'));

        $tpl->SetVariable('config', $this->BuildConfig());
        $tpl->ParseBlock('WriteConfig');

        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        //config string
        $configString = $this->BuildConfig();
        $configMD5 = md5($configString);

        $existsConfig = @file_get_contents(JAWS_PATH. 'config/JawsConfig.php');
        $existsMD5 = md5($existsConfig);
        if ($configMD5 !== $existsMD5) {
            if (!Jaws_Utils::is_writable(JAWS_PATH . 'config/')) {
                return Jaws_Error::raiseError(
                    _t('UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', 'JawsConfig.php'),
                    __FUNCTION__,
                    JAWS_ERROR_WARNING
                );
            }

            // create/overwrite a new one if the dir is writeable
            $result = @file_put_contents(JAWS_PATH . 'config/JawsConfig.php', $configString);
            if ($result === false) {
                return Jaws_Error::raiseError(
                    _t('UPGRADE_CONFIG_RESPONSE_WRITE_FAILED'),
                    __FUNCTION__,
                    JAWS_ERROR_WARNING
                );
            }
        }

        // Connect to database
        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($objDatabase)) {
            _log(JAWS_LOG_DEBUG,"There was a problem connecting to the database, please check the details and try again");
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->Registry->Init();

        _log(JAWS_LOG_DEBUG,"Setting ".JAWS_VERSION." as the current installed version");
        $GLOBALS['app']->Registry->update('version', JAWS_VERSION);

        //remove cache directory
        $path = JAWS_DATA. 'cache';
        if (!Jaws_Utils::delete($path)) {
            _log(JAWS_LOG_DEBUG,"Can't delete $path");
        }

        _log(JAWS_LOG_DEBUG,"Configuration file has been created/updated");
        return true;
    }
}