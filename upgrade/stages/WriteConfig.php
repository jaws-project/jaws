<?php
/**
 * Write configuration stuff Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
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
        $tpl = new Jaws_Template(UPGRADE_PATH . 'stages/WriteConfig/templates/');
        $tpl->Load('JawsConfig.php', false, false);

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
        $tpl = new Jaws_Template(UPGRADE_PATH . 'stages/WriteConfig/templates/');
        $tpl->Load('display.html', false, false);

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

        if (isset($_SESSION['upgrade']['upgradeLast']) && $_SESSION['upgrade']['upgradeLast'] === true) {
            require_once JAWS_PATH . 'include/Jaws/DB.php';
            $GLOBALS['db'] = new Jaws_DB($_SESSION['upgrade']['Database']);

            // Create application
            include_once JAWS_PATH . 'include/Jaws.php';
            $GLOBALS['app'] = new Jaws();
            $GLOBALS['app']->create();
            $GLOBALS['app']->OverwriteDefaults(array('language' => $_SESSION['upgrade']['language']));

            _log(JAWS_LOG_DEBUG,"Setting ".JAWS_VERSION." as the current installed version");
            $GLOBALS['app']->Registry->deleteCacheFile('core');
            $GLOBALS['app']->registry->update('/version', JAWS_VERSION);
            $GLOBALS['app']->Registry->commit('core');
        }

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

        // following what the web page says (choice 1) and assume that the user has created it already
        if (file_exists(JAWS_PATH . 'config/JawsConfig.php')) {
            $configMD5    = md5($configString);
            $existsConfig = file_get_contents(JAWS_PATH . 'config/JawsConfig.php');
            $existsMD5    = md5($existsConfig);
            if ($configMD5 == $existsMD5) {
                _log(JAWS_LOG_DEBUG,"Previous and new configuration files have the same content, everything is ok");
                return true;
            }
            _log(JAWS_LOG_DEBUG,"Previous and new configuration files have different content, trying to update content");
        }

        // create/overwrite a new one if the dir is writeable
        if (Jaws_Utils::is_writable(JAWS_PATH . 'config/')) {
            $result = file_put_contents(JAWS_PATH . 'config/JawsConfig.php', $configString);
            if ($result) {
                _log(JAWS_LOG_DEBUG,"Configuration file has been created/updated");
                return true;
            }
            _log(JAWS_LOG_DEBUG,"Configuration file couldn't be updated");
            return new Jaws_Error(_t('UPGRADE_CONFIG_RESPONSE_WRITE_FAILED'), 0, JAWS_ERROR_ERROR);
        }        

        return new Jaws_Error(_t('UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', 'JawsConfig.php'), 0, JAWS_ERROR_WARNING);
    }
}